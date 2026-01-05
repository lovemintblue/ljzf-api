<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HouseFollowUpResource\Pages;
use App\Filament\Resources\HouseFollowUpResource\RelationManagers;
use App\Models\House;
use App\Models\HouseFollowUp;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HouseFollowUpResource extends Resource
{
    protected static ?string $model = HouseFollowUp::class;

    protected static ?string $navigationIcon = 'heroicon-m-squares-2x2';

    protected static ?string $navigationGroup = '房源';

    protected static ?string $navigationLabel = '跟进记录';

    protected static ?string $label = '跟进记录';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('result', '已出租')
            ->where('is_punished', 0)
            ->where('is_processed', 0)
            ->whereHas('house', function ($query) {
                $query->where('is_show', 1);
            })
            ->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::where('result', '已出租')
            ->where('is_punished', 0)
            ->where('is_processed', 0)
            ->whereHas('house', function ($query) {
                $query->where('is_show', 1);
            })
            ->count();
        if ($count > 10) {
            return 'danger';
        } elseif ($count > 5) {
            return 'warning';
        }
        return 'success';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'id')
                    ->required(),
                Forms\Components\Select::make('house_id')
                    ->relationship('house', 'title')
                    ->required(),
                Forms\Components\TextInput::make('result')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    /**
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                // 检查筛选器是否激活，如果没有激活，默认只显示未处理的记录
                $filters = request()->get('tableFilters', []);
                $filterValue = $filters['is_processed'] ?? null;
                
                // 如果筛选器没有激活（值为 null），默认应用未处理筛选
                if ($filterValue === null) {
                    $query->where('house_follow_ups.is_punished', 0)
                          ->where('house_follow_ups.is_processed', 0);
                }
                
                // 添加自定义排序：未处理的在前，已处理的在后
                return $query->leftJoin('houses', 'house_follow_ups.house_id', '=', 'houses.id')
                    ->select('house_follow_ups.*')
                    ->orderByRaw('CASE 
                        WHEN house_follow_ups.is_punished = 0 AND house_follow_ups.is_processed = 0 AND houses.is_show = 1 THEN 0 
                        ELSE 1 
                    END')
                    ->orderBy('house_follow_ups.created_at', 'desc');
            })
            ->columns([
                Tables\Columns\ImageColumn::make('user.avatar')
                    ->label('头像'),
                Tables\Columns\TextColumn::make('user.nickname')
                    ->label('跟进人')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.phone')
                    ->label('跟进电话')
                    ->searchable(),
                Tables\Columns\TextColumn::make('house.community.name')
                    ->label('小区')
                    ->searchable(),
                Tables\Columns\TextColumn::make('house.unit')
                    ->label('单元')
                    ->searchable(),
                Tables\Columns\TextColumn::make('house.building_number')
                    ->label('栋数')
                    ->searchable(),
                Tables\Columns\TextColumn::make('house.room_number')
                    ->label('房号')
                    ->searchable(),
                Tables\Columns\TextColumn::make('room_config')
                    ->label('房间配置')
                    ->getStateUsing(function (HouseFollowUp $record) {
                        return $record->house->room_count . '室|' . $record->house->living_room_count . '厅|' . $record->house->bathroom_count . '卫';
                    }),
                Tables\Columns\TextColumn::make('house.no')
                    ->label('房源编号')
                    ->searchable(),
                Tables\Columns\TextColumn::make('house.contact_name')
                    ->label('房源联系人')
                    ->searchable(),
                Tables\Columns\TextColumn::make('house.contact_phone')
                    ->label('房源联系电话')
                    ->searchable()
                    ->description(function (HouseFollowUp $record) {
                        if (!$record->house) {
                            return null;
                        }
                        $phone = $record->house->contact_phone;
                        $count = \App\Models\House::where('contact_phone', $phone)
                            ->where('audit_status', 1)
                            ->where('is_draft', 0)
                            ->count();
                        $url = HouseResource::getUrl('phone', ['phone' => $phone]);
                        return new \Illuminate\Support\HtmlString(
                            '<a href="' . e($url) . '" target="_blank" style="color: #3b82f6; font-weight: 600; text-decoration: none;">共' . $count . '套房源</a>'
                        );
                    })
                    ->html(),
                Tables\Columns\TextColumn::make('result')
                    ->label('跟进结果'),
                Tables\Columns\IconColumn::make('is_punished')
                    ->label('处罚状态')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('跟进时间')
                    ->dateTime('Y-m-d H:i:s')
            ])
            ->recordUrl(null)
            ->recordAction(null)
            ->filters([
                Tables\Filters\SelectFilter::make('is_processed')
                    ->label('处理状态')
                    ->native(false)
                    ->options([
                        '' => '全部',
                        0 => '未处理',
                        1 => '已处理',
                    ])
                    ->default(0)
                    ->query(function (Builder $query, array $data): Builder {
                        // 获取筛选值，处理字符串和数字类型
                        $value = $data['value'] ?? null;
                        
                        // 转换为字符串进行比较，避免类型问题
                        $valueStr = (string)$value;
                        
                        if ($valueStr === '' || $value === null) {
                            // 如果选择"全部"，不添加筛选条件，显示所有记录
                            return $query;
                        }
                        
                        // 使用严格比较，确保 0 和 '0' 都能正确匹配
                        if ($valueStr === '0' || $value === 0) {
                            // 未处理：既未处罚也未下架房源（屏蔽已处理的）
                            return $query->where(function ($q) {
                                $q->where('house_follow_ups.is_punished', 0)
                                  ->where('house_follow_ups.is_processed', 0);
                            });
                        } else {
                            // 已处理：已处罚或已下架房源（屏蔽未处理的）
                            return $query->where(function ($q) {
                                $q->where('house_follow_ups.is_punished', 1)
                                  ->orWhere('house_follow_ups.is_processed', 1);
                            });
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('punish')
                    ->label(fn(HouseFollowUp $record) => $record->is_punished ? '已处罚' : '处罚')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color(fn(HouseFollowUp $record) => $record->is_punished ? 'gray' : 'warning')
                    ->disabled(fn(HouseFollowUp $record) => $record->is_punished)
                    ->form([
                        Forms\Components\Textarea::make('comment')
                            ->label('违规原因')
                            ->placeholder('请输入违规原因，将发送警告通知给跟进用户')
                            ->required()
                            ->maxLength(500),
                    ])
                    ->modalHeading('处罚跟进人')
                    ->modalDescription('处罚将扣除2次查看电话次数，并向跟进用户发送违规警告')
                    ->modalSubmitActionLabel('确认处罚')
                    ->action(function (HouseFollowUp $record, array $data) {
                        $user = $record->user;
                        if ($user) {
                            // 获取当前个人额度调整值
                            $currentAdjustment = $user->view_phone_count ?? 0;
                            
                            // 累加扣减2次（如果已经被处罚过，继续累加）
                            $newAdjustment = $currentAdjustment - 2;
                            $user->update(['view_phone_count' => $newAdjustment]);

                            // 标记为已处罚
                            $record->update(['is_punished' => true]);

                            // 发送违规警告给跟进用户
                            (new \App\Services\NotificationService())->notifyViolationWarning(
                                $user,
                                '跟进行为',
                                '恶意跟进',
                                $data['comment']
                            );

                            // 后台提示
                            Notification::make()
                                ->title('处罚成功')
                                ->body("已累计扣减用户 {$user->nickname} 的每日额度，当前调整值: {$newAdjustment} 次/天")
                                ->warning()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('处罚失败')
                                ->body('无法找到对应的跟进人')
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('house_status')
                    ->label(function (HouseFollowUp $record) {
                        return $record->house && $record->house->is_show == 1 ? '下架房源' : '已下架';
                    })
                    ->color(function (HouseFollowUp $record) {
                        return $record->house && $record->house->is_show == 1 ? 'danger' : 'gray';
                    })
                    ->disabled(function (HouseFollowUp $record) {
                        return !$record->house || $record->house->is_show == 0;
                    })
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('下架原因')
                            ->placeholder('请输入下架原因，将发送通知给房东')
                            ->required()
                            ->maxLength(500)
                            ->default('因跟进异常，房源已下架'),
                    ])
                    ->modalHeading('下架房源')
                    ->modalDescription('下架后将通知房东，请填写下架原因')
                    ->action(function (HouseFollowUp $record, array $data) {
                        if ($record->house && $record->house->is_show == 1) {
                            $house = $record->house;
                            $house->update(['is_show' => 0]);
                            
                            // 标记跟进记录为已处理，避免房源重新上架后再次显示
                            $record->update(['is_processed' => true]);
                            
                            // 发送下架通知给房东
                            if ($house->user) {
                                (new \App\Services\NotificationService())->notifyHouseOffline(
                                    $house->user,
                                    $house,
                                    $data['reason'] ?? '管理员下架'
                                );
                            }
                            
                            Notification::make()->title('下架成功，已通知房东')->success()->send();
                        }
                    })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('批量下架房源')
                        ->color('danger')
                        ->icon('heroicon-o-archive-box-x-mark')
                        ->form([
                            Forms\Components\Textarea::make('reason')
                                ->label('下架原因')
                                ->placeholder('请输入下架原因，将发送通知给所有房东')
                                ->required()
                                ->maxLength(500)
                                ->default('因跟进异常，房源已下架'),
                        ])
                        ->deselectRecordsAfterCompletion()
                        ->modalHeading('批量下架房源')
                        ->modalDescription('确定要下架所选记录对应的上架中房源吗？将通知所有房东。')
                        ->modalSubmitActionLabel('确认下架')
                        ->action(function ($records, array $data) {
                            $count = 0;
                            $skipped = 0;
                            $notificationService = new \App\Services\NotificationService();
                            
                            foreach ($records as $record) {
                                if ($record->house) {
                                    if ($record->house->is_show == 1) {
                                        $house = $record->house;
                                        $house->update(['is_show' => 0]);
                                        
                                        // 标记跟进记录为已处理，避免房源重新上架后再次显示
                                        $record->update(['is_processed' => true]);
                                        
                                        // 通知房东
                                        if ($house->user) {
                                            $notificationService->notifyHouseOffline(
                                                $house->user,
                                                $house,
                                                $data['reason'] ?? '管理员下架'
                                            );
                                        }
                                        
                                        $count++;
                                    } else {
                                        $skipped++;
                                    }
                                }
                            }

                            $message = "成功下架 {$count} 个房源并通知房东";
                            if ($skipped > 0) {
                                $message .= "，跳过 {$skipped} 个已下架房源";
                            }

                            Notification::make()
                                ->title($message)
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }

    /**
     * @return array|PageRegistration[]
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageHouseFollowUps::route('/'),
        ];
    }
}
