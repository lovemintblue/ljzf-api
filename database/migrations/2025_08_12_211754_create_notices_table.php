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
        Schema::create('notices', static function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('标题');
            $table->longText('content')->comment('内容')->nullable();
            $table->tinyInteger('status')->comment('状态 0禁用 1启用')->default(1);
            $table->timestamps();
            $table->comment('公告表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notices');
    }
};
