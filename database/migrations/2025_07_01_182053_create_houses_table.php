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
        Schema::create('houses', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('用户ID');
            $table->unsignedBigInteger('community_id')->comment('小区ID')->default(0);
            $table->string('title')->comment('标题');
            $table->string('contact_name')->comment('联系人');
            $table->string('contact_phone')->comment('联系电话');
            $table->tinyInteger('type')->comment('类型 0整租 1合租')->default(0);
            $table->tinyInteger('status')->comment('状态 0空置 1在租 2转租')->default(0);
            $table->tinyInteger('room_count')->comment('室')->default(1);
            $table->tinyInteger('living_room_count')->comment('厅')->default(0);
            $table->tinyInteger('bathroom_count')->comment('卫')->default(1);
            $table->decimal('area', 10)->comment('面积')->default(0);
            $table->integer('floor')->comment('楼层')->default(0);
            $table->integer('total_floors')->comment('总楼层')->default(0);
            $table->enum('orientation', ['东', '南', '西', '北', '东南', '东北', '西南', '西北'])->comment('朝向')->default('东');
            $table->enum('renovation', ['毛坯房', '简装修', '精装修'])->comment('装修')->default('毛坯房');
            $table->decimal('rent_price', 10)->comment('租金')->default(0);
            $table->enum('payment_method', ['押一付一', '押一付三', '押二付一', '半年付', '年付'])->comment('付款方式')->default('押一付三');
            $table->string('min_rental_period')->comment('起租时长 0月 1季 2年')->default(0);
            $table->json('images')->comment('房源照片')->nullable();
            $table->json('facility_ids')->comment('配套设施ids')->nullable();
            $table->string('building_number')->comment('栋数')->nullable();
            $table->string('room_number')->comment('房间号')->nullable();
            $table->string('province')->comment('省份')->nullable();
            $table->string('city')->comment('城市')->nullable();
            $table->string('district')->comment('区县')->nullable();
            $table->string('address')->comment('详细地址')->nullable();
            $table->timestamps();
            $table->comment('房源表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('houses');
    }
};
