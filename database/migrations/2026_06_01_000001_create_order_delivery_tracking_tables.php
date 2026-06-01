<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('delivery_tracking_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_terminal')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        DB::table('delivery_tracking_statuses')->insert([
            ['code' => 'created', 'name' => 'Created', 'sort_order' => 10, 'is_terminal' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'ready_for_dispatch', 'name' => 'Ready For Dispatch', 'sort_order' => 20, 'is_terminal' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'dispatched', 'name' => 'Dispatched', 'sort_order' => 30, 'is_terminal' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'in_transit', 'name' => 'In Transit', 'sort_order' => 40, 'is_terminal' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'out_for_delivery', 'name' => 'Out For Delivery', 'sort_order' => 50, 'is_terminal' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'delivered', 'name' => 'Delivered', 'sort_order' => 60, 'is_terminal' => true, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'delivery_failed', 'name' => 'Delivery Failed', 'sort_order' => 70, 'is_terminal' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'return_initiated', 'name' => 'Return Initiated', 'sort_order' => 80, 'is_terminal' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'returned', 'name' => 'Returned', 'sort_order' => 90, 'is_terminal' => true, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'lost', 'name' => 'Lost', 'sort_order' => 100, 'is_terminal' => true, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'cancelled', 'name' => 'Cancelled', 'sort_order' => 110, 'is_terminal' => true, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        Schema::create('order_delivery_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('shipment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('delivery_id')->nullable()->constrained()->nullOnDelete();
            $table->string('parcel_id')->nullable()->index();
            $table->string('dispatch_type')->default('unknown')->index();
            $table->foreignId('driver_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('transport_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('courier_provider_name')->nullable()->index();
            $table->string('tracking_number')->nullable()->index();
            $table->string('vehicle_number')->nullable()->index();
            $table->string('current_status')->default('created')->index();
            $table->dateTime('dispatch_date')->nullable()->index();
            $table->dateTime('delivered_date')->nullable()->index();
            $table->dateTime('returned_date')->nullable()->index();
            $table->dateTime('last_status_at')->nullable()->index();
            $table->text('last_remarks')->nullable();
            $table->timestamps();

            $table->index(['dispatch_type', 'current_status']);
            $table->index(['driver_id', 'current_status']);
            $table->index(['courier_provider_name', 'current_status'], 'odt_courier_status_idx');
            $table->index(['dispatch_date', 'current_status']);
        });

        Schema::create('order_delivery_tracking_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_delivery_tracking_id');
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('previous_status')->nullable()->index();
            $table->string('new_status')->index();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->dateTime('changed_at')->index();
            $table->timestamps();

            $table->index(['order_id', 'changed_at']);
            $table->index(['new_status', 'changed_at']);
            $table->foreign('order_delivery_tracking_id', 'odth_tracking_fk')
                ->references('id')
                ->on('order_delivery_trackings')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_delivery_tracking_histories');
        Schema::dropIfExists('order_delivery_trackings');
        Schema::dropIfExists('delivery_tracking_statuses');
    }
};
