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
        Schema::table('shops', static function (Blueprint $table) {
            $table->string('backup_contact_name')->comment('备用联系人')->nullable()->after('contact_phone');
            $table->string('backup_contact_phone')->comment('备用联系方式')->nullable()->after('backup_contact_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shops', static function (Blueprint $table) {
            $table->dropColumn(['backup_contact_name', 'backup_contact_phone']);
        });
    }
};
