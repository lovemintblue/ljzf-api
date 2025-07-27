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
            $table->dropColumn('business_district_id');
            $table->json('business_district_ids')->comment('商圈ids')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('communities', static function (Blueprint $table) {
            $table->unsignedBigInteger('business_district_id')->comment('商圈ids')->nullable();
            $table->dropColumn('business_district_ids');
        });
    }
};
