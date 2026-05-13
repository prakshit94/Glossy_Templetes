<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::transaction(function () {
            $duplicates = DB::table('stocks')
                ->select('product_id', 'warehouse_id', DB::raw('COUNT(*) as aggregate'))
                ->groupBy('product_id', 'warehouse_id')
                ->having('aggregate', '>', 1)
                ->get();

            foreach ($duplicates as $duplicate) {
                $rows = DB::table('stocks')
                    ->where('product_id', $duplicate->product_id)
                    ->where('warehouse_id', $duplicate->warehouse_id)
                    ->orderBy('id')
                    ->get();

                if ($rows->count() < 2) {
                    continue;
                }

                $keeper = $rows->first();
                DB::table('stocks')
                    ->where('id', $keeper->id)
                    ->update([
                        'quantity' => $rows->sum('quantity'),
                        'reserved_qty' => $rows->sum('reserved_qty'),
                        'committed_qty' => $rows->sum('committed_qty'),
                        'in_transit_qty' => $rows->sum('in_transit_qty'),
                        'updated_at' => now(),
                    ]);

                DB::table('stocks')->whereIn('id', $rows->skip(1)->pluck('id')->all())->delete();
            }
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->unique(['product_id', 'warehouse_id'], 'stocks_product_warehouse_unique');
        });
    }

    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropUnique('stocks_product_warehouse_unique');
        });
    }
};
