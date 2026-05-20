<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        DB::table('orders')->where('status', 'shipped')->update(['status' => 'dispatched']);
    }

    public function down(): void
    {
        // Cannot reliably restore which rows were originally "shipped".
    }
};
