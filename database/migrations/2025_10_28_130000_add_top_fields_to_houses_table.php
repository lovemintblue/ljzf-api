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
        Schema::table('houses', function (Blueprint $table) {
            $table->boolean('is_top')->default(false)->after('is_show')->comment('是否置顶');
            $table->timestamp('top_at')->nullable()->after('is_top')->comment('置顶时间');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('houses', function (Blueprint $table) {
            $table->dropColumn(['is_top', 'top_at']);
        });
    }
};

