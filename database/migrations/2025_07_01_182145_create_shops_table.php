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
        Schema::create('shops', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('用户ID');
            $table->string('title')->comment('标题');
            $table->tinyInteger('type')->comment('类型 0零售 1餐饮 2办公 3其他')->default(0);
            $table->decimal('area', 10)->comment('面积')->default(0);
            $table->integer('floor')->comment('楼层')->default(0);
            $table->integer('total_floors')->comment('总楼层')->default(0);
            $table->enum('renovation', ['毛坯', '简装', '精装修'])->comment('装修')->default('毛坯');
            $table->decimal('rent_price', 10)->comment('租金')->default(0);
            $table->decimal('deposit_price', 10)->comment('押金')->default(0);
            $table->decimal('property_fee', 10)->comment('物业费')->default(0);
            $table->enum('payment_method', ['押一付一', '押一付三', '押一付六', '押二付一', '半年付', '年付'])->comment('付款方式')->default('押一付三');
            $table->string('contact_name')->comment('联系人');
            $table->string('contact_phone')->comment('联系电话');
            $table->json('images')->comment('商铺照片')->nullable();
            $table->string('business_district')->comment('商圈')->nullable();
            $table->string('address')->comment('详细地址')->nullable();
            $table->string('surroundings')->comment('周边环境')->nullable();
            $table->string('description')->comment('描述')->nullable();
            $table->json('facility_ids')->comment('配套设施ids')->nullable();
            $table->json('industry_ids')->comment('行业ids')->nullable();
            $table->timestamps();
            $table->comment('商铺表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
