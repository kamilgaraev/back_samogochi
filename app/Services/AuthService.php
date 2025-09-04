<?php

namespace App\Services;

use App\Models\User;
use App\Models\PlayerProfile;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthService
{
    public function register(array $data)
    {
        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'is_admin' => false,
            ]);

            \Log::info('User created successfully', ['user_id' => $user->id]);

            $playerProfile = PlayerProfile::create([
                'user_id' => $user->id,
                'level' => 1,
                'total_experience' => 0,
                'energy' => 100,
                'stress' => 50,
                'anxiety' => 30,
                'last_login' => now(),
                'consecutive_days' => 0,
            ]);

            \Log::info('PlayerProfile created successfully', ['profile_id' => $playerProfile->id]);

            ActivityLog::logRegistration($user->id);

            \Log::info('ActivityLog created successfully');

            $token = JWTAuth::fromUser($user);

            \Log::info('JWT token created successfully');

            return [
                'user' => $user,
                'player' => $playerProfile,
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60
            ];
        } catch (\Exception $e) {
            \Log::error('Registration failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function login(array $credentials)
    {
        if (!$token = JWTAuth::attempt($credentials)) {
            ActivityLog::logEvent('user.login_failed', ['email' => $credentials['email']]);
            return null;
        }

        $user = auth('api')->user();
        
        if ($user && $user->playerProfile) {
            $user->playerProfile->updateLastLogin();
        }

        ActivityLog::logLogin($user->id);

        return [
            'user' => $user,
            'player' => $user->playerProfile,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60
        ];
    }

    public function logout()
    {
        $user = auth('api')->user();
        
        if ($user) {
            ActivityLog::logLogout($user->id);
        }

        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return true;
        } catch (JWTException $e) {
            return false;
        }
    }

    public function refresh()
    {
        try {
            $token = JWTAuth::refresh();
            return [
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60
            ];
        } catch (JWTException $e) {
            return null;
        }
    }

    public function me()
    {
        $user = auth('api')->user();
        return [
            'user' => $user,
            'player' => $user ? $user->playerProfile : null,
        ];
    }

    public function forgotPassword(string $email)
    {
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            return false;
        }

        $token = Str::random(60);
        
        ActivityLog::logEvent('user.password_reset_requested', ['email' => $email]);

        return true;
    }

    public function resetPassword(string $email, string $token, string $password)
    {
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            return false;
        }

        $user->update([
            'password' => Hash::make($password)
        ]);

        ActivityLog::logEvent('user.password_reset', null, $user->id);

        return true;
    }

    public function verifyEmail(string $token)
    {
        return true;
    }
}
