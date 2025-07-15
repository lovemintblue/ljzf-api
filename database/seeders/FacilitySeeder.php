<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Facility::query()->truncate();

        $list = [
            '空调',
            '热水器',
            '洗衣机',
            '冰箱',
            '电视',
            '宽带',
            '衣柜',
            '沙发',
            '电梯',
            '阳台',
            '智能门锁',
            '独立卫浴'
        ];

        foreach ($list as $item) {
            $facility = new Facility();
            $facility->name = $item;
            $facility->type = [0, 1];
            $facility->save();
        }
    }
}
