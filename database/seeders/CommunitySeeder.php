<?php

namespace Database\Seeders;

use App\Models\Community;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CommunitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Community::query()->truncate();

        Community::query()->create([
            'name' => '小区1',
            'address' => '小区1详细地址'
        ]);

        Community::query()->create([
            'name' => '小区2',
            'address' => '小区2详细地址'
        ]);
    }
}
