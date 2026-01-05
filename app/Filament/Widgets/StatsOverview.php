<?php

namespace App\Filament\Widgets;

use App\Models\House;
use App\Models\HouseFollowUp;
use App\Models\Shop;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    
    protected function getColumns(): int
    {
        return 3; // 每行最多3个（第1行），其他行会自动换行
    }

    protected function getStats(): array
    {
        // ========== 第1行：待处理数据 ==========
        // 对齐 HouseFollowUpResource 的查询条件
        $rentedHouseFollowUpCount = HouseFollowUp::query()
            ->where('result', '已出租')
            ->where('is_punished', 0)
            ->whereHas('house', function ($query) {
                $query->where('is_show', 1);
            })
            ->count();
        // 对齐 AuditHouseResource 的查询条件：排除草稿和委托房源
        $auditHouseCount = House::query()
            ->where('audit_status', 0)
            ->where('is_draft', 0)
            ->where('is_delegated', 0)
            ->count();
        // 对齐 AuditShopResource 的查询条件
        $auditShopCount = Shop::query()->where('audit_status', 0)->count();

        // ========== 第2排：用户数据 ==========
        $userCount = User::query()->count();
        $vipUserCount = User::query()->where('user_level_id', '>', 0)->count();
        $todayUserCount = User::query()->whereDate('created_at', today())->count();
        $todayVipUserCount = User::query()
            ->where('user_level_id', '>', 0)
            ->whereDate('created_at', today())
            ->count();
        $todayActiveUserCount = User::query()->whereDate('latest_visit_at', today())->count();
        $todayActiveVipUserCount = User::query()
            ->where('user_level_id', '>', 0)
            ->whereDate('latest_visit_at', today())
            ->count();

        // ========== 第3排：房源数据 ==========
        $houseCount = House::query()->count();
        $onlineHouseCount = House::query()->where('is_show', 1)->where('audit_status', 1)->count();
        $offlineHouseCount = House::query()->where('is_show', 0)->orWhere('audit_status', '!=', 1)->count();
        $todayOnlineHouseCount = House::query()
            ->where('is_show', 1)
            ->where('audit_status', 1)
            ->whereDate('created_at', today())
            ->count();
        $todayOfflineHouseCount = House::query()
            ->where('is_show', 0)
            ->whereDate('updated_at', today())
            ->count();

        // ========== 第4排：商铺数据 ==========
        $shopCount = Shop::query()->count();
        $onlineShopCount = Shop::query()->where('is_show', 1)->where('audit_status', 1)->count();
        // 下架商铺：只统计已审核通过但下架的，不包括待审核的（已在待审核商铺统计）
        $offlineShopCount = Shop::query()->where('audit_status', 1)->where('is_show', 0)->count();
        $todayOnlineShopCount = Shop::query()
            ->where('is_show', 1)
            ->where('audit_status', 1)
            ->whereDate('created_at', today())
            ->count();
        $todayOfflineShopCount = Shop::query()
            ->where('is_show', 0)
            ->whereDate('updated_at', today())
            ->count();

        return [
            // ========== 第1-2行：房源数据（6个卡片）==========
            Stat::make('房源总数', $houseCount)
                ->description('全部房源')
                ->color('primary')
                ->icon('heroicon-o-home'),
            Stat::make('待审核房源', $auditHouseCount)
                ->description('等待审核')
                ->color('danger')
                ->icon('heroicon-o-home'),
            Stat::make('上架房源', $onlineHouseCount)
                ->description('在线展示中')
                ->color('success')
                ->icon('heroicon-o-arrow-up-circle'),
            
            Stat::make('下架房源', $offlineHouseCount)
                ->description('已下架/待审核')
                ->color('gray')
                ->icon('heroicon-o-arrow-down-circle'),
            Stat::make('今日上新房源', $todayOnlineHouseCount)
                ->description('今天新上架')
                ->icon('heroicon-o-sparkles'),
            Stat::make('今日下架房源', $todayOfflineHouseCount)
                ->description('今天下架')
                ->color('warning')
                ->icon('heroicon-o-minus-circle'),

            // ========== 第3-4行：商铺数据（5个卡片）==========
            Stat::make('商铺总数', $shopCount)
                ->description('全部商铺')
                ->color('primary')
                ->icon('heroicon-o-building-storefront'),
            Stat::make('待审核商铺', $auditShopCount)
                ->description('等待审核')
                ->color('danger')
                ->icon('heroicon-o-building-storefront'),
            Stat::make('上架商铺', $onlineShopCount)
                ->description('在线展示中')
                ->color('success')
                ->icon('heroicon-o-arrow-up-circle'),
            
            Stat::make('下架商铺', $offlineShopCount)
                ->description('已下架')
                ->color('gray')
                ->icon('heroicon-o-arrow-down-circle'),
            Stat::make('今日上架商铺', $todayOnlineShopCount)
                ->description('今天新上架')
                ->icon('heroicon-o-sparkles'),
            Stat::make('今日下架商铺', $todayOfflineShopCount)
                ->description('今天下架')
                ->color('warning')
                ->icon('heroicon-o-minus-circle'),

            // ========== 第5-6行：用户数据（6个卡片）==========
            Stat::make('用户总数', $userCount)
                ->description('全部注册用户')
                ->color('success')
                ->icon('heroicon-o-users'),
            Stat::make('今日新增用户', $todayUserCount)
                ->description('今天注册')
                ->icon('heroicon-o-user-plus'),
            Stat::make('今日活跃用户', $todayActiveUserCount)
                ->description('今天访问')
                ->icon('heroicon-o-bolt'),
            
            Stat::make('会员总数', $vipUserCount)
                ->description('当前有效会员')
                ->color('info')
                ->icon('heroicon-o-star'),
            Stat::make('今日新增会员', $todayVipUserCount)
                ->description('今天开通会员')
                ->color('info')
                ->icon('heroicon-o-sparkles'),
            Stat::make('今日活跃会员', $todayActiveVipUserCount)
                ->description('会员今日访问')
                ->color('info')
                ->icon('heroicon-o-bolt'),

            // ========== 第7行：待处理数据（3个卡片）==========
            Stat::make('已出租房源跟进处理', $rentedHouseFollowUpCount)
                ->description('待跟进处理')
                ->color('warning')
                ->icon('heroicon-o-clipboard-document-list'),
        ];
    }
}
