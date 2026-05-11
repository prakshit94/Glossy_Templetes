<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('product_attribute_mapping', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_value_id')->constrained('product_attribute_values')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['product_id', 'attribute_value_id'], 'prod_attr_map_idx');
        });
    }

    public function down(): void {
        Schema::dropIfExists('product_attribute_mapping');
    }
};
