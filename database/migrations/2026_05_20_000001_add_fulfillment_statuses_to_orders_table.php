<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('orders') || !Schema::hasColumn('orders', 'status')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
                'pending',
                'confirmed',
                'processing',
                'ready_to_ship',
                'dispatched',
                'shipped',
                'delivered',
                'cancelled',
                'returned'
            ) NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('orders') || !Schema::hasColumn('orders', 'status')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            // Normalize rows that use statuses removed on rollback.
            DB::table('orders')
                ->whereIn('status', ['ready_to_ship', 'dispatched'])
                ->update(['status' => 'processing']);

            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
                'pending',
                'confirmed',
                'processing',
                'shipped',
                'delivered',
                'cancelled',
                'returned'
            ) NOT NULL DEFAULT 'pending'");
        }
    }
};
