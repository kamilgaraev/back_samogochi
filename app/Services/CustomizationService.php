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

        return [
            'customizations' => $result,
        ];
    }

    public function getPlayerCustomizationByKey(int $playerId, string $categoryKey): ?array
    {
        $player = PlayerProfile::findOrFail($playerId);
        $playerLevel = $player->level;

        $customization = $this->getCategoryData($playerId, $categoryKey, $playerLevel);
        
        if (!$customization) {
            return null;
        }

        return [
            'customization' => $customization,
        ];
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

        // Добавляем элементы по умолчанию и по уровню в разблокированные
        $defaultItemIds = $allItems->where('is_default', true)->pluck('id')->toArray();
        $unlockedByLevelIds = $allItems->where('unlock_level', '<=', $playerLevel)->pluck('id')->toArray();
        $allUnlockedIds = array_unique(array_merge($unlockedItemIds, $defaultItemIds, $unlockedByLevelIds));

        $availableIds = array_values(array_diff($allUnlockedIds, [$selectedItemId]));

        $nextUnlockItems = $allItems
            ->where('unlock_level', '>', $playerLevel)
            ->where('is_default', false)
            ->sortBy('unlock_level')
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'unlock_level' => $item->unlock_level,
                    'order' => $item->order,
                    'is_default' => $item->is_default,
                    'image_url' => $item->image_url,
                ];
            })
            ->values()
            ->toArray();

        // Считаем реальное количество разблокированных элементов
        // Включаем выбранный элемент, если он есть
        $allUnlockedWithSelected = $allUnlockedIds;
        if ($selectedItemId && !in_array($selectedItemId, $allUnlockedWithSelected)) {
            $allUnlockedWithSelected[] = $selectedItemId;
        }
        $currentMax = count($allUnlockedWithSelected);

        return [
            'key' => $categoryKey,
            'selected' => $selectedItemId,
            'available' => $availableIds,
            'max' => $allItems->count(),
            'current_max' => $currentMax,
            'next_unlock_level' => $nextUnlockItems,
        ];
    }

    public function selectItemByOrder(int $playerId, string $categoryKey, int $order): array
    {
        try {
            DB::beginTransaction();

            $item = CustomizationItem::active()
                ->byCategory($categoryKey)
                ->where('order', $order)
                ->first();

            if (!$item) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Элемент с таким order не найден',
                ];
            }

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

            if (!$playerCustomization->isUnlocked($item->id)) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Элемент не разблокирован',
                ];
            }

            $playerCustomization->selected_item_id = $item->id;
            $playerCustomization->save();

            ActivityLog::logEvent('customization.item_selected', [
                'player_id' => $playerId,
                'category_key' => $categoryKey,
                'item_id' => $item->id,
                'order' => $order,
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

