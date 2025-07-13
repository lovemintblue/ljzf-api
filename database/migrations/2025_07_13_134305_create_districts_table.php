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
        Schema::create('districts', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->comment('父级ID')->default(0);
            $table->string('name')->comment('名称');
            $table->string('full_name')->comment('全称');
            $table->json('pinyin')->comment('拼音')->nullable();
            $table->tinyInteger('level')->comment('行政区划级别')->default(0);
            $table->json('location')->comment('经纬度')->nullable();
            $table->comment('行政区划表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('districts');
    }
};
