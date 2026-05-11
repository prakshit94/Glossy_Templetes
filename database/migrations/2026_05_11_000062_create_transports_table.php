<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('transports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('vehicle_number')->unique();
            $table->string('type')->nullable(); // Truck, Van, Bike, etc.
            $table->decimal('capacity_weight', 15, 2)->nullable();
            $table->enum('status', ['available', 'on_delivery', 'maintenance', 'inactive'])->default('available')->index();
            $table->timestamps();
            $table->softDeletes()->index();

            // Optimization for high data load
            $table->index('created_at');
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('transports');
    }
};
