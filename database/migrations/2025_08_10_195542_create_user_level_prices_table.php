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
        Schema::create('user_level_prices', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_level_id')->comment('用户等级ID');
            $table->tinyInteger('cycle')->comment('周期 0月 1季 2年')->default(0);
            $table->decimal('price', 10)->comment('价格')->default(0);
            $table->comment('用户等级价格表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_level_prices');
    }
};
