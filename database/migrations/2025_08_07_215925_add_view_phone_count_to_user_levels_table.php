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
        Schema::table('user_levels', static function (Blueprint $table) {
            $table->integer('view_phone_count')->comment('查看电话次数')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_levels', static function (Blueprint $table) {
            $table->dropColumn('view_phone_count');
        });
    }
};
