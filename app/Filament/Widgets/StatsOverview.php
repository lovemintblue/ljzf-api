<?php

namespace App\Filament\Widgets;

use App\Models\House;
use App\Models\Shop;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $houseCount = House::query()->count();
        $todayHouseCount = House::query()->whereDate('created_at', today())->count();
        $auditHouseCount = House::query()->where('audit_status', 0)->count();
        $shopCount = Shop::query()->count();
        $todayShopCount = Shop::query()->whereDate('created_at', today())->count();
        $auditShopCount = Shop::query()->where('audit_status', 0)->count();
        $userCount = User::query()->count();
        $todayUserCount = User::query()->whereDate('created_at', today())->count();
        $todayActiveUserCount = User::query()->whereDate('latest_visit_at', today())->count();
        return [
            Stat::make('房源数量', $houseCount),
            Stat::make('今日新增房源', $todayHouseCount),
            Stat::make('商铺数量', $shopCount),
            Stat::make('今日新增商铺', $todayShopCount),
            Stat::make('用户总数', $userCount),
            Stat::make('今日新增用户', $todayUserCount),
            Stat::make('今日活跃用户', $todayActiveUserCount),
            Stat::make('待审核房源', $auditHouseCount),
            Stat::make('待审核商铺', $auditShopCount),
        ];
    }
}
