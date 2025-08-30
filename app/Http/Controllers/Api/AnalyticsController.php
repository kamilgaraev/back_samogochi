<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AnalyticsController extends Controller
{
    protected AnalyticsService $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function dashboard()
    {
        try {
            $result = $this->analyticsService->getDashboard();

            return response()->json([
                'success' => true,
                'message' => 'Дашборд аналитики успешно получен',
                'data' => $result['data']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении дашборда аналитики',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function playerBehavior(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'min_level' => 'sometimes|integer|min:1',
            'max_level' => 'sometimes|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибки валидации',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $filters = $request->only(['min_level', 'max_level']);
            $result = $this->analyticsService->getPlayerBehavior($filters);

            return response()->json([
                'success' => true,
                'message' => 'Аналитика поведения игроков успешно получена',
                'data' => $result['data']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении аналитики поведения игроков',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function situationStats(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category' => 'sometimes|string|in:' . \App\Enums\SituationCategory::getForValidation(),
            'difficulty_level' => 'sometimes|integer|min:1|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибки валидации',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $filters = $request->only(['category', 'difficulty_level']);
            $result = $this->analyticsService->getSituationStats($filters);

            return response()->json([
                'success' => true,
                'message' => 'Статистика ситуаций успешно получена',
                'data' => $result['data']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении статистики ситуаций',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function activityStats(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_type' => 'sometimes|string',
            'date_from' => 'sometimes|date',
            'date_to' => 'sometimes|date|after_or_equal:date_from',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибки валидации',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $filters = $request->only(['event_type', 'date_from', 'date_to']);
            $result = $this->analyticsService->getActivityStats($filters);

            return response()->json([
                'success' => true,
                'message' => 'Статистика активности успешно получена',
                'data' => $result['data']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении статистики активности',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
