<?php

namespace App\Filament\Resources\UserLevelOrderResource\Pages;

use App\Filament\Resources\UserLevelOrderResource;
use App\Filament\Resources\UserLevelOrderResource\Widgets\OrderStatsOverview;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageUserLevelOrders extends ManageRecords
{
    protected static string $resource = UserLevelOrderResource::class;

    /**
     * @return array|Actions\Action[]|Actions\ActionGroup[]
     */
    protected function getHeaderActions(): array
    {
        return [

        ];
    }

    /**
     * 页面顶部显示的统计小部件
     */
    protected function getHeaderWidgets(): array
    {
        return [
            OrderStatsOverview::class,
        ];
    }
}
