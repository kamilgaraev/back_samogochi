<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MicroActionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MicroActionController extends Controller
{
    protected MicroActionService $microActionService;

    public function __construct(MicroActionService $microActionService)
    {
        $this->microActionService = $microActionService;
    }

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category' => 'sometimes|string|in:' . \App\Enums\MicroActionCategory::getForValidation()
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
            $result = $this->microActionService->getAvailableMicroActions($userId);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Список микродействий успешно получен',
                'data' => $result['data']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении списка микродействий',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function perform(Request $request, $id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Неверный ID микродействия'
            ], 422);
        }

        try {
            $userId = auth('api')->id();
            $result = $this->microActionService->performMicroAction($id, $userId);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'cooldown_ends_at' => $result['cooldown_ends_at'] ?? null
                ], 400);
            }

            $statusCode = $result['data']['player_changes']['level_up'] ? 201 : 200;

            $playerState = $result['data']['player_state'] ?? null;
            unset($result['data']['player_state']);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['data'],
                'player_state' => $playerState
            ], $statusCode);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при выполнении микродействия',
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
            
            $result = $this->microActionService->getMicroActionHistory($userId, $limit);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'История микродействий успешно получена',
                'data' => $result['data']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении истории микродействий',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function recommendations()
    {
        try {
            $userId = auth('api')->id();
            
            $result = $this->microActionService->getRecommendedMicroActions($userId);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Рекомендованные микродействия успешно получены',
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

    public function randomRecommendation()
    {
        try {
            $userId = auth('api')->id();
            
            $result = $this->microActionService->getRandomRecommendedMicroAction($userId);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Случайное рекомендованное микродействие получено',
                'data' => $result['data']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении случайной рекомендации',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
