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
use App\Http\Controllers\Web\OrderReturnController;
use App\Http\Controllers\Web\InvoiceController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Web\DashboardController::class, 'index'])->name('dashboard');

    Route::post('/users/bulk-delete', [UserController::class, 'bulkDelete'])->name('users.bulk-delete');
    Route::post('/users/bulk-restore', [UserController::class, 'bulkRestore'])->name('users.bulk-restore');
    Route::post('/users/bulk-force-delete', [UserController::class, 'bulkForceDelete'])->name('users.bulk-force-delete');
    Route::post('/users/bulk-status', [UserController::class, 'bulkStatus'])->name('users.bulk-status');
    Route::post('/users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
    Route::delete('/users/{id}/force-delete', [UserController::class, 'forceDelete'])->name('users.force-delete');
    Route::resource('users', UserController::class)->middleware('permission:users.view');
    Route::post('/roles/bulk-delete', [\App\Http\Controllers\Web\RoleController::class, 'bulkDelete'])->name('roles.bulk-delete');
    Route::resource('roles', \App\Http\Controllers\Web\RoleController::class)->middleware('permission:roles.view');
    Route::post('/teams/bulk-delete', [\App\Http\Controllers\Web\TeamController::class, 'bulkDelete'])->name('teams.bulk-delete');
    Route::resource('teams', \App\Http\Controllers\Web\TeamController::class)->middleware('permission:teams.view');
    
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
    Route::resource('villages', \App\Http\Controllers\Web\VillageController::class)->middleware('permission:villages.view');
    Route::post('/services/bulk-delete', [ServiceController::class, 'bulkDelete'])->name('services.bulk-delete');
    Route::post('/services/bulk-status', [ServiceController::class, 'bulkStatusUpdate'])->name('services.bulk-status');
    Route::get('/services/{service}/villages', [ServiceController::class, 'getVillages'])->name('services.villages');
    Route::resource('services', ServiceController::class)->middleware('permission:services.view');

    // Catalog & Inventory
    Route::get('/products-search-api', [ProductController::class, 'searchApi'])->name('products.search.api');
    Route::post('/products/bulk-delete', [ProductController::class, 'bulkDelete'])->name('products.bulk-delete');
    Route::post('/products/{id}/restore', [ProductController::class, 'restore'])->name('products.restore');
    Route::delete('/products/{id}/force-delete', [ProductController::class, 'forceDelete'])->name('products.force-delete');
    Route::resource('products', ProductController::class)->middleware('permission:products.view');
    Route::post('/categories/bulk-delete', [CategoryController::class, 'bulkDelete'])->name('categories.bulk-delete');
    Route::resource('categories', CategoryController::class)->middleware('permission:categories.view');
    Route::post('/brands/bulk-delete', [BrandController::class, 'bulkDelete'])->name('brands.bulk-delete');
    Route::resource('brands', BrandController::class)->middleware('permission:brands.view');
    Route::resource('attributes', ProductAttributeController::class)->middleware('permission:attributes.view');
    Route::post('attributes/{attribute}/values', [ProductAttributeController::class, 'storeValue'])->name('attributes.values.store');
    Route::delete('attribute-values/{value}', [ProductAttributeController::class, 'destroyValue'])->name('attribute-values.destroy');
    Route::resource('uoms', UnitOfMeasureController::class)->middleware('permission:uoms.view');
    Route::resource('tax-rates', TaxRateController::class)->middleware('permission:tax-rates.view');
    Route::resource('hsn-codes', HsnCodeController::class)->middleware('permission:hsn-codes.view');
    Route::get('inventory/export', [InventoryController::class, 'export'])->name('inventory.export');
    Route::post('inventory/import', [InventoryController::class, 'import'])->name('inventory.import');
    Route::resource('inventory', InventoryController::class)->middleware('permission:inventory.view');
    Route::resource('warehouses', \App\Http\Controllers\Web\WarehouseController::class)->middleware('permission:warehouses.view');
    Route::get('warehouses/{warehouse}/stock', [\App\Http\Controllers\Web\WarehouseController::class, 'getStock'])->name('warehouses.stock');
    
    Route::resource('stock-transfers', \App\Http\Controllers\Web\StockTransferController::class)->names('transfers');
    Route::post('stock-transfers/{transfer}/send', [\App\Http\Controllers\Web\StockTransferController::class, 'send'])->name('transfers.send');
    Route::post('stock-transfers/{transfer}/receive', [\App\Http\Controllers\Web\StockTransferController::class, 'receive'])->name('transfers.receive');
    Route::post('stock-transfers/{transfer}/cancel', [\App\Http\Controllers\Web\StockTransferController::class, 'cancel'])->name('transfers.cancel');

    Route::resource('stock-adjustments', \App\Http\Controllers\Web\StockAdjustmentController::class)->names('adjustments');
    Route::post('stock-adjustments/{adjustment}/approve', [\App\Http\Controllers\Web\StockAdjustmentController::class, 'approve'])->name('adjustments.approve');
    Route::post('stock-adjustments/{adjustment}/reject', [\App\Http\Controllers\Web\StockAdjustmentController::class, 'reject'])->name('adjustments.reject');
    Route::post('orders/bulk-status', [OrderController::class, 'bulkStatus'])->name('orders.bulk-status');
    Route::post('orders/bulk-verification', [OrderController::class, 'bulkStoreVerification'])->name('orders.bulk-verification');
    Route::get('orders/bulk-print', [OrderController::class, 'bulkPrint'])->name('orders.bulk-print');
    Route::get('orders/export', [OrderController::class, 'bulkExport'])->name('orders.export');
    Route::post('orders/import', [OrderController::class, 'bulkImport'])->name('orders.import');
    Route::get('orders/import-template', [OrderController::class, 'bulkImportTemplate'])->name('orders.import-template');
    Route::get('orders/{order}/invoice-pdf', [OrderController::class, 'downloadInvoice'])->name('orders.invoice-pdf');
    Route::post('orders/{order}/generate-invoice', [OrderController::class, 'generateInvoice'])->name('orders.generate-invoice');
    Route::get('orders/{order}/cod-pdf', [OrderController::class, 'downloadReceipt'])->name('orders.cod-pdf');
    // BUG-25 FIX: Custom action routes declared BEFORE Route::resource() per Laravel convention.
    Route::post('orders/{order}/confirm', [OrderController::class, 'confirm'])->name('orders.confirm');
    Route::post('orders/{order}/ship', [OrderController::class, 'ship'])->name('orders.ship');
    Route::post('orders/{order}/dispatch', [OrderController::class, 'dispatch'])->name('orders.dispatch');
    Route::post('orders/{order}/processing', [OrderController::class, 'markProcessing'])->name('orders.processing');
    Route::post('orders/{order}/deliver', [OrderController::class, 'markDelivered'])->name('orders.deliver');
    Route::post('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('orders/{order}/revert-status', [OrderController::class, 'revertStatus'])->name('orders.revert-status');
    Route::get('orders/{order}/receipt', [OrderController::class, 'receipt'])->name('orders.receipt');
    Route::post('orders/{order}/verification', [OrderController::class, 'storeVerification'])->name('orders.verification.store');
    Route::resource('orders', OrderController::class)->middleware('permission:orders.view');

    Route::resource('returns', OrderReturnController::class)->middleware('permission:returns.view');
    Route::post('returns/{return}/status', [OrderReturnController::class, 'updateStatus'])->name('returns.status');

    Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('payments/search-orders', [\App\Http\Controllers\Web\PaymentController::class, 'searchOrders'])->name('payments.search-orders');
    Route::post('payments/bulk-upload', [\App\Http\Controllers\Web\PaymentController::class, 'bulkUpload'])->name('payments.bulk-upload');
    Route::resource('payments', \App\Http\Controllers\Web\PaymentController::class)->middleware('permission:payments.view');

    Route::post('refunds/{refund}/status', [\App\Http\Controllers\Web\RefundController::class, 'updateStatus'])->name('refunds.status');
    Route::resource('refunds', \App\Http\Controllers\Web\RefundController::class)->except(['edit', 'update', 'destroy'])->middleware('permission:refunds.view');

    // Order / Shipment Tracking URLs mapped to OrderTrackingController
    Route::get('shipment-tracking', [\App\Http\Controllers\Web\OrderTrackingController::class, 'index'])->name('order.tracking.index');
    Route::get('shipment-tracking/performance', [\App\Http\Controllers\Web\DeliveryPerformanceController::class, 'index'])->name('delivery.performance.index');
    Route::get('shipment-tracking/performance/partners/{driver}', [\App\Http\Controllers\Web\DeliveryPerformanceController::class, 'partner'])->name('delivery.performance.partner');
    Route::get('shipment-tracking/performance/couriers/{provider}', [\App\Http\Controllers\Web\DeliveryPerformanceController::class, 'courier'])->name('delivery.performance.courier');
    Route::get('shipment-tracking/{shipment}', [\App\Http\Controllers\Web\OrderTrackingController::class, 'show'])->name('order.tracking.show');
    Route::post('shipment-tracking/{shipment}/events', [\App\Http\Controllers\Web\OrderTrackingController::class, 'storeEvent'])->name('order.tracking.events.store');
    Route::put('shipment-tracking/{shipment}/status', [\App\Http\Controllers\Web\OrderTrackingController::class, 'updateStatus'])->name('order.tracking.status.update');
    Route::put('shipment-tracking/events/{event}', [\App\Http\Controllers\Web\OrderTrackingController::class, 'updateEvent'])->name('order.tracking.events.update');
    Route::delete('shipment-tracking/events/{event}', [\App\Http\Controllers\Web\OrderTrackingController::class, 'destroyEvent'])->name('order.tracking.events.destroy');

    Route::get('order-tracking', [\App\Http\Controllers\Web\OrderTrackingController::class, 'index']);
    Route::get('order-tracking/performance', [\App\Http\Controllers\Web\DeliveryPerformanceController::class, 'index']);
    Route::get('order-tracking/performance/partners/{driver}', [\App\Http\Controllers\Web\DeliveryPerformanceController::class, 'partner']);
    Route::get('order-tracking/performance/couriers/{provider}', [\App\Http\Controllers\Web\DeliveryPerformanceController::class, 'courier']);
    Route::get('order-tracking/{shipment}', [\App\Http\Controllers\Web\OrderTrackingController::class, 'show']);

    // Promo Codes / Coupons

Route::post(
    '/coupons/validate',
    [App\Http\Controllers\Web\CouponController::class, 'validateApi']
)->name('coupons.validate');

Route::post(
    '/coupons/bulk-status',
    [App\Http\Controllers\Web\CouponController::class, 'bulkStatus']
)->name('coupons.bulk-status');

Route::post(
    '/coupons/bulk-delete',
    [App\Http\Controllers\Web\CouponController::class, 'bulkDelete']
)->name('coupons.bulk-delete');

Route::resource(
    'coupons',
    App\Http\Controllers\Web\CouponController::class
)->middleware('permission:coupons.view');

// Offers

Route::post(
    '/offers/bulk-status',
    [App\Http\Controllers\Web\OfferController::class, 'bulkStatus']
)->name('offers.bulk-status');

Route::post(
    '/offers/bulk-delete',
    [App\Http\Controllers\Web\OfferController::class, 'bulkDelete']
)->name('offers.bulk-delete');

Route::resource(
    'offers',
    App\Http\Controllers\Web\OfferController::class
)->except(['show'])->middleware('permission:offers.view');


    // Transport Management
    Route::post('/transport/bulk-delete', [\App\Http\Controllers\Web\TransportController::class, 'bulkDelete'])->name('transport.bulk-delete');
    Route::post('/transport/store', [\App\Http\Controllers\Web\TransportController::class, 'store'])->name('transport.store');
    Route::resource('transport', \App\Http\Controllers\Web\TransportController::class)->except(['create', 'edit', 'store'])->middleware('permission:transport.view');

    // Drivers Management
    Route::post('/drivers/bulk-delete', [\App\Http\Controllers\Web\DriverController::class, 'bulkDelete'])->name('drivers.bulk-delete');
    Route::post('/drivers/store', [\App\Http\Controllers\Web\DriverController::class, 'store'])->name('drivers.store');
    Route::resource('drivers', \App\Http\Controllers\Web\DriverController::class)->except(['create', 'edit', 'store'])->middleware('permission:drivers.view');

    // Delivery Management
    Route::post('/delivery/bulk-delete', [\App\Http\Controllers\Web\DeliveryController::class, 'bulkDelete'])->name('delivery.bulk-delete');
    Route::post('/delivery/assign', [\App\Http\Controllers\Web\DeliveryController::class, 'assign'])->name('delivery.assign');
    Route::post('/delivery/{delivery}/deliver', [\App\Http\Controllers\Web\DeliveryController::class, 'markDelivered'])->name('delivery.deliver');
    Route::post('/delivery/{delivery}/verification', [\App\Http\Controllers\Web\DeliveryController::class, 'storeVerification'])->name('delivery.verification.store');
    Route::resource('delivery', \App\Http\Controllers\Web\DeliveryController::class)->except(['create', 'edit'])->middleware('permission:delivery.view');

    Route::get('accounts', [\App\Http\Controllers\Web\AccountController::class, 'index'])->name('accounts.index')->middleware('permission:accounts.view');
    Route::get('expenses', [\App\Http\Controllers\Web\ExpenseController::class, 'index'])->name('expenses.index')->middleware('permission:expenses.view');
    Route::get('transactions', [\App\Http\Controllers\Web\AccountingTransactionController::class, 'index'])->name('transactions.index')->middleware('permission:transactions.view');
    Route::get('financial-reports', [\App\Http\Controllers\Web\FinancialReportController::class, 'index'])->name('financial.reports.index')->middleware('permission:financial-reports.view');

    Route::get('departments', [\App\Http\Controllers\Web\DepartmentController::class, 'index'])->name('departments.index')->middleware('permission:departments.view');
    Route::get('employees', [\App\Http\Controllers\Web\EmployeeController::class, 'index'])->name('employees.index')->middleware('permission:employees.view');
    Route::get('attendance', [\App\Http\Controllers\Web\AttendanceController::class, 'index'])->name('attendance.index')->middleware('permission:attendance.view');
    Route::get('payroll', [\App\Http\Controllers\Web\PayrollController::class, 'index'])->name('payroll.index')->middleware('permission:payroll.view');

    $sidebarScaffoldModules = [
        'customer-groups' => ['title' => 'Customer Groups', 'icon' => 'users-2'],
        'reviews' => ['title' => 'Reviews & Ratings', 'icon' => 'star'],
        'support-tickets' => ['title' => 'Support Tickets', 'icon' => 'mail'],
        'replacement' => ['title' => 'Replacement', 'icon' => 'package'],
        'purchase-orders' => ['title' => 'Purchase Orders', 'icon' => 'purchase'],
        'suppliers' => ['title' => 'Suppliers', 'icon' => 'building'],
        'vendors' => ['title' => 'Vendors', 'icon' => 'building'],
        'sales-reports' => ['title' => 'Sales Reports', 'icon' => 'reports'],
        'inventory-reports' => ['title' => 'Inventory Reports', 'icon' => 'inventory'],
        'customer-analytics' => ['title' => 'Customer Analytics', 'icon' => 'users'],
        'performance-reports' => ['title' => 'Performance Reports', 'icon' => 'activity'],
        'campaigns' => ['title' => 'Campaigns', 'icon' => 'marketing'],
        'email-marketing' => ['title' => 'Email Marketing', 'icon' => 'mail'],
    ];

    foreach ($sidebarScaffoldModules as $uri => $meta) {
        Route::get($uri, [\App\Http\Controllers\Web\ScaffoldController::class, 'show'])
             ->defaults('uri', $uri)
             ->defaults('title', $meta['title'])
             ->defaults('icon', $meta['icon'])
             ->name(str_replace('-', '.', $uri) . '.index');
    }

    // Customer Management
    Route::get('/customers/search-by-phone', [\App\Http\Controllers\Web\CustomerController::class, 'searchByPhone'])->name('customers.search-by-phone');
    Route::post('/customers/bulk-delete', [\App\Http\Controllers\Web\CustomerController::class, 'bulkDelete'])->name('customers.bulk-delete');
    Route::post('/customers/bulk-restore', [\App\Http\Controllers\Web\CustomerController::class, 'bulkRestore'])->name('customers.bulk-restore');
    Route::post('/customers/bulk-force-delete', [\App\Http\Controllers\Web\CustomerController::class, 'bulkForceDelete'])->name('customers.bulk-force-delete');
    Route::post('/customers/bulk-status', [\App\Http\Controllers\Web\CustomerController::class, 'bulkStatus'])->name('customers.bulk-status');
    Route::post('/customers/{id}/restore', [\App\Http\Controllers\Web\CustomerController::class, 'restore'])->name('customers.restore');
    Route::delete('/customers/{id}/force-delete', [\App\Http\Controllers\Web\CustomerController::class, 'forceDelete'])->name('customers.force-delete');
    Route::resource('customers', \App\Http\Controllers\Web\CustomerController::class)->middleware('permission:customers.view');

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
