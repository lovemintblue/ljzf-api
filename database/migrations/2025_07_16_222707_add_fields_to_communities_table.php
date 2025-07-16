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
        Schema::table('communities', static function (Blueprint $table) {
            $table->string('built_year')->comment('建成年代')->nullable();
            $table->string('property_fee')->comment('物业费(元/月/㎡)')->nullable();
            $table->string('property_company')->comment('物业公司')->nullable();
            $table->string('developer')->comment('开发商')->nullable();
            $table->string('building_count')->comment('楼栋总数')->nullable();
            $table->string('house_count')->comment('房屋总数')->nullable();
            $table->decimal('average_rent_price', 10)->comment('租金均价(元/月)')->default(0);
            $table->decimal('average_sale_price', 10)->comment('售价均价(元/㎡)')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('communities', static function (Blueprint $table) {
            $table->dropColumn('built_year', 'property_fee', 'property_company', 'developer', 'building_count', 'house_count', 'average_rent_price', 'average_sale_price');
        });
    }
};
