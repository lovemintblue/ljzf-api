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
        Schema::create('house_operation_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('house_id')->comment('房源ID');
            $table->unsignedBigInteger('operator_id')->nullable()->comment('操作人ID（管理员ID）');
            $table->string('operator_type', 20)->default('admin')->comment('操作人类型：admin=管理员, user=用户');
            $table->string('operation_type', 20)->comment('操作类型：publish=首次发布, offline=下架, update=更新排序, online=重新上架');
            $table->text('reason')->nullable()->comment('操作原因（如下架原因）');
            $table->json('metadata')->nullable()->comment('其他元数据');
            $table->timestamps();
            
            // 索引
            $table->index('house_id');
            $table->index('operator_id');
            $table->index('operation_type');
            $table->index('created_at');
            
            $table->comment('房源操作日志表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('house_operation_logs');
    }
};

