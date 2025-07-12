<?php

namespace Database\Seeders;

use App\Models\BusinessDistrict;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BusinessDistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        BusinessDistrict::query()->truncate();

        BusinessDistrict::query()->create([
            'name' => '商圈1'
        ]);
        BusinessDistrict::query()->create([
            'name' => '商圈2'
        ]);
    }
}
