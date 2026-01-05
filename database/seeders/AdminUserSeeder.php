<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AdminUser::query()->truncate();

        AdminUser::query()->create([
            'name' => '管理员',
            'username' => 'admin',
            'password' => Hash::make('admin'),
        ]);
    }
}
