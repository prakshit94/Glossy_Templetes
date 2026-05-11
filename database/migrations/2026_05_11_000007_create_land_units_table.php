<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('land_units', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->decimal('conversion_to_sq_mt', 15, 6)->nullable();
            $table->string('status')->default('active')->index();
            $table->timestamps();
            $table->softDeletes()->index();

            // Optimization for high data load
            $table->index('created_at');
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('land_units');
    }
};
