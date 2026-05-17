<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\ServiceController;
use App\Http\Controllers\Web\ProductController;
use App\Http\Controllers\Web\CategoryController;
use App\Http\Controllers\Web\BrandController;
use App\Http\Controllers\Web\ProductAttributeController;
use App\Http\Controllers\Web\UnitOfMeasureController;
use App\Http\Controllers\Web\TaxRateController;
use App\Http\Controllers\Web\HsnCodeController;
use App\Http\Controllers\Web\InventoryController;
use App\Http\Controllers\Web\OrderController;

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
    Route::get('/villages-search', [\App\Http\Controllers\Web\VillageController::class, 'search'])->name('villages.search');
    Route::resource('villages', \App\Http\Controllers\Web\VillageController::class);
    Route::post('/services/bulk-delete', [ServiceController::class, 'bulkDelete'])->name('services.bulk-delete');
    Route::post('/services/bulk-status', [ServiceController::class, 'bulkStatusUpdate'])->name('services.bulk-status');
    Route::get('/services/{service}/villages', [ServiceController::class, 'getVillages'])->name('services.villages');
    Route::resource('services', ServiceController::class);

    // Catalog & Inventory
    Route::get('/products-search-api', [ProductController::class, 'searchApi'])->name('products.search.api');
    Route::post('/products/bulk-delete', [ProductController::class, 'bulkDelete'])->name('products.bulk-delete');
    Route::post('/products/{id}/restore', [ProductController::class, 'restore'])->name('products.restore');
    Route::delete('/products/{id}/force-delete', [ProductController::class, 'forceDelete'])->name('products.force-delete');
    Route::resource('products', ProductController::class);
    Route::post('/categories/bulk-delete', [CategoryController::class, 'bulkDelete'])->name('categories.bulk-delete');
    Route::resource('categories', CategoryController::class);
    Route::post('/brands/bulk-delete', [BrandController::class, 'bulkDelete'])->name('brands.bulk-delete');
    Route::resource('brands', BrandController::class);
    Route::resource('attributes', ProductAttributeController::class);
    Route::post('attributes/{attribute}/values', [ProductAttributeController::class, 'storeValue'])->name('attributes.values.store');
    Route::delete('attribute-values/{value}', [ProductAttributeController::class, 'destroyValue'])->name('attribute-values.destroy');
    Route::resource('uoms', UnitOfMeasureController::class);
    Route::resource('tax-rates', TaxRateController::class);
    Route::resource('hsn-codes', HsnCodeController::class);
    Route::get('inventory/export', [InventoryController::class, 'export'])->name('inventory.export');
    Route::post('inventory/import', [InventoryController::class, 'import'])->name('inventory.import');
    Route::resource('inventory', InventoryController::class);
    Route::resource('warehouses', \App\Http\Controllers\Web\WarehouseController::class);
    Route::get('warehouses/{warehouse}/stock', [\App\Http\Controllers\Web\WarehouseController::class, 'getStock'])->name('warehouses.stock');
    
    Route::resource('stock-transfers', \App\Http\Controllers\Web\StockTransferController::class)->names('transfers');
    Route::post('stock-transfers/{transfer}/send', [\App\Http\Controllers\Web\StockTransferController::class, 'send'])->name('transfers.send');
    Route::post('stock-transfers/{transfer}/receive', [\App\Http\Controllers\Web\StockTransferController::class, 'receive'])->name('transfers.receive');
    Route::post('stock-transfers/{transfer}/cancel', [\App\Http\Controllers\Web\StockTransferController::class, 'cancel'])->name('transfers.cancel');

    Route::resource('stock-adjustments', \App\Http\Controllers\Web\StockAdjustmentController::class)->names('adjustments');
    Route::post('stock-adjustments/{adjustment}/approve', [\App\Http\Controllers\Web\StockAdjustmentController::class, 'approve'])->name('adjustments.approve');
    Route::post('stock-adjustments/{adjustment}/reject', [\App\Http\Controllers\Web\StockAdjustmentController::class, 'reject'])->name('adjustments.reject');
    Route::post('orders/bulk-status', [OrderController::class, 'bulkStatus'])->name('orders.bulk-status');
    Route::resource('orders', OrderController::class);
    Route::post('orders/{order}/confirm', [OrderController::class, 'confirm'])->name('orders.confirm');
    Route::post('orders/{order}/ship', [OrderController::class, 'ship'])->name('orders.ship');
    Route::post('orders/{order}/processing', [OrderController::class, 'markProcessing'])->name('orders.processing');
    Route::post('orders/{order}/deliver', [OrderController::class, 'markDelivered'])->name('orders.deliver');
    Route::post('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::get('orders/{order}/receipt', [OrderController::class, 'receipt'])->name('orders.receipt');

    // Order / Shipment Tracking URLs mapped to OrderTrackingController
    Route::get('shipment-tracking', [\App\Http\Controllers\Web\OrderTrackingController::class, 'index'])->name('order.tracking.index');
    Route::get('shipment-tracking/{shipment}', [\App\Http\Controllers\Web\OrderTrackingController::class, 'show'])->name('order.tracking.show');
    Route::post('shipment-tracking/{shipment}/events', [\App\Http\Controllers\Web\OrderTrackingController::class, 'storeEvent'])->name('order.tracking.events.store');
    Route::put('shipment-tracking/{shipment}/status', [\App\Http\Controllers\Web\OrderTrackingController::class, 'updateStatus'])->name('order.tracking.status.update');
    Route::put('shipment-tracking/events/{event}', [\App\Http\Controllers\Web\OrderTrackingController::class, 'updateEvent'])->name('order.tracking.events.update');
    Route::delete('shipment-tracking/events/{event}', [\App\Http\Controllers\Web\OrderTrackingController::class, 'destroyEvent'])->name('order.tracking.events.destroy');

    Route::get('order-tracking', [\App\Http\Controllers\Web\OrderTrackingController::class, 'index']);
    Route::get('order-tracking/{shipment}', [\App\Http\Controllers\Web\OrderTrackingController::class, 'show']);

    $sidebarScaffoldModules = [
        'customer-groups' => ['title' => 'Customer Groups', 'icon' => 'users-2'],
        'reviews' => ['title' => 'Reviews & Ratings', 'icon' => 'star'],
        'support-tickets' => ['title' => 'Support Tickets', 'icon' => 'mail'],
        'invoices' => ['title' => 'Invoices', 'icon' => 'finance'],
        'payments' => ['title' => 'Payments', 'icon' => 'credit-card'],
        'returns' => ['title' => 'Returns', 'icon' => 'return'],
        'refunds' => ['title' => 'Refunds', 'icon' => 'refresh-cw'],
        'replacement' => ['title' => 'Replacement', 'icon' => 'package'],
        'purchase-orders' => ['title' => 'Purchase Orders', 'icon' => 'purchase'],
        'suppliers' => ['title' => 'Suppliers', 'icon' => 'building'],
        'vendors' => ['title' => 'Vendors', 'icon' => 'building'],
        'transport' => ['title' => 'Transport', 'icon' => 'truck'],
        'delivery' => ['title' => 'Delivery', 'icon' => 'truck-2'],
        'drivers' => ['title' => 'Drivers', 'icon' => 'users-2'],
        'accounts' => ['title' => 'Accounts', 'icon' => 'finance'],
        'expenses' => ['title' => 'Expenses', 'icon' => 'activity'],
        'transactions' => ['title' => 'Transactions', 'icon' => 'credit-card'],
        'financial-reports' => ['title' => 'Financial Reports', 'icon' => 'bar-chart'],
        'sales-reports' => ['title' => 'Sales Reports', 'icon' => 'reports'],
        'inventory-reports' => ['title' => 'Inventory Reports', 'icon' => 'inventory'],
        'customer-analytics' => ['title' => 'Customer Analytics', 'icon' => 'users'],
        'performance-reports' => ['title' => 'Performance Reports', 'icon' => 'activity'],
        'employees' => ['title' => 'Employees', 'icon' => 'employees'],
        'attendance' => ['title' => 'Attendance', 'icon' => 'calendar'],
        'payroll' => ['title' => 'Payroll', 'icon' => 'finance'],
        'departments' => ['title' => 'Departments', 'icon' => 'building'],
        'campaigns' => ['title' => 'Campaigns', 'icon' => 'marketing'],
        'coupons' => ['title' => 'Coupons', 'icon' => 'gift'],
        'email-marketing' => ['title' => 'Email Marketing', 'icon' => 'mail'],
    ];

    foreach ($sidebarScaffoldModules as $uri => $meta) {
        Route::get($uri, function () use ($uri, $meta) {
            return view("{$uri}.index", [
                'moduleKey' => $uri,
                'moduleTitle' => $meta['title'],
                'moduleIcon' => $meta['icon'],
            ]);
        })->name(str_replace('-', '.', $uri) . '.index');
    }

    // Customer Management
    Route::get('/customers/search-by-phone', [\App\Http\Controllers\Web\CustomerController::class, 'searchByPhone'])->name('customers.search-by-phone');
    Route::post('/customers/bulk-delete', [\App\Http\Controllers\Web\CustomerController::class, 'bulkDelete'])->name('customers.bulk-delete');
    Route::post('/customers/bulk-restore', [\App\Http\Controllers\Web\CustomerController::class, 'bulkRestore'])->name('customers.bulk-restore');
    Route::post('/customers/bulk-force-delete', [\App\Http\Controllers\Web\CustomerController::class, 'bulkForceDelete'])->name('customers.bulk-force-delete');
    Route::post('/customers/bulk-status', [\App\Http\Controllers\Web\CustomerController::class, 'bulkStatus'])->name('customers.bulk-status');
    Route::post('/customers/{id}/restore', [\App\Http\Controllers\Web\CustomerController::class, 'restore'])->name('customers.restore');
    Route::delete('/customers/{id}/force-delete', [\App\Http\Controllers\Web\CustomerController::class, 'forceDelete'])->name('customers.force-delete');
    Route::resource('customers', \App\Http\Controllers\Web\CustomerController::class);

    // Customer Addresses
    Route::post('/customers/{customer}/orders/place', [\App\Http\Controllers\Web\CustomerController::class, 'placeOrder'])->name('customers.orders.place');
    Route::post('customers/{customer}/addresses', [\App\Http\Controllers\Web\CustomerAddressController::class, 'store'])->name('customers.addresses.store');
    Route::put('customers/{customer}/addresses/{address}', [\App\Http\Controllers\Web\CustomerAddressController::class, 'update'])->name('customers.addresses.update');
    Route::delete('customers/{customer}/addresses/{address}', [\App\Http\Controllers\Web\CustomerAddressController::class, 'destroy'])->name('customers.addresses.destroy');
});

Route::post('/logout', function () {
    Auth::logout();
    return redirect()->route('login');
})->name('logout');
