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
        Schema::table('houses', static function (Blueprint $table) {
            $table->string('watermark_video')->comment('水印视频')->nullable();
            $table->json('watermark_images')->comment('水印照片')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('houses', static function (Blueprint $table) {
            $table->dropColumn('watermark_video','watermark_images');
        });
    }
};
