<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// API Documentation
Route::get('/docs', function () {
    $docsPath = base_path('docs/build/index.html');
    
    if (file_exists($docsPath)) {
        return response()->file($docsPath);
    }
    
    return response()->json([
        'message' => 'Документация не найдена. Выполните: cd docs && npm install && npm run build',
        'commands' => [
            'cd docs',
            'npm install', 
            'npm run build'
        ]
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
