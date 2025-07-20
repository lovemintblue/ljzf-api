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
            $table->unsignedBigInteger('business_district_id')->comment('商圈ID')->default(0);
            $table->string('province')->comment('省份')->nullable();
            $table->string('city')->comment('城市')->nullable();
            $table->string('district')->comment('区县')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('communities', static function (Blueprint $table) {
            $table->dropColumn('province', 'city', 'district', 'business_district_id');
        });
    }
};
