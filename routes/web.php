<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\ServiceController;
use App\Http\Controllers\Web\ProductController;
use App\Http\Controllers\Web\CategoryController;
use App\Http\Controllers\Web\InventoryController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::post('/users/bulk-delete', [UserController::class, 'bulkDelete'])->name('users.bulk-delete');
    Route::post('/users/bulk-restore', [UserController::class, 'bulkRestore'])->name('users.bulk-restore');
    Route::post('/users/bulk-force-delete', [UserController::class, 'bulkForceDelete'])->name('users.bulk-force-delete');
    Route::post('/users/bulk-status', [UserController::class, 'bulkStatus'])->name('users.bulk-status');
    Route::post('/users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
    Route::delete('/users/{id}/force-delete', [UserController::class, 'forceDelete'])->name('users.force-delete');
    Route::resource('users', UserController::class);
    Route::post('/roles/bulk-delete', [\App\Http\Controllers\Web\RoleController::class, 'bulkDelete'])->name('roles.bulk-delete');
    Route::resource('roles', \App\Http\Controllers\Web\RoleController::class);
    Route::post('/teams/bulk-delete', [\App\Http\Controllers\Web\TeamController::class, 'bulkDelete'])->name('teams.bulk-delete');
    Route::resource('teams', \App\Http\Controllers\Web\TeamController::class);
    
    // System Activity
    Route::get('/activities', [\App\Http\Controllers\Web\ActivityController::class, 'index'])->name('activities.index');
    Route::post('/activities/read', [\App\Http\Controllers\Web\ActivityController::class, 'markAsRead'])->name('activities.read');
    Route::post('/activities/bulk-delete', [\App\Http\Controllers\Web\ActivityController::class, 'bulkDelete'])->name('activities.bulk-delete');
    Route::get('/permissions', [\App\Http\Controllers\Web\PermissionController::class, 'index'])->name('permissions.index');
    
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Web\SettingsController::class, 'index'])->name('index');
        Route::put('/update', [\App\Http\Controllers\Web\SettingsController::class, 'update'])->name('update');
        Route::post('/clear-cache', [\App\Http\Controllers\Web\SettingsController::class, 'clearCache'])->name('clear-cache');
    });

    // Village & Service Management
    Route::post('/villages/import', [\App\Http\Controllers\Web\VillageController::class, 'import'])->name('villages.import');
    Route::post('/villages/bulk-delete', [\App\Http\Controllers\Web\VillageController::class, 'bulkDelete'])->name('villages.bulk-delete');
    Route::post('/villages/bulk-service', [\App\Http\Controllers\Web\VillageController::class, 'bulkServiceUpdate'])->name('villages.bulk-service');
    Route::resource('villages', \App\Http\Controllers\Web\VillageController::class);
    Route::post('/services/bulk-delete', [ServiceController::class, 'bulkDelete'])->name('services.bulk-delete');
    Route::post('/services/bulk-status', [ServiceController::class, 'bulkStatusUpdate'])->name('services.bulk-status');
    Route::get('/services/{service}/villages', [ServiceController::class, 'getVillages'])->name('services.villages');
    Route::resource('services', ServiceController::class);

    // Catalog & Inventory
    Route::post('/products/bulk-delete', [ProductController::class, 'bulkDelete'])->name('products.bulk-delete');
    Route::resource('products', ProductController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('inventory', InventoryController::class);
});

Route::post('/logout', function () {
    Auth::logout();
    return redirect()->route('login');
})->name('logout');
