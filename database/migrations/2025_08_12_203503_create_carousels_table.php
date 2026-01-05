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
        Schema::create('carousels', static function (Blueprint $table) {
            $table->id();
            $table->string('image')->comment('图片')->nullable();
            $table->integer('sort')->comment('排序')->default(0);
            $table->tinyInteger('status')->comment('状态 0禁用 1启用')->default(1);
            $table->comment('广告轮播表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carousels');
    }
};
