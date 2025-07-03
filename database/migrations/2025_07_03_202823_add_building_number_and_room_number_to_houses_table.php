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
            $table->string('building_number')->comment('栋数')->nullable();
            $table->string('room_number')->comment('房间号')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('houses', static function (Blueprint $table) {
            $table->dropColumn('building_number', 'room_number');
        });
    }
};
