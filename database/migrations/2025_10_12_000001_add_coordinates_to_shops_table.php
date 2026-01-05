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
        Schema::table('shops', function (Blueprint $table) {
            // 检查字段是否存在，避免重复添加
            if (!Schema::hasColumn('shops', 'latitude')) {
                $table->string('latitude', 50)->nullable()->comment('纬度')->after('address');
            }
            if (!Schema::hasColumn('shops', 'longitude')) {
                $table->string('longitude', 50)->nullable()->comment('经度')->after('latitude');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};

