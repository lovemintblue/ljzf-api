<?php

namespace App\Filament\Resources\HouseResource\Pages;

use App\Filament\Resources\HouseResource;
use App\Models\AdminUser;
use App\Models\House;
use App\Models\HouseFollowUp;
use App\Models\HouseOperationLog;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class HouseFollowUpAndOperationLogs extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string $resource = HouseResource::class;

    protected static string $view = 'filament.resources.house-resource.pages.house-follow-up-and-operation-logs';

    protected static ?string $title = '跟进操作日志';
    
    protected static bool $shouldRegisterNavigation = false;

    // 缓存操作人信息
    protected static array $operatorCache = [];

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }
    
    public function getTitle(): string
    {
        return "跟进操作日志 - 房源#{$this->record->no}";
    }

    protected function getOperator($operatorId, $operatorType)
    {
        if (!$operatorId) {
            return null;
        }

        $cacheKey = "{$operatorType}_{$operatorId}";
        
        if (!isset(static::$operatorCache[$cacheKey])) {
            if ($operatorType === 'admin') {
                static::$operatorCache[$cacheKey] = AdminUser::find($operatorId);
            } else {
                static::$operatorCache[$cacheKey] = User::find($operatorId);
            }
        }

        return static::$operatorCache[$cacheKey];
    }

    public function getTableRecords(): EloquentCollection
    {
        // 获取操作日志
        $operationLogs = HouseOperationLog::query()
            ->where('house_id', $this->record->id)
            ->get();

        // 获取跟进记录
        $followUps = HouseFollowUp::query()
            ->where('house_id', $this->record->id)
            ->with('user')
            ->get();

        // 为每条记录添加类型标记和显示字段
        $operationLogs->each(function ($log) {
            $log->record_type = 'operation';
            
            // 根据操作类型设置显示字段
            switch ($log->operation_type) {
                case 'publish':
                    $log->operation_type_name = '首次发布';
                    $log->operation_type_color = 'success';
                    $log->operation_type_icon = 'heroicon-o-check-circle';
                    break;
                case 'online':
                    $log->operation_type_name = '上架';
                    $log->operation_type_color = 'success';
                    $log->operation_type_icon = 'heroicon-o-arrow-up';
                    break;
                case 'offline':
                    $log->operation_type_name = '下架';
                    $log->operation_type_color = 'warning';
                    $log->operation_type_icon = 'heroicon-o-arrow-down';
                    break;
                case 'update':
                    $log->operation_type_name = '更新排序';
                    $log->operation_type_color = 'info';
                    $log->operation_type_icon = 'heroicon-o-arrow-path';
                    break;
                default:
                    $log->operation_type_name = '操作';
                    $log->operation_type_color = 'gray';
                    $log->operation_type_icon = 'heroicon-o-document';
                    break;
            }
        });

        $followUps->each(function ($followUp) {
            $followUp->record_type = 'followup';
            // 添加虚拟字段以便统一显示
            $followUp->operation_type = 'followup';
            $followUp->operation_type_name = '跟进';
            $followUp->operation_type_color = 'warning';
            $followUp->operation_type_icon = 'heroicon-o-chat-bubble-left-right';
            $followUp->operator_id = $followUp->user_id;
            $followUp->operator_type = 'user';
        });

        // 合并并按时间排序
        $merged = $operationLogs->concat($followUps)->sortByDesc('created_at');
        
        return new EloquentCollection($merged->values()->all());
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(HouseOperationLog::query()->whereRaw('1 = 0'))
            ->paginated(false)
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('operation_type_name')
                    ->label('类型')
                    ->badge()
                    ->color(fn ($record) => $record->operation_type_color ?? 'gray')
                    ->icon(fn ($record) => $record->operation_type_icon ?? 'heroicon-o-document'),
                Tables\Columns\ImageColumn::make('operator_avatar')
                    ->label('头像')
                    ->circular()
                    ->getStateUsing(function ($record) {
                        if (!$record->operator_id) return null;
                        if ($record->operator_type === 'user') {
                            $operator = $this->getOperator($record->operator_id, $record->operator_type);
                            return $operator?->avatar;
                        }
                        return null;
                    })
                    ->defaultImageUrl(function ($record) {
                        if ($record->operator_type === 'admin' && $record->operator_id) {
                            $operator = $this->getOperator($record->operator_id, $record->operator_type);
                            $name = $operator ? $operator->name : "管理员";
                            $firstChar = mb_substr($name, 0, 1);
                            $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40">
                                <circle cx="20" cy="20" r="20" fill="#3b82f6"/>
                                <text x="20" y="25" text-anchor="middle" fill="white" font-size="16" font-weight="bold" font-family="Arial">' . htmlspecialchars($firstChar) . '</text>
                            </svg>';
                            return 'data:image/svg+xml;base64,' . base64_encode($svg);
                        }
                        return url('/images/default-avatar.png');
                    }),
                Tables\Columns\TextColumn::make('operator_name')
                    ->label('操作人')
                    ->getStateUsing(function ($record) {
                        if (!$record->operator_id) return '系统';
                        $operator = $this->getOperator($record->operator_id, $record->operator_type);
                        if ($record->operator_type === 'admin') {
                            return $operator ? $operator->name : "管理员#{$record->operator_id}";
                        } else {
                            return $operator ? $operator->nickname : "用户#{$record->operator_id}";
                        }
                    })
                    ->description(function ($record) {
                        if (!$record->operator_id) return null;
                        $operator = $this->getOperator($record->operator_id, $record->operator_type);
                        
                        if ($record->operator_type === 'admin') {
                            $username = $operator ? $operator->username : '';
                            return $username ? "后台管理员 ({$username})" : '后台管理员';
                        } else {
                            $phone = $operator ? $operator->phone : '';
                            if ($operator && $operator->is_staff) {
                                return $phone ? "员工 ({$phone})" : '员工';
                            }
                            return $phone ? "用户 ({$phone})" : '用户';
                        }
                    }),
                Tables\Columns\TextColumn::make('content')
                    ->label('内容')
                    ->getStateUsing(function ($record) {
                        // 判断是跟进记录还是操作日志
                        if ($record->record_type === 'followup') {
                            return $record->result ?? '-';
                        }
                        
                        // 操作日志根据类型显示
                        if ($record->operation_type === 'publish') {
                            return '首次发布';
                        } elseif ($record->operation_type === 'online') {
                            return '重新上架';
                        } elseif ($record->operation_type === 'update') {
                            return '更新排序';
                        } elseif ($record->operation_type === 'offline') {
                            return $record->reason ?? '下架';
                        }
                        
                        return $record->reason ?? '-';
                    })
                    ->limit(50),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('时间')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('返回')
                ->url(HouseResource::getUrl('index'))
                ->icon('heroicon-o-arrow-left')
                ->color('gray'),
        ];
    }
}

