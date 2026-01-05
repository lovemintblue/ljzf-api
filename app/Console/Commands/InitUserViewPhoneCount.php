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
        // 清除所有用户的临时额度
        User::query()
            ->where('temp_quota_date', '<', date('Y-m-d'))
            ->update([
                'temp_quota' => 0,
                'temp_quota_date' => null,
                'view_phone_count' => 0,
            ]);
        Log::info('每日访问次数重置完成（临时额度已清除）');
        $this->info('同步完成');
    }
}
