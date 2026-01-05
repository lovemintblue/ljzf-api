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
        Schema::table('house_follow_ups', function (Blueprint $table) {
            if (!Schema::hasColumn('house_follow_ups', 'is_processed')) {
                $table->boolean('is_processed')->default(false)->comment('是否已处理（因跟进记录下架过房源）')->after('is_punished');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('house_follow_ups', function (Blueprint $table) {
            $table->dropColumn('is_processed');
        });
    }
};

