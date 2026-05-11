<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Advanced Composite Indexes for High Performance
        
        Schema::table('stocks', function (Blueprint $table) {
            $table->index(['product_id', 'warehouse_id'], 'idx_stocks_product_warehouse');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->index(['order_id', 'product_id'], 'idx_order_items_lookup');
        });

        Schema::table('ledger_entries', function (Blueprint $table) {
            $table->index(['ledger_id', 'entry_date'], 'idx_ledger_statement_lookup');
        });

        Schema::table('attendance', function (Blueprint $table) {
            $table->index(['employee_id', 'date'], 'idx_attendance_employee_date');
        });

        // Fulltext Indexes (If database supports it, usually MySQL 5.6+ / MariaDB 10.0.5+)
        Schema::table('products', function (Blueprint $table) {
            $table->fullText(['name', 'sku', 'description'], 'ft_product_search');
        });

        Schema::table('parties', function (Blueprint $table) {
            $table->fullText(['name', 'email', 'phone'], 'ft_party_search');
        });
    }

    public function down(): void {
        // Handled by dropping the tables in their respective migrations
    }
};
