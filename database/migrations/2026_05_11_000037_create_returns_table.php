<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_no')->unique();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('reason')->nullable();
            $table->decimal('refund_amount', 15, 2)->default(0);
            $table->enum('status', ['requested', 'received', 'inspected', 'completed', 'rejected'])->default('requested')->index();
            $table->timestamps();
            $table->softDeletes()->index();

            // Optimization for high data load
            $table->index('created_at');
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('returns');
    }
};
