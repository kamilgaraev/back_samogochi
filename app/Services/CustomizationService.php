<?php

namespace App\Services;

use App\Models\CustomizationItem;
use App\Models\PlayerCustomization;
use App\Models\PlayerProfile;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;

class CustomizationService
{
    public function getPlayerCustomizations(int $playerId): array
    {
        $player = PlayerProfile::findOrFail($playerId);
        $playerLevel = $player->level;

        $allCategoryKeys = CustomizationItem::active()
            ->select('category_key')
            ->distinct()
            ->pluck('category_key');

        $result = [];

        foreach ($allCategoryKeys as $categoryKey) {
            $customization = $this->getCategoryData($playerId, $categoryKey, $playerLevel);
            if ($customization) {
                $result[] = $customization;
            }
        }

        return $result;
    }

    public function getPlayerCustomizationByKey(int $playerId, string $categoryKey): ?array
    {
        $player = PlayerProfile::findOrFail($playerId);
        $playerLevel = $player->level;

        return $this->getCategoryData($playerId, $categoryKey, $playerLevel);
    }

    private function getCategoryData(int $playerId, string $categoryKey, int $playerLevel): ?array
    {
        $playerCustomization = PlayerCustomization::where('player_id', $playerId)
            ->where('category_key', $categoryKey)
            ->first();

        $allItems = CustomizationItem::active()
            ->byCategory($categoryKey)
            ->orderBy('order')
            ->get();

        if ($allItems->isEmpty()) {
            return null;
        }

        $unlockedItemIds = $playerCustomization->unlocked_items ?? [];
        $selectedItemId = $playerCustomization->selected_item_id ?? null;

        $availableIds = array_values(array_diff($unlockedItemIds, [$selectedItemId]));

        $nextUnlockLevel = $allItems
            ->where('unlock_level', '>', $playerLevel)
            ->sortBy('unlock_level')
            ->first()?->unlock_level;

        return [
            'key' => $categoryKey,
            'selected' => $selectedItemId,
            'available' => $availableIds,
            'max' => $allItems->count(),
            'current_max' => $allItems->where('unlock_level', '<=', $playerLevel)->count(),
            'next_unlock_level' => $nextUnlockLevel,
        ];
    }

    public function selectItem(int $playerId, string $categoryKey, int $itemId): array
    {
        try {
            DB::beginTransaction();

            $playerCustomization = PlayerCustomization::firstOrCreate(
                [
                    'player_id' => $playerId,
                    'category_key' => $categoryKey,
                ],
                [
                    'unlocked_items' => [],
                    'new_unlocked_items' => [],
                ]
            );

            if (!$playerCustomization->isUnlocked($itemId)) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Элемент не разблокирован',
                ];
            }

            $playerCustomization->selected_item_id = $itemId;
            $playerCustomization->save();

            ActivityLog::logEvent('customization.item_selected', [
                'player_id' => $playerId,
                'category_key' => $categoryKey,
                'item_id' => $itemId,
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Элемент успешно выбран',
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Ошибка при выборе элемента: ' . $e->getMessage(),
            ];
        }
    }

    public function markAsViewed(int $playerId, string $categoryKey, array $viewedItemIds): array
    {
        try {
            DB::beginTransaction();

            $playerCustomization = PlayerCustomization::where('player_id', $playerId)
                ->where('category_key', $categoryKey)
                ->first();

            if (!$playerCustomization) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Данные кастомизации не найдены',
                ];
            }

            $playerCustomization->markAsViewed($viewedItemIds);
            $playerCustomization->save();

            ActivityLog::logEvent('customization.items_viewed', [
                'player_id' => $playerId,
                'category_key' => $categoryKey,
                'viewed_items' => $viewedItemIds,
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Элементы отмечены как просмотренные',
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Ошибка при обновлении: ' . $e->getMessage(),
            ];
        }
    }

    public function unlockItemsForLevel(int $playerId, int $newLevel): array
    {
        try {
            DB::beginTransaction();

            $player = PlayerProfile::findOrFail($playerId);
            
            $itemsToUnlock = CustomizationItem::active()
                ->where('unlock_level', $newLevel)
                ->get();

            $unlockedByCategory = [];

            foreach ($itemsToUnlock as $item) {
                $playerCustomization = PlayerCustomization::firstOrCreate(
                    [
                        'player_id' => $playerId,
                        'category_key' => $item->category_key,
                    ],
                    [
                        'unlocked_items' => [],
                        'new_unlocked_items' => [],
                    ]
                );

                if (!$playerCustomization->isUnlocked($item->id)) {
                    $playerCustomization->unlockItem($item->id);
                    $playerCustomization->addNewUnlockedItem($item->id);
                    $playerCustomization->save();

                    if (!isset($unlockedByCategory[$item->category_key])) {
                        $unlockedByCategory[$item->category_key] = [];
                    }
                    $unlockedByCategory[$item->category_key][] = $item->id;

                    ActivityLog::logEvent('customization.item_unlocked', [
                        'player_id' => $playerId,
                        'item_id' => $item->id,
                        'category_key' => $item->category_key,
                        'unlock_level' => $newLevel,
                    ]);
                }
            }

            DB::commit();

            return [
                'success' => true,
                'unlocked_by_category' => $unlockedByCategory,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Ошибка при разблокировке элементов: ' . $e->getMessage(),
            ];
        }
    }

    public function initializePlayerCustomizations(int $playerId): void
    {
        $defaultItems = CustomizationItem::active()
            ->where('is_default', true)
            ->orWhere('unlock_level', 1)
            ->get();

        foreach ($defaultItems as $item) {
            $playerCustomization = PlayerCustomization::firstOrCreate(
                [
                    'player_id' => $playerId,
                    'category_key' => $item->category_key,
                ],
                [
                    'unlocked_items' => [],
                    'new_unlocked_items' => [],
                ]
            );

            if (!$playerCustomization->isUnlocked($item->id)) {
                $playerCustomization->unlockItem($item->id);
                
                if ($item->is_default && !$playerCustomization->selected_item_id) {
                    $playerCustomization->selected_item_id = $item->id;
                }
                
                $playerCustomization->save();
            }
        }
    }

    public function getNewUnlockedItems(int $playerId): array
    {
        $customizations = PlayerCustomization::where('player_id', $playerId)
            ->whereNotNull('new_unlocked_items')
            ->get();

        $result = [];

        foreach ($customizations as $customization) {
            $newItems = $customization->new_unlocked_items ?? [];
            
            if (!empty($newItems)) {
                $result[] = [
                    'key' => $customization->category_key,
                    'new_unlocked' => $newItems,
                ];
            }
        }

        return $result;
    }
}

