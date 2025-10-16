<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'api' => 'Salesmen API',
        'version' => '1.0.0',
        'status' => 'operational',
        'timestamp' => now()->toISOString()
    ]);
});
