<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminWebController;

Route::get('/', function () {
    return view('welcome');
});

// === DEFAULT LOGIN ROUTE ===
Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

// === ADMIN ROUTES ===
Route::prefix('admin')->name('admin.')->group(function () {
    
    // Public admin routes (login)
    Route::get('login', [AdminWebController::class, 'login'])->name('login');
    Route::post('authenticate', [AdminWebController::class, 'authenticate'])->name('authenticate');
    
    // Protected admin routes - Base admin access required for all
    Route::middleware(['auth', 'permission:admin.access'])->group(function () {
        
        // Dashboard - Basic admin access
        Route::get('/', [AdminWebController::class, 'dashboard'])->name('dashboard');
        Route::get('/realtime', function () {
            return view('admin.realtime-dashboard');
        })->name('realtime');
        
        // Metrics API для веб-админки
        Route::prefix('metrics')->name('metrics.')->group(function () {
            Route::get('dashboard', [\App\Http\Controllers\Admin\RealtimeMetricsController::class, 'getDashboardData'])->name('dashboard');
            Route::get('current', [\App\Http\Controllers\Admin\RealtimeMetricsController::class, 'getCurrentMetrics'])->name('current');
        });
        
        Route::post('logout', [AdminWebController::class, 'logout'])->name('logout');
        
        // User management - Specific permissions
        Route::prefix('users')->name('users.')->group(function () {
            Route::middleware(['permission:users.view'])->group(function () {
                Route::get('/', [AdminWebController::class, 'users'])->name('index');
                Route::get('{id}', [AdminWebController::class, 'userShow'])->name('show');
            });
            
            // Role management (Super Admin / Admin only)
            Route::middleware(['permission:users.manage-roles'])->group(function () {
                Route::patch('{id}/toggle-admin', [AdminWebController::class, 'userToggleAdmin'])->name('toggle-admin');
                Route::post('{id}/assign-role', [AdminWebController::class, 'userAssignRole'])->name('assign-role');
                Route::delete('{userId}/remove-role/{roleId}', [AdminWebController::class, 'userRemoveRole'])->name('remove-role');
            });
        });
        
        // Role & Permission Management (Super Admin only)
        Route::prefix('roles')->name('roles.')->middleware(['permission:users.manage-roles'])->group(function () {
            Route::get('/', [AdminWebController::class, 'roles'])->name('index');
            Route::get('create', [AdminWebController::class, 'roleCreate'])->name('create');
            Route::post('/', [AdminWebController::class, 'roleStore'])->name('store');
        });
        
        // Situations management - Granular permissions
        Route::prefix('situations')->name('situations.')->group(function () {
            Route::middleware(['permission:situations.view'])->get('/', [AdminWebController::class, 'situations'])->name('index');
            Route::middleware(['permission:situations.view'])->get('export-template', [AdminWebController::class, 'situationsExportTemplate'])->name('export-template');
            Route::middleware(['permission:situations.create'])->post('import', [AdminWebController::class, 'situationsImport'])->name('import');
            Route::middleware(['permission:situations.create'])->get('create', [AdminWebController::class, 'situationCreate'])->name('create');
            Route::middleware(['permission:situations.create'])->post('/', [AdminWebController::class, 'situationStore'])->name('store');
            Route::middleware(['permission:situations.edit'])->get('{id}/edit', [AdminWebController::class, 'situationEdit'])->name('edit');
            Route::middleware(['permission:situations.edit'])->patch('{id}', [AdminWebController::class, 'situationUpdate'])->name('update');
            Route::middleware(['permission:situations.delete'])->delete('{id}', [AdminWebController::class, 'situationDestroy'])->name('destroy');
        });
        
        // Micro-actions management
        Route::prefix('micro-actions')->name('micro-actions.')->group(function () {
            Route::middleware(['permission:situations.view'])->get('/', [AdminWebController::class, 'microActions'])->name('index');
            Route::middleware(['permission:situations.create'])->get('create', [AdminWebController::class, 'microActionCreate'])->name('create');
            Route::middleware(['permission:situations.create'])->post('/', [AdminWebController::class, 'microActionStore'])->name('store');
            Route::middleware(['permission:situations.view'])->get('{id}', [AdminWebController::class, 'microActionEdit'])->name('edit');
            Route::middleware(['permission:situations.edit'])->patch('{id}', [AdminWebController::class, 'microActionUpdate'])->name('update');
            Route::middleware(['permission:situations.delete'])->delete('{id}', [AdminWebController::class, 'microActionDestroy'])->name('destroy');
        });
        
        // Game configurations - View and Edit permissions
        Route::prefix('configs')->name('configs.')->group(function () {
            Route::middleware(['permission:configs.view'])->get('/', [AdminWebController::class, 'configs'])->name('index');
            Route::middleware(['permission:configs.edit'])->patch('{key}', [AdminWebController::class, 'configUpdate'])->name('update');
        });

        // Customization management - Same permissions as configs
        Route::prefix('customization')->name('customization.')->group(function () {
            Route::middleware(['permission:configs.view'])->get('/', [AdminWebController::class, 'customizationItems'])->name('index');
            Route::middleware(['permission:configs.edit'])->get('create', [AdminWebController::class, 'customizationItemCreate'])->name('create');
            Route::middleware(['permission:configs.edit'])->post('/', [AdminWebController::class, 'customizationItemStore'])->name('store');
            Route::middleware(['permission:configs.edit'])->get('{id}/edit', [AdminWebController::class, 'customizationItemEdit'])->name('edit');
            Route::middleware(['permission:configs.edit'])->patch('{id}', [AdminWebController::class, 'customizationItemUpdate'])->name('update');
            Route::middleware(['permission:configs.edit'])->delete('{id}', [AdminWebController::class, 'customizationItemDestroy'])->name('destroy');
        });
        
        // Analytics (if needed in future)
        Route::prefix('analytics')->name('analytics.')->middleware(['permission:analytics.view'])->group(function () {
            Route::get('/', function () { 
                return redirect()->route('admin.dashboard')->with('info', 'Расширенная аналитика в разработке'); 
            })->name('index');
        });
    });
});

// API Documentation
Route::get('/docs', function () {
    $docsPath = base_path('docs/build/index.html');
    
    if (file_exists($docsPath)) {
        return response()->file($docsPath);
    }
    
    return response()->json([
        'message' => 'Документация не найдена. Выполните сборку: cd docs && npm run build'
    ], 404);
});

// Static files for documentation (CSS, JS, etc.)
Route::get('/docs/{path}', function ($path) {
    $filePath = base_path('docs/build/' . $path);
    
    if (file_exists($filePath) && is_file($filePath)) {
        $mimeType = mime_content_type($filePath);
        return response()->file($filePath, ['Content-Type' => $mimeType]);
    }
    
    return abort(404);
})->where('path', '.*');
