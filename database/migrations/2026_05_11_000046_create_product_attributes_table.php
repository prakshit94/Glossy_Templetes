<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('type')->default('select'); // select, color, text
            $table->string('status')->default('active')->index();
            $table->boolean('is_filterable')->default(true);
            $table->timestamps();
            $table->softDeletes()->index();

            // Optimization for high data load
            $table->index('created_at');
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('product_attributes');
    }
};
