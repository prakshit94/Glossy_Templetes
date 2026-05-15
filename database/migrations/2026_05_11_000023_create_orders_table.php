<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no')->unique();
            $table->enum('type', ['sale', 'purchase'])->index();
            $table->foreignId('party_id')->constrained()->cascadeOnDelete();
            $table->dateTime('order_date');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2)->default(0);
            $table->enum('status', ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'])->default('pending')->index();
            $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();

            // Address FK references (merged from add_shipping_address_id & add_billing_address_id)
            $table->foreignId('shipping_address_id')->nullable()->constrained('party_addresses')->nullOnDelete();
            $table->foreignId('billing_address_id')->nullable()->constrained('party_addresses')->nullOnDelete();

            // Denormalised address text for receipts (merged from add_full_address_fields)
            $table->text('shipping_address')->nullable();
            $table->text('billing_address')->nullable();

            // User tracking (merged from add_user_tracking_to_orders)
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes()->index();

            // Optimization for high data load
            $table->index('created_at');
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('orders');
    }
};
