<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SituationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SituationController extends Controller
{
    protected SituationService $situationService;

    public function __construct(SituationService $situationService)
    {
        $this->situationService = $situationService;
    }

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'per_page' => 'sometimes|integer|min:1|max:50',
            'category' => 'sometimes|string|in:' . \App\Enums\SituationCategory::getForValidation()
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибки валидации',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userId = auth('api')->id();
            $perPage = $request->input('per_page', 15);
            $category = $request->input('category');

            if ($category) {
                $result = $this->situationService->getSituationsByCategory($category, $userId);
            } else {
                $result = $this->situationService->getAvailableSituations($userId, $perPage);
            }

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Список ситуаций успешно получен',
                'data' => $result['data']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении списка ситуаций',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Неверный ID ситуации'
            ], 422);
        }

        try {
            $userId = auth('api')->id();
            $result = $this->situationService->getSituationById($id, $userId);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Детали ситуации успешно получены',
                'data' => $result['data']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении ситуации',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function random(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category' => 'sometimes|string|in:' . \App\Enums\SituationCategory::getForValidation()
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибки валидации',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userId = auth('api')->id();
            $category = $request->input('category');
            
            $result = $this->situationService->getRandomSituation($userId, $category);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'cooldown_ends_at' => $result['cooldown_ends_at'] ?? null
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Случайная ситуация успешно получена',
                'data' => $result['data']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении случайной ситуации',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function complete(Request $request, $id)
    {
        $validator = Validator::make(array_merge($request->all(), ['situation_id' => $id]), [
            'situation_id' => 'required|integer|min:1',
            'option_id' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибки валидации',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userId = auth('api')->id();
            $optionId = $request->input('option_id');
            
            $result = $this->situationService->completeSituation($id, $optionId, $userId);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }

            $statusCode = $result['data']['player_changes']['level_up'] ? 201 : 200;

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['data']
            ], $statusCode);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при завершении ситуации',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function history(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'sometimes|integer|min:1|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибки валидации',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userId = auth('api')->id();
            $limit = $request->input('limit', 20);
            
            $result = $this->situationService->getPlayerSituationHistory($userId, $limit);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'История ситуаций успешно получена',
                'data' => $result['data']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении истории ситуаций',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function recommendations()
    {
        try {
            $userId = auth('api')->id();
            
            $result = $this->situationService->getRecommendedSituations($userId);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Рекомендованные ситуации успешно получены',
                'data' => $result['data']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении рекомендаций',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
