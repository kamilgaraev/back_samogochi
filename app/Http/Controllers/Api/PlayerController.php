<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PlayerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PlayerController extends Controller
{
    protected PlayerService $playerService;

    public function __construct(PlayerService $playerService)
    {
        $this->playerService = $playerService;
    }

    public function profile()
    {
        try {
            $userId = auth('api')->id();
            $profile = $this->playerService->getPlayerProfile($userId);

            if (!$profile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Профиль игрока не найден'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Профиль игрока успешно получен',
                'data' => $profile
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении профиля',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'stress' => 'sometimes|integer|min:0|max:100',
            'anxiety' => 'sometimes|integer|min:0|max:100',
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
            $result = $this->playerService->updatePlayerProfile($userId, $request->all());

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }

            $profile = $this->playerService->getPlayerProfile($userId);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $profile,
                'updated_fields' => $result['updated_fields']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении профиля',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function stats()
    {
        try {
            $userId = auth('api')->id();
            $result = $this->playerService->getPlayerStats($userId);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Статистика игрока успешно получена',
                'data' => $result['data']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении статистики',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function progress()
    {
        try {
            $userId = auth('api')->id();
            $result = $this->playerService->getPlayerProgress($userId);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Прогресс игрока успешно получен',
                'data' => $result['data']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении прогресса',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function claimDailyReward()
    {
        try {
            $userId = auth('api')->id();
            $result = $this->playerService->claimDailyReward($userId);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Ежедневная награда получена!',
                'data' => [
                    'experience_gained' => $result['experience_gained'],
                    'bonus_experience' => $result['bonus_experience'],
                    'consecutive_days' => $result['consecutive_days']
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении награды',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function addExperience(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|integer|min:1|max:1000',
            'reason' => 'sometimes|string|max:255'
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
            $amount = $request->input('amount');
            $reason = $request->input('reason', 'manual');

            $result = $this->playerService->addExperience($userId, $amount, $reason);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }

            $response = [
                'success' => true,
                'message' => 'Опыт успешно добавлен',
                'data' => [
                    'experience_added' => $result['experience_added'],
                    'old_level' => $result['old_level'],
                    'new_level' => $result['new_level']
                ]
            ];

            if ($result['level_up']) {
                $response['message'] = 'Поздравляем! Вы достигли нового уровня!';
                $response['level_up'] = true;
            }

            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при добавлении опыта',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateEnergy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|integer|min:-200|max:200'
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
            $amount = $request->input('amount');

            $result = $this->playerService->updateEnergy($userId, $amount);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Энергия успешно обновлена',
                'data' => [
                    'old_energy' => $result['old_energy'],
                    'new_energy' => $result['new_energy'],
                    'change' => $result['change']
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении энергии',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|integer|min:-100|max:100'
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
            $amount = $request->input('amount');

            $result = $this->playerService->updateStress($userId, $amount);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Уровень стресса успешно обновлен',
                'data' => [
                    'old_stress' => $result['old_stress'],
                    'new_stress' => $result['new_stress'],
                    'change' => $result['change'],
                    'stress_status' => $result['stress_status']
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении стресса',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
