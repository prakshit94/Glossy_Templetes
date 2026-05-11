<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('payroll', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employee_profiles')->cascadeOnDelete();
            $table->integer('month');
            $table->integer('year');
            $table->decimal('basic_salary', 15, 2);
            $table->decimal('bonus', 15, 2)->default(0);
            $table->decimal('deductions', 15, 2)->default(0);
            $table->decimal('net_salary', 15, 2);
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending')->index();
            $table->timestamps();
            $table->softDeletes()->index();

            $table->unique(['employee_id', 'month', 'year']);
            // Optimization for high data load
            $table->index('created_at');
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('payroll');
    }
};
