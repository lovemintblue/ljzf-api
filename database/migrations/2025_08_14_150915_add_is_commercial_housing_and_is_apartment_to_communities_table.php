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
        Schema::table('communities', static function (Blueprint $table) {
            $table->tinyInteger('is_commercial_housing')->comment('是否为商品房')->default(0);
            $table->tinyInteger('is_apartment')->comment('是否为公寓')->comment(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('communities', static function (Blueprint $table) {
            $table->dropColumn('is_commercial_housing');
            $table->dropColumn('is_apartment');
        });
    }
};
