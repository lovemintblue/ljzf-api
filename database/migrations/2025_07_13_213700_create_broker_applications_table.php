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
        Schema::create('broker_applications', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('用户ID');
            $table->tinyInteger('status')->comment('状态 0审核中 1已通过 2已驳回')->default(0);
            $table->string('refuse_reason')->comment('驳回原因')->nullable();
            $table->timestamps();
            $table->comment('经纪人申请表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('broker_applications');
    }
};
