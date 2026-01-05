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
            if (!Schema::hasColumn('house_follow_ups', 'is_punished')) {
                $table->boolean('is_punished')->default(false)->comment('是否已处罚')->after('result');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('house_follow_ups', function (Blueprint $table) {
            $table->dropColumn('is_punished');
        });
    }
};

