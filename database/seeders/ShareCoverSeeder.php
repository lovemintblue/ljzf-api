<?php

namespace Database\Seeders;

use App\Models\ShareCover;
use Illuminate\Database\Seeder;

class ShareCoverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ShareCover::create([
            'image' => 'https://qiniuoss.lejia1.cn/1759918659_1l0VUNbXoT.png',
            'sort' => 1,
            'is_active' => true,
        ]);
    }
}

