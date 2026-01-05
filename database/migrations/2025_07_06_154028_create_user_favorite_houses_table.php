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
        Schema::create('user_favorite_houses', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('用户ID');
            $table->unsignedBigInteger('house_id')->comment('房源ID');
            $table->timestamps();
            $table->comment('用户收藏房源表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_favorite_houses');
    }
};
