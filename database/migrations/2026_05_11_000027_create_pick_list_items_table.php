<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pick_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pick_list_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity_to_pick', 15, 4);
            $table->decimal('quantity_picked', 15, 4)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('pick_list_items');
    }
};
