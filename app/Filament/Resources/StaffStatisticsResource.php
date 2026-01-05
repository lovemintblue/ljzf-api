<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StaffStatisticsResource\Pages;
use App\Models\AdminUser;
use App\Models\House;
use App\Models\HouseOperationLog;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StaffStatisticsResource extends Resource
{
    protected static ?string $model = User::class;
    
    protected static ?string $policy = \App\Policies\StaffStatisticsPolicy::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = '用户';

    protected static ?string $navigationLabel = '员工统计';

    protected static ?string $label = '员工统计';
    
    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return auth()->check() && auth()->user()->can('view_any_staff::statistics');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->where('is_staff', 1)
                    ->with(['houses'])
                    ->orderBy('created_at', 'desc')
            )
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->label('头像')
                    ->circular()
                    ->defaultImageUrl(function ($record) {
                        if ($record && ($record->operator_type ?? null) === 'admin') {
                            $name = $record->name ?? $record->username ?? '管理员';
                            $firstChar = mb_substr($name, 0, 1);
                            $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40">
                                <circle cx="20" cy="20" r="20" fill="#3b82f6"/>
                                <text x="20" y="25" text-anchor="middle" fill="white" font-size="16" font-weight="bold" font-family="Arial">' . htmlspecialchars($firstChar) . '</text>
                            </svg>';
                            return 'data:image/svg+xml;base64,' . base64_encode($svg);
                        }
                        return null;
                    }),
                Tables\Columns\TextColumn::make('display_name')
                    ->label('姓名/昵称')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('手机号/用户名')
                    ->searchable(),
                Tables\Columns\TextColumn::make('today_count')
                    ->label('今日上传')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        return HouseOperationLog::where('operator_id', $record->id)
                            ->where('operator_type', $record->operator_type ?? 'user')
                            ->where('operation_type', 'publish')
                            ->whereDate('created_at', today())
                            ->count();
                    })
                    ->color('success'),
                Tables\Columns\TextColumn::make('yesterday_count')
                    ->label('昨天上传')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        return HouseOperationLog::where('operator_id', $record->id)
                            ->where('operator_type', $record->operator_type ?? 'user')
                            ->where('operation_type', 'publish')
                            ->whereDate('created_at', today()->subDay())
                            ->count();
                    })
                    ->color('info'),
                Tables\Columns\TextColumn::make('week_count')
                    ->label('近7天上传')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        return HouseOperationLog::where('operator_id', $record->id)
                            ->where('operator_type', $record->operator_type ?? 'user')
                            ->where('operation_type', 'publish')
                            ->where('created_at', '>=', now()->subDays(7))
                            ->count();
                    })
                    ->color('warning'),
                Tables\Columns\TextColumn::make('month_count')
                    ->label('本月上传')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        return HouseOperationLog::where('operator_id', $record->id)
                            ->where('operator_type', $record->operator_type ?? 'user')
                            ->where('operation_type', 'publish')
                            ->whereYear('created_at', now()->year)
                            ->whereMonth('created_at', now()->month)
                            ->count();
                    })
                    ->color('primary'),
                Tables\Columns\TextColumn::make('total_count')
                    ->label('累计发布')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        return HouseOperationLog::where('operator_id', $record->id)
                            ->where('operator_type', $record->operator_type ?? 'user')
                            ->where('operation_type', 'publish')
                            ->count();
                    })
                    ->color('gray'),
            ])
            ->paginated([10, 25, 50, 100]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageStaffStatistics::route('/'),
        ];
    }
}

