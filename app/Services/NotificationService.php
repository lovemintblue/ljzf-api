<?php
/**
 * 系统通知服务层
 */

namespace App\Services;

use App\Models\NotificationTemplate;
use App\Models\User;
use App\Notifications\UserNotification;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * 发送系统通知
     * @param User $user
     * @param string $templateCode
     * @param array $variables
     * @return bool
     */
    public function send(User $user, string $templateCode, array $variables = []): bool
    {
        try {
            $template = NotificationTemplate::where('code', $templateCode)
                ->where('is_enabled', true)
                ->first();

            if (!$template) {
                Log::warning("通知模板不存在或未启用: {$templateCode}");
                return false;
            }

            $notification = $template->renderNotification($variables);

            $user->notify(new UserNotification($notification));

            return true;
        } catch (\Exception $e) {
            Log::error("发送通知失败: {$templateCode}", [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 批量发送通知
     * @param array $users
     * @param string $templateCode
     * @param array $variables
     * @return int 成功数量
     */
    public function sendBatch(array $users, string $templateCode, array $variables = []): int
    {
        $count = 0;
        foreach ($users as $user) {
            if ($this->send($user, $templateCode, $variables)) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * 注册成功通知
     */
    public function notifyRegisterSuccess(User $user): bool
    {
        return $this->send($user, 'user_register_success', [
            'nickname' => $user->nickname,
            'register_time' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 登录成功通知
     */
    public function notifyLoginSuccess(User $user, string $ip = ''): bool
    {
        return $this->send($user, 'user_login_success', [
            'nickname' => $user->nickname,
            'login_time' => now()->format('Y-m-d H:i:s'),
            'ip' => $ip ?: request()->ip(),
        ]);
    }

    /**
     * 房源等待审核通知
     */
    public function notifyHousePendingAudit(User $user, $house): bool
    {
        $communityName = $house->community->name ?? '未知';
        $houseTitle = "{$communityName} {$house->room_count}室{$house->living_room_count}厅{$house->bathroom_count}卫";
        
        return $this->send($user, 'house_pending_audit', [
            'house_title' => $houseTitle,
            'submit_time' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 房源审核通过通知
     */
    public function notifyHouseAuditPassed(User $user, $house): bool
    {
        $communityName = $house->community->name ?? '未知';
        $houseTitle = "{$communityName} {$house->room_count}室{$house->living_room_count}厅{$house->bathroom_count}卫";
        
        return $this->send($user, 'house_audit_passed', [
            'house_title' => $houseTitle,
            'online_time' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 房源下架通知
     */
    public function notifyHouseOffline(User $user, $house, string $reason = '管理员下架'): bool
    {
        $communityName = $house->community->name ?? '未知';
        $houseTitle = "{$communityName} {$house->room_count}室{$house->living_room_count}厅{$house->bathroom_count}卫";
        
        return $this->send($user, 'house_offline', [
            'house_title' => $houseTitle,
            'reason' => $reason,
            'offline_time' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 商铺等待审核通知
     */
    public function notifyShopPendingAudit(User $user, $shop): bool
    {
        $shopTitle = $shop->title ?: ($shop->no ?? '待生成');
        
        return $this->send($user, 'shop_pending_audit', [
            'shop_title' => $shopTitle,
            'submit_time' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 商铺审核通过通知
     */
    public function notifyShopAuditPassed(User $user, $shop): bool
    {
        $shopTitle = $shop->title ?: ($shop->no ?? '待生成');
        
        return $this->send($user, 'shop_audit_passed', [
            'shop_title' => $shopTitle,
            'online_time' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 商铺下架通知
     */
    public function notifyShopOffline(User $user, $shop, string $reason = '管理员下架'): bool
    {
        $shopTitle = $shop->title ?: ($shop->no ?? '待生成');
        
        return $this->send($user, 'shop_offline', [
            'shop_title' => $shopTitle,
            'reason' => $reason,
            'offline_time' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 下架跟进处理通知
     */
    public function notifyOfflineFollowUp(User $user, string $type, string $title, string $comment): bool
    {
        return $this->send($user, 'offline_follow_up', [
            'type' => $type,
            'title' => $title,
            'comment' => $comment,
        ]);
    }

    /**
     * 违规警告通知
     */
    public function notifyViolationWarning(User $user, string $type, string $title, string $reason): bool
    {
        return $this->send($user, 'violation_warning', [
            'type' => $type,
            'title' => $title,
            'reason' => $reason,
        ]);
    }

    /**
     * 会员开通成功通知
     */
    public function notifyVipActivated(User $user, string $levelName, string $startTime, string $expireTime): bool
    {
        return $this->send($user, 'vip_activated', [
            'level_name' => $levelName,
            'start_time' => $startTime,
            'expire_time' => $expireTime,
        ]);
    }

    /**
     * 会员即将到期提醒
     */
    public function notifyVipExpireWarning(User $user, string $levelName, string $expireTime, int $days): bool
    {
        return $this->send($user, 'vip_expire_warning', [
            'level_name' => $levelName,
            'expire_time' => $expireTime,
            'days' => $days,
        ]);
    }

    /**
     * 会员已到期通知
     */
    public function notifyVipExpired(User $user, string $levelName, string $expireTime): bool
    {
        return $this->send($user, 'vip_expired', [
            'level_name' => $levelName,
            'expire_time' => $expireTime,
        ]);
    }
}
