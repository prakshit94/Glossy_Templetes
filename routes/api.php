<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\UserController;
use App\Http\Controllers\Api\v1\TeamController;

Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);

        // User management
        Route::apiResource('users', UserController::class)->names('api.users');
        Route::post('/users/{user}/suspend', [UserController::class, 'suspend'])->middleware('can:users.edit')->name('api.users.suspend');
        Route::post('/users/{user}/activate', [UserController::class, 'activate'])->middleware('can:users.edit')->name('api.users.activate');

        // Team management
        Route::apiResource('teams', TeamController::class)->names('api.teams');
        Route::post('/teams/{team}/invite', [TeamController::class, 'invite'])->middleware('can:teams.manage')->name('api.teams.invite');

        // MFA
        Route::prefix('mfa')->group(function () {
            Route::post('/enable', [AuthController::class, 'enableMfa']);
            Route::post('/verify', [AuthController::class, 'verifyMfa']);
            Route::post('/disable', [AuthController::class, 'disableMfa']);
        });
    });
});
