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
            if (!Schema::hasColumn('shops', 'floor_height')) {
                $table->decimal('floor_height', 8, 2)->nullable()->comment('层高（米）')->after('area');
            }
            if (!Schema::hasColumn('shops', 'frontage')) {
                $table->decimal('frontage', 8, 2)->nullable()->comment('面宽（米）')->after('floor_height');
            }
            if (!Schema::hasColumn('shops', 'depth')) {
                $table->decimal('depth', 8, 2)->nullable()->comment('进深（米）')->after('frontage');
            }
            // description 字段已存在，跳过
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            // 只删除本次新增的3个字段，description 已存在不删除
            $table->dropColumn(['floor_height', 'frontage', 'depth']);
        });
    }
};

