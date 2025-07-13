<?php

namespace Database\Seeders;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SqlDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @return void
     * @throws FileNotFoundException
     */
    public function run(): void
    {
        // 1. 获取 SQL 文件路径
        $path = database_path('sql/districts.sql');

        // 2. 检查文件是否存在
        if (!File::exists($path)) {
            $this->command->error("SQL 文件未找到: {$path}");
            return;
        }

        // 3. 读取 SQL 内容
        $sql = File::get($path);

        // 4. 执行 SQL（关键步骤）
        DB::unprepared($sql);

        $this->command->info('SQL 文件数据导入成功！');
    }
}
