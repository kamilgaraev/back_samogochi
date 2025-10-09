<?php

namespace App\Services;

use App\Models\User;
use App\Models\PlayerProfile;
use App\Models\ActivityLog;
use App\Notifications\VerifyEmailNotification;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Carbon\Carbon;

class AuthService
{
    public function register(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_admin' => false,
        ]);

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

        ActivityLog::logRegistration($user->id);

        $this->sendEmailVerification($user);

        return [
            'user' => $user,
            'player' => $playerProfile,
            'email_verification_sent' => true,
            'message' => 'Регистрация успешна. Проверьте email для подтверждения.'
        ];
    }

    public function login(array $credentials)
    {
        $user = User::where('email', $credentials['email'])->first();
        
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            ActivityLog::logEvent('user.login_failed', ['email' => $credentials['email']]);
            return null;
        }

        if (!$user->email_verified_at) {
            ActivityLog::logEvent('user.login_blocked_unverified', ['email' => $user->email], $user->id);
            return ['error' => 'email_not_verified', 'message' => 'Email не подтвержден'];
        }

        $token = JWTAuth::fromUser($user);
        
        if ($user->playerProfile) {
            $user->playerProfile->updateLastLogin();
        }

        ActivityLog::logLogin($user->id);

        return [
            'user' => $user,
            'player' => $user->playerProfile,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => (int)config('jwt.ttl') * 60
        ];
    }

    public function logout()
    {
        $user = JWTAuth::user();
        
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
            Log::info('Refresh attempt started');
            $currentToken = JWTAuth::getToken();
            Log::info('Current token exists', ['has_token' => !!$currentToken]);
            
            $token = JWTAuth::refresh();
            Log::info('Token refreshed successfully');
            
            return [
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => (int)config('jwt.ttl') * 60
            ];
        } catch (JWTException $e) {
            Log::error('JWT Refresh failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function me()
    {
        $user = JWTAuth::user();
        
        if (!$user) {
            return null;
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'avatar' => $user->avatar,
            'is_admin' => $user->is_admin,
            'player' => $user->playerProfile,
        ];
    }

    public function sendEmailVerification(User $user)
    {
        $token = strtoupper(Str::random(6));
        
        DB::table('email_verification_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($token),
                'created_at' => Carbon::now()
            ]
        );

        try {
            $user->notify(new VerifyEmailNotification($token, $user->email));
            ActivityLog::logEvent('user.email_verification_sent', ['email' => $user->email], $user->id);
            Log::info('Email verification sent successfully', ['email' => $user->email]);
        } catch (\Exception $e) {
            Log::error('Email verification failed', [
                'email' => $user->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }

        return true;
    }

    public function verifyEmail(string $email, string $token)
    {
        $record = DB::table('email_verification_tokens')
            ->where('email', $email)
            ->first();

        if (!$record) {
            return false;
        }

        if (!Hash::check($token, $record->token)) {
            return false;
        }

        if (Carbon::parse($record->created_at)->addHours(24)->isPast()) {
            DB::table('email_verification_tokens')->where('email', $email)->delete();
            return false;
        }

        $user = User::where('email', $email)->first();
        
        if (!$user) {
            return false;
        }

        $user->update(['email_verified_at' => Carbon::now()]);
        
        DB::table('email_verification_tokens')->where('email', $email)->delete();

        ActivityLog::logEvent('user.email_verified', ['email' => $email], $user->id);

        $jwtToken = JWTAuth::fromUser($user);

        return [
            'user' => $user,
            'player' => $user->playerProfile,
            'token' => $jwtToken,
            'token_type' => 'bearer',
            'expires_in' => (int)config('jwt.ttl') * 60
        ];
    }

    public function resendEmailVerification(string $email)
    {
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            return false;
        }

        if ($user->email_verified_at) {
            return false;
        }

        return $this->sendEmailVerification($user);
    }

    public function forgotPassword(string $email)
    {
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            return false;
        }

        $token = strtoupper(Str::random(6));
        
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($token),
                'created_at' => Carbon::now()
            ]
        );

        try {
            $user->notify(new ResetPasswordNotification($token, $email));
            ActivityLog::logEvent('user.password_reset_requested', ['email' => $email], $user->id);
            Log::info('Password reset email sent successfully', ['email' => $email]);
        } catch (\Exception $e) {
            Log::error('Password reset email failed', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }

        return true;
    }

    public function resetPassword(string $email, string $token, string $password)
    {
        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();
        
        if (!$record) {
            return false;
        }

        if (!Hash::check($token, $record->token)) {
            return false;
        }

        if (Carbon::parse($record->created_at)->addHours(1)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return false;
        }

        $user = User::where('email', $email)->first();
        
        if (!$user) {
            return false;
        }

        $user->update([
            'password' => Hash::make($password)
        ]);

        DB::table('password_reset_tokens')->where('email', $email)->delete();

        ActivityLog::logEvent('user.password_reset', null, $user->id);

        return true;
    }
}
