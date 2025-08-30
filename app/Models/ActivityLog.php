<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_type',
        'event_data',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'event_data' => 'json',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function logEvent($eventType, $eventData = null, $userId = null, $ipAddress = null, $userAgent = null)
    {
        return self::create([
            'user_id' => $userId ?? auth('api')->id(),
            'event_type' => $eventType,
            'event_data' => $eventData,
            'ip_address' => $ipAddress ?? request()->ip(),
            'user_agent' => $userAgent ?? request()->userAgent(),
        ]);
    }

    public static function logLogin($userId)
    {
        return self::logEvent(\App\Enums\ActivityEventType::USER_LOGIN->value, null, $userId);
    }

    public static function logLogout($userId)
    {
        return self::logEvent(\App\Enums\ActivityEventType::USER_LOGOUT->value, null, $userId);
    }

    public static function logRegistration($userId)
    {
        return self::logEvent(\App\Enums\ActivityEventType::USER_REGISTRATION->value, null, $userId);
    }

    public static function logSituationComplete($situationId, $optionId, $userId = null)
    {
        return self::logEvent(\App\Enums\ActivityEventType::SITUATION_COMPLETED->value, [
            'situation_id' => $situationId,
            'option_id' => $optionId,
        ], $userId);
    }

    public static function logMicroActionPerform($microActionId, $userId = null)
    {
        return self::logEvent(\App\Enums\ActivityEventType::MICRO_ACTION_PERFORMED->value, [
            'micro_action_id' => $microActionId,
        ], $userId);
    }
}
