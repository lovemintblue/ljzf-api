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
        Schema::create('user_levels', static function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('名称');
            $table->integer('level')->comment('等级')->default(0);
            $table->decimal('price', 10)->comment('价格')->default(0);
            $table->decimal('original_price', 10)->comment('原价')->default(0);
            $table->tinyInteger('is_recommend')->comment('是否推荐 0否 1是')->default(0);
            $table->tinyInteger('is_good_value')->comment('是否超值 0否 1是')->default(0);
            $table->tinyInteger('cycle')->comment('周期 0月 1季 2年')->default(0);
            $table->json('privilege')->comment('特权')->nullable();
            $table->comment('用户等级表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_levels');
    }
};
