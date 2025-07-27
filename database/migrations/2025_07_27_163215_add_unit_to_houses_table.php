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
        Schema::table('houses', static function (Blueprint $table) {
            $table->string('unit')->comment('单元')->nullable();
            $table->string('deposit_method')->comment('押金方式')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('houses', static function (Blueprint $table) {
            $table->dropColumn('unit');
            $table->dropColumn('deposit_method');
        });
    }
};
