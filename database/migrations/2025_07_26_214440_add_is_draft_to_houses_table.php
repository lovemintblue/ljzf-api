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
            $table->tinyInteger('is_draft')->comment('草稿 0否 1是')->default(0);
            $table->string('longitude')->comment('经度')->nullable();
            $table->string('latitude')->comment('纬度')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('houses', static function (Blueprint $table) {
            $table->dropColumn('is_draft');
            $table->dropColumn('longitude');
            $table->dropColumn('latitude');
        });
    }
};
