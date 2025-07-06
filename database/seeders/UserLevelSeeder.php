<?php

namespace Database\Seeders;

use App\Models\UserLevel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserLevel::query()->truncate();

        $list = [
            [
                'name' => '月度会员',
                'level' => 1,
                'price' => '19.9',
                'original_price' => '29.9',
                'is_recommend' => 0,
                'is_good_value' => 0,
                'cycle' => 0,
                'privilege' => [
                    0, 1, 2
                ]
            ],
            [
                'name' => '季度会员',
                'level' => 1,
                'price' => '49.9',
                'original_price' => '89.7',
                'is_recommend' => 1,
                'is_good_value' => 0,
                'cycle' => 1,
                'privilege' => [
                    0, 1, 2, 3
                ]
            ],
            [
                'name' => '年度会员',
                'level' => 1,
                'price' => '169.9',
                'original_price' => '356.8',
                'is_recommend' => 0,
                'is_good_value' => 1,
                'cycle' => 2,
                'privilege' => [
                    0, 1, 2, 3, 4
                ]
            ]
        ];

        foreach ($list as $item) {
            $userLevel = new UserLevel();
            $userLevel->name = $item['name'];
            $userLevel->level = $item['level'];
            $userLevel->price = $item['price'];
            $userLevel->original_price = $item['original_price'];
            $userLevel->is_recommend = $item['is_recommend'];
            $userLevel->is_good_value = $item['is_good_value'];
            $userLevel->cycle = $item['cycle'];
            $userLevel->privilege = $item['privilege'];
            $userLevel->save();
        }
    }
}
