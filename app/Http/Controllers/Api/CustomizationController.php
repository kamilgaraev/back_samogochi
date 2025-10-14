<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CustomizationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CustomizationController extends Controller
{
    protected CustomizationService $customizationService;

    public function __construct(CustomizationService $customizationService)
    {
        $this->customizationService = $customizationService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $playerProfile = $user->playerProfile;

            if (!$playerProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Профиль игрока не найден'
                ], 404);
            }

            $result = $this->customizationService->getPlayerCustomizations($playerProfile->id);

            $customizationsByKey = [];
            foreach ($result['customizations'] as $customization) {
                $customizationsByKey[$customization['key']] = $customization;
            }

            return response()->json([
                'success' => true,
                'message' => 'Кастомизации получены успешно',
                'data' => array_merge($customizationsByKey, [
                    'unlock_levels' => $result['unlock_levels']
                ])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении данных: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, string $categoryKey): JsonResponse
    {
        try {
            $user = $request->user();
            $playerProfile = $user->playerProfile;

            if (!$playerProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Профиль игрока не найден'
                ], 404);
            }

            $result = $this->customizationService->getPlayerCustomizationByKey($playerProfile->id, $categoryKey);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Категория не найдена'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Кастомизация категории получена успешно',
                'data' => array_merge($result['customization'], [
                    'unlock_levels' => $result['unlock_levels']
                ])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении данных: ' . $e->getMessage()
            ], 500);
        }
    }

    public function select(Request $request): JsonResponse
    {
        $request->validate([
            'key' => 'required|string',
            'selected' => 'required|integer|min:1',
        ]);

        try {
            $user = $request->user();
            $playerProfile = $user->playerProfile;

            if (!$playerProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Профиль игрока не найден'
                ], 404);
            }

            $result = $this->customizationService->selectItem(
                $playerProfile->id,
                $request->input('key'),
                $request->input('selected')
            );

            if (!$result['success']) {
                return response()->json($result, 400);
            }

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при выборе элемента: ' . $e->getMessage()
            ], 500);
        }
    }

    public function markViewed(Request $request): JsonResponse
    {
        $request->validate([
            'key' => 'required|string',
            'viewed_items' => 'required|array',
            'viewed_items.*' => 'integer|min:1',
        ]);

        try {
            $user = $request->user();
            $playerProfile = $user->playerProfile;

            if (!$playerProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Профиль игрока не найден'
                ], 404);
            }

            $result = $this->customizationService->markAsViewed(
                $playerProfile->id,
                $request->input('key'),
                $request->input('viewed_items')
            );

            if (!$result['success']) {
                return response()->json($result, 400);
            }

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении: ' . $e->getMessage()
            ], 500);
        }
    }
}

