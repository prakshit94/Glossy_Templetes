<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->string('address_line_1')->nullable()->after('address');
            $table->string('address_line_2')->nullable()->after('address_line_1');
            $table->foreignId('village_id')->nullable()->after('address_line_2')->constrained('villages')->nullOnDelete();
            $table->string('village_name')->nullable()->after('village_id');
            $table->string('post_office')->nullable()->after('village_name');
            $table->string('taluka')->nullable()->after('post_office');
            $table->string('city')->nullable()->after('taluka');
            $table->string('pincode')->nullable()->after('state');
        });
    }

    public function down(): void {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropForeign(['village_id']);
            $table->dropColumn([
                'address_line_1',
                'address_line_2',
                'village_id',
                'village_name',
                'post_office',
                'taluka',
                'city',
                'pincode'
            ]);
        });
    }
};
