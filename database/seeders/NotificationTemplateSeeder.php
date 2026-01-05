<?php

namespace Database\Seeders;

use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class NotificationTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'code' => 'user_register_success',
                'name' => '注册成功通知',
                'title' => '欢迎注册',
                'content' => '尊敬的用户{nickname}，欢迎注册成为平台会员！注册时间：{register_time}',
                'is_enabled' => true,
                'variables' => 'nickname:用户昵称, register_time:注册时间',
            ],
            [
                'code' => 'user_login_success',
                'name' => '登录成功通知',
                'title' => '登录成功',
                'content' => '尊敬的用户{nickname}，您已成功登录。登录时间：{login_time}，登录IP：{ip}',
                'is_enabled' => false,
                'variables' => 'nickname:用户昵称, login_time:登录时间, ip:登录IP',
            ],
            [
                'code' => 'house_pending_audit',
                'name' => '房源等待人工审核',
                'title' => '房源待审核',
                'content' => '您发布的房源【{house_title}】已提交，正在等待人工审核，请耐心等待。提交时间：{submit_time}',
                'is_enabled' => true,
                'variables' => 'house_title:房源标题, submit_time:提交时间',
            ],
            [
                'code' => 'house_audit_passed',
                'name' => '房源审核通过已成功上架',
                'title' => '房源审核通过',
                'content' => '恭喜您！房源【{house_title}】已审核通过并成功上架，现在可以被其他用户浏览。上架时间：{online_time}',
                'is_enabled' => true,
                'variables' => 'house_title:房源标题, online_time:上架时间',
            ],
            [
                'code' => 'house_offline',
                'name' => '房源已下架',
                'title' => '房源已下架',
                'content' => '您的房源【{house_title}】已下架。下架原因：{reason}。下架时间：{offline_time}',
                'is_enabled' => true,
                'variables' => 'house_title:房源标题, reason:下架原因, offline_time:下架时间',
            ],
            [
                'code' => 'shop_pending_audit',
                'name' => '商铺等待人工审核',
                'title' => '商铺待审核',
                'content' => '您发布的商铺【{shop_title}】已提交，正在等待人工审核，请耐心等待。提交时间：{submit_time}',
                'is_enabled' => true,
                'variables' => 'shop_title:商铺标题, submit_time:提交时间',
            ],
            [
                'code' => 'shop_audit_passed',
                'name' => '商铺审核通过已成功上架',
                'title' => '商铺审核通过',
                'content' => '恭喜您！商铺【{shop_title}】已审核通过并成功上架，现在可以被其他用户浏览。上架时间：{online_time}',
                'is_enabled' => true,
                'variables' => 'shop_title:商铺标题, online_time:上架时间',
            ],
            [
                'code' => 'shop_offline',
                'name' => '商铺已下架',
                'title' => '商铺已下架',
                'content' => '您的商铺【{shop_title}】已下架。下架原因：{reason}。下架时间：{offline_time}',
                'is_enabled' => true,
                'variables' => 'shop_title:商铺标题, reason:下架原因, offline_time:下架时间',
            ],
            [
                'code' => 'offline_follow_up',
                'name' => '下架跟进处理通知',
                'title' => '下架处理通知',
                'content' => '您的{type}【{title}】已下架并进入跟进处理流程。处理意见：{comment}。请及时处理，如有疑问请联系客服。',
                'is_enabled' => true,
                'variables' => 'type:类型(房源/商铺), title:标题, comment:处理意见',
            ],
            [
                'code' => 'violation_warning',
                'name' => '跟进处理违规通知',
                'title' => '违规警告',
                'content' => '您的{type}【{title}】存在违规行为，已被标记为违规。违规原因：{reason}。请立即整改，否则可能影响您的账号信誉。',
                'is_enabled' => true,
                'variables' => 'type:类型(房源/商铺), title:标题, reason:违规原因',
            ],
            [
                'code' => 'vip_activated',
                'name' => '会员开通成功',
                'title' => '会员开通成功',
                'content' => '恭喜您成为{level_name}会员！开通时间：{start_time}，到期时间：{expire_time}。即日起您可享平台所属会员权益，可在小程序【我的】页面点击"我的会员"查看详情与权益使用说明。',
                'is_enabled' => true,
                'variables' => 'level_name:会员等级, start_time:开通时间, expire_time:到期时间',
            ],
            [
                'code' => 'vip_expire_warning',
                'name' => '会员到期3天提醒',
                'title' => '会员即将到期',
                'content' => '尊敬的{level_name}会员，您的会员将于{expire_time}到期（剩余{days}天）。为避免影响使用，请及时续费。',
                'is_enabled' => true,
                'variables' => 'level_name:会员等级, expire_time:到期时间, days:剩余天数',
            ],
            [
                'code' => 'vip_expired',
                'name' => '会员到期',
                'title' => '会员已到期',
                'content' => '尊敬的用户，您的{level_name}会员已于{expire_time}到期。部分会员权益已停止使用，如需继续享受会员服务，请及时续费。',
                'is_enabled' => true,
                'variables' => 'level_name:会员等级, expire_time:到期时间',
            ],
        ];

        foreach ($templates as $template) {
            NotificationTemplate::updateOrCreate(
                ['code' => $template['code']],
                $template
            );
        }
    }
}
