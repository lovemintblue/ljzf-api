<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL 的 ENUM 字段修改需要用原生 SQL
        DB::statement("ALTER TABLE shops MODIFY COLUMN payment_method ENUM('月付', '季付', '半年付', '年付') COMMENT '付款方式' DEFAULT '月付'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 回滚时恢复原来的枚举值
        DB::statement("ALTER TABLE shops MODIFY COLUMN payment_method ENUM('押一付三', '押二付三', '押三付三', '半年付', '年付') COMMENT '付款方式' DEFAULT '押一付三'");
    }
};

