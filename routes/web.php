<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminWebController;

Route::get('/', function () {
    return view('welcome');
});

// === ADMIN ROUTES ===
Route::prefix('admin')->name('admin.')->group(function () {
    
    // Public admin routes (login)
    Route::get('login', [AdminWebController::class, 'login'])->name('login');
    Route::post('authenticate', [AdminWebController::class, 'authenticate'])->name('authenticate');
    
    // Protected admin routes
    Route::middleware(['auth', 'admin'])->group(function () {
        
        // Dashboard
        Route::get('/', [AdminWebController::class, 'dashboard'])->name('dashboard');
        Route::post('logout', [AdminWebController::class, 'logout'])->name('logout');
        
        // User management
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [AdminWebController::class, 'users'])->name('index');
            Route::get('{id}', [AdminWebController::class, 'userShow'])->name('show');
            Route::patch('{id}/toggle-admin', [AdminWebController::class, 'userToggleAdmin'])->name('toggle-admin');
        });
        
        // Situations management
        Route::prefix('situations')->name('situations.')->group(function () {
            Route::get('/', [AdminWebController::class, 'situations'])->name('index');
            Route::get('create', [AdminWebController::class, 'situationCreate'])->name('create');
            Route::post('/', [AdminWebController::class, 'situationStore'])->name('store');
            Route::get('{id}/edit', [AdminWebController::class, 'situationEdit'])->name('edit');
            Route::patch('{id}', [AdminWebController::class, 'situationUpdate'])->name('update');
            Route::delete('{id}', [AdminWebController::class, 'situationDestroy'])->name('destroy');
        });
        
        // Micro-actions management (placeholder routes)
        Route::prefix('micro-actions')->name('micro-actions.')->group(function () {
            Route::get('/', function () { return redirect()->route('admin.dashboard')->with('info', 'Микро-действия скоро будут доступны'); })->name('index');
        });
        
        // Game configurations
        Route::prefix('configs')->name('configs.')->group(function () {
            Route::get('/', [AdminWebController::class, 'configs'])->name('index');
            Route::patch('{key}', [AdminWebController::class, 'configUpdate'])->name('update');
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
