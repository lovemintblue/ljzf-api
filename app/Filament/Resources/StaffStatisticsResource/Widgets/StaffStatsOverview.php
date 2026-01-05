<?php

namespace App\Filament\Resources\StaffStatisticsResource\Widgets;

use App\Models\AdminUser;
use App\Models\House;
use App\Models\HouseOperationLog;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StaffStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $staffCount = User::where('is_staff', 1)->count();
        
        // 统计所有员工今日发布的房源（基于操作日志）
        $todayTotal = HouseOperationLog::where('operator_type', 'user')
            ->where('operation_type', 'publish')
            ->whereDate('created_at', today())
            ->count();
        
        // 统计所有员工近7天发布的房源
        $weekTotal = HouseOperationLog::where('operator_type', 'user')
            ->where('operation_type', 'publish')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();
        
        // 统计所有员工本月发布的房源
        $monthTotal = HouseOperationLog::where('operator_type', 'user')
            ->where('operation_type', 'publish')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        // 统计管理员的累计操作
        $adminUpdateTotal = HouseOperationLog::where('operator_type', 'admin')
            ->where('operation_type', 'update')
            ->count();
        
        $adminOnlineTotal = HouseOperationLog::where('operator_type', 'admin')
            ->where('operation_type', 'online')
            ->count();
        
        $adminOfflineTotal = HouseOperationLog::where('operator_type', 'admin')
            ->where('operation_type', 'offline')
            ->count();

        return [
            Stat::make('员工总数', $staffCount)
                ->description('当前员工人数')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
                
            Stat::make('今日总上传', $todayTotal)
                ->description('所有员工今日上传')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
                
            Stat::make('近7天总上传', $weekTotal)
                ->description('所有员工近7天上传')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning'),
                
            Stat::make('本月总上传', $monthTotal)
                ->description('所有员工本月上传')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),

            Stat::make('累计更新排序', $adminUpdateTotal)
                ->description('管理员累计更新次数')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('info'),
                
            Stat::make('累计上架操作', $adminOnlineTotal)
                ->description('管理员累计上架次数')
                ->descriptionIcon('heroicon-m-arrow-up')
                ->color('success'),
                
            Stat::make('累计下架操作', $adminOfflineTotal)
                ->description('管理员累计下架次数')
                ->descriptionIcon('heroicon-m-arrow-down')
                ->color('danger'),
        ];
    }
}

