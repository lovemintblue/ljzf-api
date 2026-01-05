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
        Schema::table('facilities', static function (Blueprint $table) {
            $table->dropColumn('type');
            $table->json('type')->comment('类型 0房源 1店铺')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facilities', static function (Blueprint $table) {
            $table->dropColumn('type');
            $table->tinyInteger('type')->comment('类型 0房源 1商铺')->default(0);
        });
    }
};
