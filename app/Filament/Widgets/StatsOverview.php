<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('房源数量', 0),
            Stat::make('今日新增房源', 0),
            Stat::make('商铺数量', 0),
            Stat::make('今日新增商铺', 0),
        ];
    }
}
