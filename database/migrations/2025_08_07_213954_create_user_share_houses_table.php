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
        Schema::create('user_share_houses', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('用户id');
            $table->string('contact_phone')->comment('联系方式');
            $table->json('house_ids')->comment('房源ids')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_share_houses');
    }
};
