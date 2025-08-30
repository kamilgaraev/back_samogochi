<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибки валидации',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->authService->register($request->only(['name', 'email', 'password']));
            
            return response()->json([
                'success' => true,
                'message' => 'Пользователь успешно зарегистрирован',
                'data' => $result
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка регистрации',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибки валидации',
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only(['email', 'password']);
        $result = $this->authService->login($credentials);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Неверные учетные данные'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Авторизация успешна',
            'data' => $result
        ]);
    }

    public function logout()
    {
        $success = $this->authService->logout();

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Вы успешно вышли из системы'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Ошибка при выходе из системы'
        ], 500);
    }

    public function refresh()
    {
        $result = $this->authService->refresh();

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Не удалось обновить токен'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Токен успешно обновлен',
            'data' => $result
        ]);
    }

    public function me()
    {
        $result = $this->authService->me();

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибки валидации',
                'errors' => $validator->errors()
            ], 422);
        }

        $success = $this->authService->forgotPassword($request->email);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Инструкции по сбросу пароля отправлены на email'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Пользователь с таким email не найден'
        ], 404);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибки валидации',
                'errors' => $validator->errors()
            ], 422);
        }

        $success = $this->authService->resetPassword(
            $request->email,
            $request->token,
            $request->password
        );

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Пароль успешно изменен'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Недействительный токен или email'
        ], 400);
    }

    public function verifyEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибки валидации',
                'errors' => $validator->errors()
            ], 422);
        }

        $success = $this->authService->verifyEmail($request->token);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Email успешно подтвержден'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Недействительный токен'
        ], 400);
    }
}
