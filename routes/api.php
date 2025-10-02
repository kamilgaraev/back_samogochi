<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->middleware('throttle.game:10,1')->group(function () {
    Route::post('register', [\App\Http\Controllers\Api\AuthController::class, 'register']);
    Route::post('login', [\App\Http\Controllers\Api\AuthController::class, 'login']);
    Route::post('refresh', [\App\Http\Controllers\Api\AuthController::class, 'refresh']);
    Route::post('logout', [\App\Http\Controllers\Api\AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('verify-email', [\App\Http\Controllers\Api\AuthController::class, 'verifyEmail']);
    Route::post('forgot-password', [\App\Http\Controllers\Api\AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [\App\Http\Controllers\Api\AuthController::class, 'resetPassword']);
});

Route::middleware(['auth:api', 'throttle.game:120,1'])->group(function () {
    Route::prefix('player')->group(function () {
        Route::get('profile', [\App\Http\Controllers\Api\PlayerController::class, 'profile']);
        Route::put('profile', [\App\Http\Controllers\Api\PlayerController::class, 'updateProfile']);
        Route::put('personal-info', [\App\Http\Controllers\Api\PlayerController::class, 'updatePersonalInfo']);
        Route::get('stats', [\App\Http\Controllers\Api\PlayerController::class, 'stats']);
        Route::get('progress', [\App\Http\Controllers\Api\PlayerController::class, 'progress']);
        Route::post('daily-reward', [\App\Http\Controllers\Api\PlayerController::class, 'claimDailyReward']);
        Route::post('add-experience', [\App\Http\Controllers\Api\PlayerController::class, 'addExperience']);
        Route::post('update-energy', [\App\Http\Controllers\Api\PlayerController::class, 'updateEnergy']);
        Route::post('update-stress', [\App\Http\Controllers\Api\PlayerController::class, 'updateStress']);
    });

    Route::prefix('situations')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\SituationController::class, 'index']);
        Route::get('random', [\App\Http\Controllers\Api\SituationController::class, 'random']);
        Route::get('random-recommended', [\App\Http\Controllers\Api\SituationController::class, 'randomRecommended']);
        Route::get('active', [\App\Http\Controllers\Api\SituationController::class, 'active']);
        Route::get('history', [\App\Http\Controllers\Api\SituationController::class, 'history']);
        Route::get('recommendations', [\App\Http\Controllers\Api\SituationController::class, 'recommendations']);
        Route::get('{id}', [\App\Http\Controllers\Api\SituationController::class, 'show']);
        Route::post('{id}/start', [\App\Http\Controllers\Api\SituationController::class, 'start']);
        Route::post('{id}/complete', [\App\Http\Controllers\Api\SituationController::class, 'complete']);
    });

    Route::prefix('micro-actions')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\MicroActionController::class, 'index']);
        Route::get('recommendations', [\App\Http\Controllers\Api\MicroActionController::class, 'recommendations']);
        Route::get('recommendations/random', [\App\Http\Controllers\Api\MicroActionController::class, 'randomRecommendation']);
        Route::get('history', [\App\Http\Controllers\Api\MicroActionController::class, 'history']);
        Route::post('{id}/perform', [\App\Http\Controllers\Api\MicroActionController::class, 'perform']);
    });
});

Route::middleware(['auth:api', 'admin', 'throttle.game:30,1'])->prefix('admin')->group(function () {
    Route::get('configs', [\App\Http\Controllers\Api\AdminController::class, 'configs']);
    Route::put('configs/{key}', [\App\Http\Controllers\Api\AdminController::class, 'updateConfig']);
    
    Route::prefix('situations')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\AdminController::class, 'situations']);
        Route::post('/', [\App\Http\Controllers\Api\AdminController::class, 'createSituation']);
        Route::put('{id}', [\App\Http\Controllers\Api\AdminController::class, 'updateSituation']);
        Route::delete('{id}', [\App\Http\Controllers\Api\AdminController::class, 'deleteSituation']);
    });

    Route::prefix('metrics')->group(function () {
        Route::get('current', [\App\Http\Controllers\Admin\RealtimeMetricsController::class, 'getCurrentMetrics']);
        Route::get('dashboard', [\App\Http\Controllers\Admin\RealtimeMetricsController::class, 'getDashboardData']);
        Route::get('health', [\App\Http\Controllers\Admin\RealtimeMetricsController::class, 'getSystemHealth']);
        Route::get('{metric}/history', [\App\Http\Controllers\Admin\RealtimeMetricsController::class, 'getMetricHistory']);
        Route::get('{metric}/trend', [\App\Http\Controllers\Admin\RealtimeMetricsController::class, 'getMetricTrend']);
    });
});

Route::middleware(['auth:api', 'throttle.game:60,1'])->prefix('analytics')->group(function () {
    Route::get('dashboard', [\App\Http\Controllers\Api\AnalyticsController::class, 'dashboard']);
    Route::get('player-behavior', [\App\Http\Controllers\Api\AnalyticsController::class, 'playerBehavior']);
    Route::get('situation-stats', [\App\Http\Controllers\Api\AnalyticsController::class, 'situationStats']);
    Route::get('activity-stats', [\App\Http\Controllers\Api\AnalyticsController::class, 'activityStats']);
});
