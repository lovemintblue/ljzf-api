<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckVipExpiration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vip:check-expiration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '检查会员到期状态并发送通知';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $notificationService = new NotificationService();
        $now = Carbon::now();

        // 1. 检查3天后到期的会员（提醒续费）
        $expiringSoon = User::query()
            ->whereNotNull('user_level_id')
            ->whereNotNull('expired_at')
            ->whereBetween('expired_at', [
                $now->copy()->addDays(3)->startOfDay(),
                $now->copy()->addDays(3)->endOfDay(),
            ])
            ->with('userLevel')
            ->get();

        foreach ($expiringSoon as $user) {
            if ($user->userLevel) {
                $days = Carbon::parse($user->expired_at)->diffInDays($now);
                $notificationService->notifyVipExpireWarning(
                    $user,
                    $user->userLevel->name,
                    Carbon::parse($user->expired_at)->format('Y-m-d H:i:s'),
                    $days
                );
                $this->info("已提醒用户 {$user->id} 会员即将到期");
            }
        }

        // 2. 检查今天到期的会员
        $expiredToday = User::query()
            ->whereNotNull('user_level_id')
            ->whereNotNull('expired_at')
            ->whereBetween('expired_at', [
                $now->copy()->startOfDay(),
                $now->copy()->endOfDay(),
            ])
            ->with('userLevel')
            ->get();

        foreach ($expiredToday as $user) {
            if ($user->userLevel) {
                $levelName = $user->userLevel->name;
                $expireTime = Carbon::parse($user->expired_at)->format('Y-m-d H:i:s');
                
                // 发送到期通知
                $notificationService->notifyVipExpired($user, $levelName, $expireTime);
                
                // 清除会员等级
                $user->user_level_id = null;
                $user->expired_at = null;
                $user->save();
                
                $this->info("用户 {$user->id} 会员已到期并清除");
            }
        }

        $this->info('会员到期检查完成！');
        return Command::SUCCESS;
    }
}
