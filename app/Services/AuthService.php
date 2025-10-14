<?php

namespace App\Services;

use App\Models\User;
use App\Models\PlayerProfile;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Carbon\Carbon;

class AuthService
{
    public function __construct()
    {
        // Проверка конфигурации почты при инициализации сервиса
        $this->validateMailConfiguration();
    }

    private function validateMailConfiguration()
    {
        $mailDriver = config('mail.default');

        if ($mailDriver === 'log') {
            Log::warning('Mail driver is set to LOG - emails will not be sent', [
                'mail_driver' => $mailDriver,
                'recommended_drivers' => ['smtp', 'mailgun', 'ses', 'postmark']
            ]);
        }

        if ($mailDriver === 'smtp') {
            $requiredConfig = ['host', 'port', 'username', 'password'];
            $missingConfig = [];

            foreach ($requiredConfig as $config) {
                if (empty(config("mail.mailers.smtp.{$config}"))) {
                    $missingConfig[] = $config;
                }
            }

            if (!empty($missingConfig)) {
                Log::error('SMTP mail configuration is incomplete', [
                    'missing_config' => $missingConfig,
                    'mail_driver' => $mailDriver
                ]);
            }
        }
    }

    public function register(array $data)
    {
        return DB::transaction(function () use ($data) {
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

            $customizationService = app(\App\Services\CustomizationService::class);
            $customizationService->initializePlayerCustomizations($playerProfile->id);

            ActivityLog::logRegistration($user->id);

            $this->sendEmailVerification($user);

            return [
                'user' => $user,
                'player' => $playerProfile,
                'email_verification_sent' => true,
                'message' => 'Регистрация успешна. Проверьте email для подтверждения.'
            ];
        });
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

        try {
            return DB::transaction(function () use ($user, $token) {
                DB::table('email_verification_tokens')->updateOrInsert(
                    ['email' => $user->email],
                    [
                        'token' => Hash::make($token),
                        'created_at' => Carbon::now()
                    ]
                );

                $verificationUrl = config('app.url') . '/api/auth/verify-email/' . $token . '?email=' . urlencode($user->email);

                Mail::send('emails.verify-email', [
                    'verificationUrl' => $verificationUrl,
                    'userName' => $user->name
                ], function ($message) use ($user) {
                    $message->from('noreply@stressapi.ru', 'Самогочи')
                            ->to($user->email, $user->name)
                            ->subject('Подтверждение email адреса');
                });

                ActivityLog::logEvent('user.email_verification_sent', ['email' => $user->email], $user->id);
                Log::info('Email verification sent successfully', ['email' => $user->email]);

                return true;
            });
        } catch (\Exception $e) {
            Log::error('Email verification failed - transaction rolled back', [
                'email' => $user->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id,
                'token_generated' => $token,
                'mail_driver' => config('mail.default')
            ]);

            // Уведомление администраторам о критической ошибке
            Log::critical('CRITICAL: Email verification failed for user registration', [
                'email' => $user->email,
                'user_id' => $user->id,
                'error_type' => get_class($e),
                'mail_config' => [
                    'driver' => config('mail.default'),
                    'host' => config('mail.mailers.' . config('mail.default') . '.host'),
                    'port' => config('mail.mailers.' . config('mail.default') . '.port')
                ]
            ]);

            throw $e;
        }
    }

    public function verifyEmail(string $email, string $token)
    {
        Log::info('Starting email verification', ['email' => $email, 'token' => $token]);
        
        $record = DB::table('email_verification_tokens')
            ->where('email', $email)
            ->first();

        if (!$record) {
            Log::warning('Token not found in DB', ['email' => $email]);
            return false;
        }

        Log::info('Token found in DB', ['email' => $email]);

        if (!Hash::check($token, $record->token)) {
            Log::warning('Token hash mismatch', ['email' => $email]);
            return false;
        }

        Log::info('Token hash matched', ['email' => $email]);

        if (Carbon::parse($record->created_at)->addHours(24)->isPast()) {
            Log::warning('Token expired', ['email' => $email, 'created_at' => $record->created_at]);
            DB::table('email_verification_tokens')->where('email', $email)->delete();
            return false;
        }

        $user = User::where('email', $email)->first();
        
        if (!$user) {
            Log::error('User not found', ['email' => $email]);
            return false;
        }

        Log::info('User found, updating email_verified_at', ['user_id' => $user->id, 'email' => $email]);
        
        $updated = $user->update(['email_verified_at' => Carbon::now()]);
        
        Log::info('Update result', ['updated' => $updated, 'email_verified_at' => $user->email_verified_at]);
        
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
            Log::warning('Resend email verification failed - user not found', ['email' => $email]);
            return false;
        }

        if ($user->email_verified_at) {
            Log::info('Resend email verification failed - email already verified', ['email' => $email]);
            return false;
        }

        // Проверить, существует ли уже активный токен
        $existingToken = DB::table('email_verification_tokens')
            ->where('email', $email)
            ->first();

        if ($existingToken) {
            Log::info('Resending email verification - existing token found', [
                'email' => $email,
                'token_created_at' => $existingToken->created_at
            ]);
        } else {
            Log::info('Resending email verification - no existing token', ['email' => $email]);
        }

        try {
            return $this->sendEmailVerification($user);
        } catch (\Exception $e) {
            Log::error('Resend email verification failed', [
                'email' => $email,
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            return false;
        }
    }

    public function forgotPassword(string $email)
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return false;
        }

        $newPassword = Str::random(12);

        try {
            return DB::transaction(function () use ($user, $newPassword, $email) {
                $user->update(['password' => Hash::make($newPassword)]);

                Mail::send('emails.reset-password', [
                    'newPassword' => $newPassword,
                    'userName' => $user->name
                ], function ($message) use ($user) {
                    $message->from('noreply@stressapi.ru', 'Самогочи')
                            ->to($user->email, $user->name)
                            ->subject('Новый пароль');
                });

                ActivityLog::logEvent('user.password_reset', ['email' => $email], $user->id);
                Log::info('New password sent successfully', ['email' => $email]);

                return true;
            });
        } catch (\Exception $e) {
            Log::error('Password reset email failed - transaction rolled back', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id,
                'new_password_generated' => $newPassword,
                'mail_driver' => config('mail.default')
            ]);

            // Уведомление администраторам о критической ошибке
            Log::critical('CRITICAL: Password reset email failed', [
                'email' => $email,
                'user_id' => $user->id,
                'error_type' => get_class($e),
                'mail_config' => [
                    'driver' => config('mail.default'),
                    'host' => config('mail.mailers.' . config('mail.default') . '.host'),
                    'port' => config('mail.mailers.' . config('mail.default') . '.port')
                ]
            ]);

            throw $e;
        }
    }

}
