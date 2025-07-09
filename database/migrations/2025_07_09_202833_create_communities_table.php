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
        Schema::create('communities', static function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('名称');
            $table->string('address')->comment('地址');
            $table->timestamps();
            $table->comment('小区表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communities');
    }
};
