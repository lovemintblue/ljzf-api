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
        Schema::create('user_level_orders', static function (Blueprint $table) {
            $table->id();
            $table->string('no')->comment('订单号')->unique();
            $table->unsignedBigInteger('user_id')->comment('用户ID');
            $table->unsignedBigInteger('user_level_id')->comment('用户等级ID');
            $table->decimal('total_amount', 10)->comment('订单金额')->default(0);
            $table->tinyInteger('status')->comment('状态 0已支付 1未支付')->default(0);
            $table->timestamps();
            $table->comment('会员订单表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_level_orders');
    }
};
