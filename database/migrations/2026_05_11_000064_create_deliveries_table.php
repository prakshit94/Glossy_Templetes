<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('transport_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['pending', 'out_for_delivery', 'delivered', 'failed'])->default('pending')->index();
            $table->dateTime('delivered_at')->nullable();
            $table->string('proof_of_delivery')->nullable(); // Image path
            $table->timestamps();
            $table->softDeletes()->index();

            // Optimization for high data load
            $table->index('created_at');
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('deliveries');
    }
};
