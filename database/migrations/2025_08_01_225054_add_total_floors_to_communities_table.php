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
            $table->string('total_floors')->comment('总楼层')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('communities', static function (Blueprint $table) {
            $table->dropColumn('total_floors');
        });
    }
};
