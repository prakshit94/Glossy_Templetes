<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('address')->nullable();
            $table->string('state')->nullable();
            $table->string('status')->default('active')->index();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes()->index();

            // Optimization for high data load
            $table->index('created_at');
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('warehouses');
    }
};
