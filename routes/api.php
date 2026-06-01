<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\UserController;
use App\Http\Controllers\Api\v1\TeamController;
use App\Http\Controllers\Api\Chat\ConversationController as ChatConversationController;
use App\Http\Controllers\Api\Chat\GroupController as ChatGroupController;
use App\Http\Controllers\Api\Chat\MessageController as ChatMessageController;
use App\Http\Controllers\Api\Chat\PresenceController as ChatPresenceController;
use App\Http\Controllers\Api\Chat\SearchController as ChatSearchController;
use App\Http\Controllers\Api\Chat\UserController as ChatUserController;

Route::prefix('v1')->middleware('throttle:api')->group(function () {
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

        Route::prefix('chat')->group(function () {
            Route::get('/conversations', [ChatConversationController::class, 'index']);
            Route::post('/conversations', [ChatConversationController::class, 'store']);
            Route::get('/conversations/{conversation}', [ChatConversationController::class, 'show']);
            Route::post('/conversations/{conversation}/archive', [ChatConversationController::class, 'archive']);
            Route::post('/conversations/{conversation}/pin', [ChatConversationController::class, 'pin']);
            Route::get('/conversations/{conversation}/messages', [ChatMessageController::class, 'index']);
            Route::post('/conversations/{conversation}/messages', [ChatMessageController::class, 'store']);
            Route::post('/conversations/{conversation}/read', [ChatMessageController::class, 'markRead']);
            Route::put('/messages/{message}', [ChatMessageController::class, 'update']);
            Route::delete('/messages/{message}', [ChatMessageController::class, 'destroy']);
            Route::post('/messages/{message}/edit', [ChatMessageController::class, 'update']);
            Route::post('/messages/{message}/delete', [ChatMessageController::class, 'destroy']);
            Route::post('/messages/{message}/forward', [ChatMessageController::class, 'forward']);
            Route::put('/groups/{conversation}', [ChatGroupController::class, 'update']);
            Route::delete('/groups/{conversation}', [ChatGroupController::class, 'destroy']);
            Route::post('/groups/{conversation}/members', [ChatGroupController::class, 'addMember']);
            Route::delete('/groups/{conversation}/members', [ChatGroupController::class, 'removeMember']);
            Route::put('/groups/{conversation}/members/role', [ChatGroupController::class, 'updateRole']);
            Route::post('/groups/{conversation}/transfer-owner', [ChatGroupController::class, 'transferOwner']);
            Route::post('/groups/{conversation}/leave', [ChatGroupController::class, 'leave']);
            Route::get('/presence', [ChatPresenceController::class, 'index']);
            Route::post('/presence', [ChatPresenceController::class, 'update']);
            Route::get('/users', [ChatUserController::class, 'index']);
            Route::get('/search', ChatSearchController::class);
        });

        // Village Service Management
        Route::prefix('logistics')->group(function () {
            Route::get('/villages', [\App\Http\Controllers\Api\VillageApiController::class, 'index']);
            Route::get('/villages/search', [\App\Http\Controllers\Api\VillageApiController::class, 'search']);
            Route::get('/serviceability/check', [\App\Http\Controllers\Api\VillageApiController::class, 'checkServiceability']);
            Route::post('/village-services/bulk-import', [\App\Http\Controllers\Api\VillageApiController::class, 'bulkImport']);
            Route::get('/order-trackings', [\App\Http\Controllers\Api\v1\OrderDeliveryTrackingController::class, 'index']);
            Route::get('/order-trackings/performance', [\App\Http\Controllers\Api\v1\OrderDeliveryTrackingController::class, 'performance']);
            Route::get('/order-trackings/{tracking}', [\App\Http\Controllers\Api\v1\OrderDeliveryTrackingController::class, 'show']);
        });
    });
});
