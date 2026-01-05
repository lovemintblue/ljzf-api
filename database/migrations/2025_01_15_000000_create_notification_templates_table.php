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
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('通知代码');
            $table->string('name')->comment('通知名称');
            $table->string('title')->comment('通知标题');
            $table->text('content')->comment('通知内容');
            $table->boolean('is_enabled')->default(true)->comment('是否启用');
            $table->text('variables')->nullable()->comment('可用变量说明');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
