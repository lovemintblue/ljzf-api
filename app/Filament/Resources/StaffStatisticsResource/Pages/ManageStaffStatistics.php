<?php

namespace App\Filament\Resources\StaffStatisticsResource\Pages;

use App\Filament\Resources\StaffStatisticsResource;
use App\Models\AdminUser;
use App\Models\House;
use App\Models\HouseOperationLog;
use App\Models\User;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ManageRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class ManageStaffStatistics extends ManageRecords
{
    protected static string $resource = StaffStatisticsResource::class;

    protected static ?string $title = '员工统计';

    public ?string $activeTab = 'admin';

    // 监听 activeTab 变化，重新渲染表格
    public function updatedActiveTab(): void
    {
        // 清除表格缓存，强制重新渲染
        $this->resetTable();
    }

    protected function getHeaderWidgets(): array
    {
        return [
            StaffStatisticsResource\Widgets\StaffStatsOverview::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'staff' => Tab::make('员工')
                ->badge(User::where('is_staff', 1)->count()),
            'admin' => Tab::make('管理员')
                ->badge(AdminUser::count()),
        ];
    }

    public function isAdminTab(): bool
    {
        return $this->activeTab === 'admin';
    }

    public function table(Table $table): Table
    {
        // 检查当前是否在管理员标签页
        $isAdmin = ($this->activeTab ?? 'staff') === 'admin';
        
        $columns = [
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
                ->label($isAdmin ? '今日更新' : '今日上传')
                ->badge()
                ->getStateUsing(function ($record) use ($isAdmin) {
                    $operationType = $isAdmin ? 'update' : 'publish';
                    return HouseOperationLog::where('operator_id', $record->id)
                        ->where('operator_type', $record->operator_type ?? 'user')
                        ->where('operation_type', $operationType)
                        ->whereDate('created_at', today())
                        ->count();
                })
                ->color($isAdmin ? 'info' : 'success'),
            Tables\Columns\TextColumn::make('yesterday_count')
                ->label($isAdmin ? '今日上架' : '昨天上传')
                ->badge()
                ->getStateUsing(function ($record) use ($isAdmin) {
                    if ($isAdmin) {
                        return HouseOperationLog::where('operator_id', $record->id)
                            ->where('operator_type', $record->operator_type ?? 'user')
                            ->where('operation_type', 'online')
                            ->whereDate('created_at', today())
                            ->count();
                    }
                    return HouseOperationLog::where('operator_id', $record->id)
                        ->where('operator_type', $record->operator_type ?? 'user')
                        ->where('operation_type', 'publish')
                        ->whereDate('created_at', today()->subDay())
                        ->count();
                })
                ->color($isAdmin ? 'success' : 'info'),
            Tables\Columns\TextColumn::make('week_count')
                ->label($isAdmin ? '今日下架' : '近7天上传')
                ->badge()
                ->getStateUsing(function ($record) use ($isAdmin) {
                    if ($isAdmin) {
                        return HouseOperationLog::where('operator_id', $record->id)
                            ->where('operator_type', $record->operator_type ?? 'user')
                            ->where('operation_type', 'offline')
                            ->whereDate('created_at', today())
                            ->count();
                    }
                    return HouseOperationLog::where('operator_id', $record->id)
                        ->where('operator_type', $record->operator_type ?? 'user')
                        ->where('operation_type', 'publish')
                        ->where('created_at', '>=', now()->subDays(7))
                        ->count();
                })
                ->color($isAdmin ? 'danger' : 'warning'),
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
                ->color('primary')
                ->hidden(fn () => $isAdmin),
            Tables\Columns\TextColumn::make('total_count')
                ->label('累计发布')
                ->badge()
                ->getStateUsing(function ($record) {
                    return HouseOperationLog::where('operator_id', $record->id)
                        ->where('operator_type', $record->operator_type ?? 'user')
                        ->where('operation_type', 'publish')
                        ->count();
                })
                ->color('gray')
                ->hidden(fn () => $isAdmin),
        ];

        // 始终添加这三列，但根据标签页动态隐藏
        $columns[] = Tables\Columns\TextColumn::make('update_count')
            ->label('累计更新')
            ->badge()
            ->getStateUsing(function ($record) {
                return HouseOperationLog::where('operator_id', $record->id)
                    ->where('operator_type', $record->operator_type ?? 'user')
                    ->where('operation_type', 'update')
                    ->count();
            })
            ->color('info')
            ->hidden(fn () => ($this->activeTab ?? 'admin') !== 'admin');
        
        $columns[] = Tables\Columns\TextColumn::make('online_count')
            ->label('累计上架')
            ->badge()
            ->getStateUsing(function ($record) {
                return HouseOperationLog::where('operator_id', $record->id)
                    ->where('operator_type', $record->operator_type ?? 'user')
                    ->where('operation_type', 'online')
                    ->count();
            })
            ->color('success')
            ->hidden(fn () => ($this->activeTab ?? 'admin') !== 'admin');
        
        $columns[] = Tables\Columns\TextColumn::make('offline_count')
            ->label('累计下架')
            ->badge()
            ->getStateUsing(function ($record) {
                return HouseOperationLog::where('operator_id', $record->id)
                    ->where('operator_type', $record->operator_type ?? 'user')
                    ->where('operation_type', 'offline')
                    ->count();
            })
            ->color('danger')
            ->hidden(fn () => ($this->activeTab ?? 'admin') !== 'admin');

        return parent::table($table)->columns($columns)->paginated([10, 25, 50, 100]);
    }

    public function getTableRecords(): EloquentCollection
    {
        $activeTab = $this->activeTab ?? 'staff';

        if ($activeTab === 'admin') {
            // 只显示管理员
            $admins = AdminUser::all()->map(function ($admin) {
                $admin->operator_type = 'admin';
                $admin->display_type = '管理员';
                $admin->display_name = $admin->name ?? $admin->username;
                $admin->phone = $admin->username; // 管理员用 username 代替 phone
                $admin->avatar = null; // 管理员没有头像
                return $admin;
            });

            $sorted = $admins->sortByDesc(function ($record) {
                // 按今日发布数量排序
                return \App\Models\HouseOperationLog::where('operator_id', $record->id)
                    ->where('operator_type', 'admin')
                    ->where('operation_type', 'publish')
                    ->whereDate('created_at', today())
                    ->count();
            });

            return new EloquentCollection($sorted->values()->all());
        } else {
            // 只显示员工
            $staffUsers = User::where('is_staff', 1)->get()->map(function ($user) {
                $user->operator_type = 'user';
                $user->display_type = '员工';
                $user->display_name = $user->nickname ?? $user->phone;
                return $user;
            });

            $sorted = $staffUsers->sortByDesc(function ($record) {
                // 按今日发布数量排序
                return \App\Models\HouseOperationLog::where('operator_id', $record->id)
                    ->where('operator_type', 'user')
                    ->where('operation_type', 'publish')
                    ->whereDate('created_at', today())
                    ->count();
            });

            return new EloquentCollection($sorted->values()->all());
        }
    }
}

