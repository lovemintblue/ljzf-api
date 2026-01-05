<?php

namespace App\Filament\Resources\UserLevelOrderResource\Widgets;

use App\Models\UserLevelOrder;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrderStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // ========== 财务数据统计 ==========
        $todayIncome = UserLevelOrder::query()
            ->where('status', 1) // 已支付
            ->whereDate('created_at', today())
            ->sum('total_amount');
        
        $monthIncome = UserLevelOrder::query()
            ->where('status', 1)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('total_amount');
        
        $yearIncome = UserLevelOrder::query()
            ->where('status', 1)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');
        
        $todayOrderCount = UserLevelOrder::query()
            ->where('status', 1)
            ->whereDate('created_at', today())
            ->count();
        
        $monthOrderCount = UserLevelOrder::query()
            ->where('status', 1)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();
        
        $yearOrderCount = UserLevelOrder::query()
            ->where('status', 1)
            ->whereYear('created_at', now()->year)
            ->count();

        return [
            Stat::make('日收入', '¥' . number_format($todayIncome, 2))
                ->description('今日订单收入')
                ->color('success')
                ->icon('heroicon-o-banknotes'),
            
            Stat::make('月收入', '¥' . number_format($monthIncome, 2))
                ->description('本月订单收入')
                ->color('success')
                ->icon('heroicon-o-currency-dollar'),
            
            Stat::make('年收入', '¥' . number_format($yearIncome, 2))
                ->description('本年订单收入')
                ->color('success')
                ->icon('heroicon-o-trophy'),
            
            Stat::make('日订单数量', $todayOrderCount)
                ->description('今日成交订单')
                ->icon('heroicon-o-shopping-cart'),
            
            Stat::make('月订单数量', $monthOrderCount)
                ->description('本月成交订单')
                ->icon('heroicon-o-document-text'),
            
            Stat::make('年订单数量', $yearOrderCount)
                ->description('本年成交订单')
                ->icon('heroicon-o-clipboard-document-check'),
        ];
    }
}

