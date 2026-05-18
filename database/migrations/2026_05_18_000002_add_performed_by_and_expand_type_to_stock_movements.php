<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            // Track which user performed the movement (null for system/import actions)
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete()->after('status');
        });

        // Expand the type enum to include 'reserve' and 'release' movement types
        // MySQL-compatible ALTER for enum expansion
        DB::statement("ALTER TABLE stock_movements MODIFY COLUMN type ENUM('in','out','adjustment','transfer','reserve','release') NOT NULL");
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['performed_by']);
            $table->dropColumn('performed_by');
        });

        DB::statement("ALTER TABLE stock_movements MODIFY COLUMN type ENUM('in','out','adjustment','transfer') NOT NULL");
    }
};
