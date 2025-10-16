<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AdminService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    protected AdminService $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    public function configs()
    {
        try {
            $result = $this->adminService->getConfigs();

            return response()->json([
                'success' => true,
                'message' => 'Конфигурации успешно получены',
                'data' => $result['data']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении конфигураций',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateConfig(Request $request, $key)
    {
        $validator = Validator::make($request->all(), [
            'value' => 'required',
            'description' => 'sometimes|string|max:1000',
            'is_active' => 'sometimes|boolean'
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
            $result = $this->adminService->updateConfig($key, $request->all(), $userId);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['data']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении конфигурации',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function situations(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category' => 'sometimes|string|in:' . \App\Enums\SituationCategory::getForValidation(),
            'difficulty_level' => 'sometimes|integer|min:1|max:5',
            'is_active' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибки валидации',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $filters = $request->only(['category', 'difficulty_level', 'is_active']);
            $result = $this->adminService->getSituations($filters);

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

    public function createSituation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'category' => 'required|string|in:' . \App\Enums\SituationCategory::getForValidation(),
            'difficulty_level' => 'required|integer|min:1|max:5',
            'min_level_required' => 'sometimes|integer|min:1',
            'stress_impact' => 'required|integer|min:-50|max:50',
            'experience_reward' => 'required|integer|min:1|max:100',
            'is_active' => 'sometimes|boolean',
            'required_customization_key' => 'nullable|string|max:100',
            'options' => 'sometimes|array|min:1',
            'options.*.text' => 'required|string|max:1000',
            'options.*.stress_change' => 'required|integer|min:-50|max:50',
            'options.*.experience_reward' => 'required|integer|min:0|max:100',
            'options.*.energy_cost' => 'sometimes|integer|min:0|max:50',
            'options.*.min_level_required' => 'sometimes|integer|min:1',
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
            $result = $this->adminService->createSituation($request->all(), $userId);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['data']
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при создании ситуации',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateSituation(Request $request, $id)
    {
        $validator = Validator::make(array_merge($request->all(), ['id' => $id]), [
            'id' => 'required|integer|min:1',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:2000',
            'category' => 'sometimes|string|in:' . \App\Enums\SituationCategory::getForValidation(),
            'difficulty_level' => 'sometimes|integer|min:1|max:5',
            'min_level_required' => 'sometimes|integer|min:1',
            'stress_impact' => 'sometimes|integer|min:-50|max:50',
            'experience_reward' => 'sometimes|integer|min:1|max:100',
            'is_active' => 'sometimes|boolean',
            'required_customization_key' => 'nullable|string|max:100',
            'options' => 'sometimes|array|min:1',
            'options.*.text' => 'required|string|max:1000',
            'options.*.stress_change' => 'required|integer|min:-50|max:50',
            'options.*.experience_reward' => 'required|integer|min:0|max:100',
            'options.*.energy_cost' => 'sometimes|integer|min:0|max:50',
            'options.*.min_level_required' => 'sometimes|integer|min:1',
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
            $result = $this->adminService->updateSituation($id, $request->all(), $userId);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['data']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении ситуации',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteSituation($id)
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
            $result = $this->adminService->deleteSituation($id, $userId);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => $result['message']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при удалении ситуации',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
