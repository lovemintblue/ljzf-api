<?php

namespace App\Filament\Widgets;

use App\Models\House;
use App\Models\Shop;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $houseCount = House::query()->count();
        $todayHouseCount = House::query()->whereDate('created_at', today())->count();
        $shopCount = Shop::query()->count();
        $todayShopCount = Shop::query()->whereDate('created_at', today())->count();
        return [
            Stat::make('房源数量', $houseCount),
            Stat::make('今日新增房源', $todayHouseCount),
            Stat::make('商铺数量', $shopCount),
            Stat::make('今日新增商铺', $todayShopCount),
        ];
    }
}
