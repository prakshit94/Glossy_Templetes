<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('parties', function (Blueprint $table) {
            $table->string('firstname')->nullable()->after('name');
            $table->string('middlename')->nullable()->after('firstname');
            $table->string('lastname')->nullable()->after('middlename');
            $table->string('alternatemobile')->nullable()->after('phone');
            $table->string('relative_mobile')->nullable()->after('alternatemobile');
        });
    }

    public function down(): void
    {
        Schema::table('parties', function (Blueprint $table) {
            $table->dropColumn(['firstname', 'middlename', 'lastname', 'alternatemobile', 'relative_mobile']);
        });
    }
};
