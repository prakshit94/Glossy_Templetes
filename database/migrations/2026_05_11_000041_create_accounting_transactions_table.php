<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('accounting_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_no')->unique();
            $table->string('reference_no')->nullable()->index();
            $table->date('transaction_date')->index();
            $table->string('description')->nullable();
            $table->string('status')->default('active')->index();
            $table->timestamps();
            $table->softDeletes()->index();

            // Optimization for high data load
            $table->index('created_at');
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('accounting_transactions');
    }
};
