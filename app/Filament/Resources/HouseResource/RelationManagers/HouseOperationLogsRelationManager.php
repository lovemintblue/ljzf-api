<?php

namespace App\Filament\Resources\HouseResource\RelationManagers;

use App\Models\AdminUser;
use App\Models\HouseOperationLog;
use App\Models\User;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class HouseOperationLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'operationLogs';

    protected static ?string $title = '操作日志';

    protected static ?string $recordTitleAttribute = 'operation_type';

    // 缓存操作人信息，避免重复查询
    protected static array $operatorCache = [];

    protected function getOperator(HouseOperationLog $record)
    {
        if (!$record->operator_id) {
            return null;
        }

        $cacheKey = "{$record->operator_type}_{$record->operator_id}";
        
        if (!isset(static::$operatorCache[$cacheKey])) {
            if ($record->operator_type === 'admin') {
                static::$operatorCache[$cacheKey] = AdminUser::find($record->operator_id);
            } else {
                static::$operatorCache[$cacheKey] = User::find($record->operator_id);
            }
        }

        return static::$operatorCache[$cacheKey];
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('operation_type')
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('operation_type')
                    ->label('操作类型')
                    ->badge()
                    ->getStateUsing(fn (HouseOperationLog $record) => $record->operation_type_name)
                    ->color(fn (HouseOperationLog $record) => $record->operation_type_color)
                    ->icon(fn (HouseOperationLog $record) => $record->operation_type_icon),
                Tables\Columns\ImageColumn::make('operator_avatar')
                    ->label('头像')
                    ->circular()
                    ->getStateUsing(function (HouseOperationLog $record) {
                        if (!$record->operator_id) return null;
                        if ($record->operator_type === 'user') {
                            $operator = $this->getOperator($record);
                            return $operator?->avatar;
                        }
                        return null;
                    })
                    ->defaultImageUrl(function (HouseOperationLog $record) {
                        // 为管理员生成SVG头像
                        if ($record->operator_type === 'admin' && $record->operator_id) {
                            $operator = $this->getOperator($record);
                            $name = $operator ? $operator->name : "管理员";
                            $firstChar = mb_substr($name, 0, 1);
                            // 生成SVG数据URL
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
                    ->getStateUsing(function (HouseOperationLog $record) {
                        if (!$record->operator_id) return '系统';
                        $operator = $this->getOperator($record);
                        if ($record->operator_type === 'admin') {
                            return $operator ? $operator->name : "管理员#{$record->operator_id}";
                        } else {
                            return $operator ? $operator->nickname : "用户#{$record->operator_id}";
                        }
                    })
                    ->description(function (HouseOperationLog $record) {
                        if (!$record->operator_id) return null;
                        $operator = $this->getOperator($record);
                        
                        if ($record->operator_type === 'admin') {
                            // 管理员显示用户名
                            $username = $operator ? $operator->username : '';
                            return $username ? "后台管理员 ({$username})" : '后台管理员';
                        } else {
                            // 用户/员工显示手机号
                            $phone = $operator ? $operator->phone : '';
                            if ($operator && $operator->is_staff) {
                                return $phone ? "员工 ({$phone})" : '员工';
                            }
                            return $phone ? "用户 ({$phone})" : '用户';
                        }
                    })
                    ->searchable(false),
                Tables\Columns\TextColumn::make('reason')
                    ->label('操作原因')
                    ->limit(50)
                    ->default('-')
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) > 50) {
                            return $state;
                        }
                        return null;
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('操作时间')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('operation_type')
                    ->label('操作类型')
                    ->options([
                        'publish' => '首次发布',
                        'offline' => '下架',
                        'update' => '更新排序',
                        'online' => '重新上架',
                    ]),
            ])
            ->headerActions([
                // 不允许手动创建操作日志
            ])
            ->actions([
                // 只读，不允许编辑和删除
            ])
            ->bulkActions([
                // 不允许批量操作
            ])
            ->emptyStateHeading('暂无操作记录')
            ->emptyStateDescription('当房源进行发布、下架、更新等操作时，会自动记录在此处')
            ->emptyStateIcon('heroicon-o-document-text');
    }
}

