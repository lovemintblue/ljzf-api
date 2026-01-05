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
        Schema::table('houses', static function (Blueprint $table) {
            $table->tinyInteger('is_delegated')->comment('是否委托发布 0否 1是')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('houses', static function (Blueprint $table) {
            $table->dropColumn('is_delegated');
        });
    }
};
