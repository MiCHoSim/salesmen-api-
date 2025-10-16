<?php

use App\Http\Controllers\CodelistController;
use App\Http\Controllers\SalesmanController;
use Illuminate\Support\Facades\Route;

Route::apiResource('salesmen', SalesmanController::class);

Route::get('/codelists', [CodelistController::class, 'index']);

// Health check endpoint
Route::get('/health', function () {
    return response()->json(['status' => 'OK']);
});
