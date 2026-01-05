<?php

namespace Database\Seeders;

use App\Models\Industry;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IndustrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Industry::query()->truncate();

        $list = [
            '零售',
            '餐饮',
            '服装',
            '美容美发',
            '教育培训',
            '便利店',
            '健身房',
            '咖啡厅',
            '酒吧',
            '药店',
            '其他'
        ];

        foreach ($list as $item) {
            $industry = new Industry();
            $industry->name = $item;
            $industry->save();
        }
    }
}
