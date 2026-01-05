<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_level_orders', static function (Blueprint $table) {
            $table->tinyInteger('cycle')->comment('周期 0月 1季 2年')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_level_orders', function (Blueprint $table) {
            $table->dropColumn('cycle');
        });
    }
};
