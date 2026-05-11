<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('parties', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('type')->index(); // customer, supplier, vendor, etc.
            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable()->index();
            $table->string('gst_no')->nullable()->index();
            $table->string('pan_no')->nullable()->index();
            $table->string('tax_no')->nullable();
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->integer('credit_days')->default(0);
            $table->string('status')->default('active')->index();
            $table->boolean('is_active')->default(true);
            $table->foreignId('account_type_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes()->index();

            // Optimization for high data load
            $table->index('created_at');
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('parties');
    }
};
