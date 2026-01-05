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
        Schema::table('users', static function (Blueprint $table) {
            $table->integer('temp_quota')->default(0)->comment('临时额度');
            $table->date('temp_quota_date')->nullable()->comment('临时额度日期');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', static function (Blueprint $table) {
            $table->dropColumn(['temp_quota', 'temp_quota_date']);
        });
    }
};

