<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class InitUserViewPhoneCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:init-user-view-phone-count';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '初始化用户查看手机号次数';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::query()
            ->with('useLevel')
            ->whereNot('user_level_id', 0)
            ->get();

        foreach ($users as $user) {
            $user->view_phone_count = $user->userLevel->view_phone_count;
        }
        $this->info('同步完成');
    }
}
