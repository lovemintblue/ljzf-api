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
            $table->tinyInteger('audit_status')->comment('审核状态 0审核中 1审核通过 2审核驳回')->default(0);
            $table->string('refuse_reason')->comment('驳回原因')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('houses', static function (Blueprint $table) {
            $table->dropColumn('audit_status');
        });
    }
};
