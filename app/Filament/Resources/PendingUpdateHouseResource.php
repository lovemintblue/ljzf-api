<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PendingUpdateHouseResource\Pages;
use App\Models\Community;
use App\Models\House;
use App\Models\HouseOperationLog;
use App\Models\User;
use App\Services\NotificationService;
use App\Settings\GeneralSettings;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PendingUpdateHouseResource extends Resource
{
    protected static ?string $model = House::class;

    protected static ?string $navigationIcon = 'heroicon-m-squares-2x2';

    protected static ?string $navigationGroup = '房源';

    protected static ?string $navigationLabel = '待更新房源';

    protected static ?string $label = '待更新房源';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        $settings = app(GeneralSettings::class);
        $days = $settings->house_update_days;
        $cutoffDate = Carbon::now()->subDays($days);

        return House::where('is_show', 1)
            ->where('audit_status', 1)
            ->where('is_draft', 0)
            ->where(function ($q) use ($cutoffDate) {
                $q->where(function ($subQ) use ($cutoffDate) {
                    $subQ->whereNotNull('last_updated_at')
                        ->where('last_updated_at', '<', $cutoffDate);
                })->orWhere(function ($subQ) use ($cutoffDate) {
                    $subQ->whereNull('last_updated_at')
                        ->where('created_at', '<', $cutoffDate);
                });
            })
            ->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getNavigationBadge();
        if ($count > 50) {
            return 'danger';
        } elseif ($count > 20) {
            return 'warning';
        }
        return 'success';
    }

    public static function getEloquentQuery(): Builder
    {
        $settings = app(GeneralSettings::class);
        $days = $settings->house_update_days;
        $cutoffDate = Carbon::now()->subDays($days);

        return parent::getEloquentQuery()
            ->where('is_show', 1)
            ->where('audit_status', 1)
            ->where('is_draft', 0)
            ->where(function ($q) use ($cutoffDate) {
                $q->where(function ($subQ) use ($cutoffDate) {
                    $subQ->whereNotNull('last_updated_at')
                        ->where('last_updated_at', '<', $cutoffDate);
                })->orWhere(function ($subQ) use ($cutoffDate) {
                    $subQ->whereNull('last_updated_at')
                        ->where('created_at', '<', $cutoffDate);
                });
            });
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'asc')
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->label('封面图'),
                Tables\Columns\TextColumn::make('no')
                    ->label('编号')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.nickname')
                    ->label('发布人'),
                Tables\Columns\TextColumn::make('community.name')
                    ->label('小区')
                    ->searchable(),
                Tables\Columns\TextColumn::make('building_number')
                    ->label('栋数')
                    ->searchable(),
                Tables\Columns\TextColumn::make('unit')
                    ->label('单元')
                    ->searchable(),
                Tables\Columns\TextColumn::make('floor')
                    ->label('楼层')
                    ->searchable(),
                Tables\Columns\TextColumn::make('room_number')
                    ->label('房号')
                    ->searchable(),
                Tables\Columns\TextColumn::make('room_config')
                    ->label('房间配置')
                    ->getStateUsing(function (House $record) {
                        return $record->room_count . '室|' . $record->living_room_count . '厅|' . $record->bathroom_count . '卫';
                    }),
                Tables\Columns\TextColumn::make('rent_price')
                    ->label('租金')
                    ->searchable(),
                Tables\Columns\TextColumn::make('contact_name')
                    ->label('联系人')
                    ->searchable()
                    ->description(fn (House $record) => $record->backup_contact_name ? "备用: {$record->backup_contact_name}" : null),
                Tables\Columns\TextColumn::make('contact_phone')
                    ->label('联系电话')
                    ->searchable()
                    ->description(function (House $record) {
                        $count = House::where('contact_phone', $record->contact_phone)
                            ->where('audit_status', 1)
                            ->where('is_draft', 0)
                            ->count();
                        $url = HouseResource::getUrl('phone', ['phone' => $record->contact_phone, 'from' => 'pending']);
                        $desc = '<a href="' . e($url) . '" target="_blank" style="color: #3b82f6; font-weight: 600; text-decoration: none;">共' . $count . '套房源</a>';
                        if (!empty($record->backup_contact_phone)) {
                            $desc .= '<br>备用: ' . e($record->backup_contact_phone);
                        }
                        return new \Illuminate\Support\HtmlString($desc);
                    })
                    ->html(),
                Tables\Columns\TextColumn::make('last_updated_at')
                    ->label('最后更新时间')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->default(fn (House $record) => $record->created_at)
                    ->description(function (House $record) {
                        $lastUpdate = $record->last_updated_at ?: $record->created_at;
                        $days = (int) Carbon::parse($lastUpdate)->diffInDays(now());
                        return "已 {$days} 天未更新";
                    }),
            ])
            ->recordUrl(null)
            ->recordAction(null)
            ->filters([
                // 第一排：发布人、小区、栋数、房号
                SelectFilter::make('user_id')
                    ->label('发布人')
                    ->relationship(
                        'user',
                        'nickname',
                        fn (Builder $query) => $query->whereNotNull('nickname')
                    )
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->nickname ?: '未设置昵称')
                    ->searchable()
                    ->preload()
                    ->native(false),
                SelectFilter::make('community_id')
                    ->label('小区')
                    ->searchable()
                    ->native(false)
                    ->options(Community::all()->pluck('name', 'id')),
                Tables\Filters\Filter::make('building_number')
                    ->label('栋数')
                    ->form([
                        Forms\Components\TextInput::make('building_number')
                            ->label('栋数')
                            ->placeholder('输入栋数搜索')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['building_number'],
                                fn (Builder $query, $value): Builder => $query->where('building_number', 'like', "%{$value}%")
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['building_number']) {
                            return null;
                        }
                        return '栋数: ' . $data['building_number'];
                    }),
                Tables\Filters\Filter::make('room_number')
                    ->label('房号')
                    ->form([
                        Forms\Components\TextInput::make('room_number')
                            ->label('房号')
                            ->placeholder('输入房号搜索')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['room_number'],
                                fn (Builder $query, $value): Builder => $query->where('room_number', 'like', "%{$value}%")
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['room_number']) {
                            return null;
                        }
                        return '房号: ' . $data['room_number'];
                    }),
                // 第二排：室、厅、卫
                SelectFilter::make('room_count')
                    ->label('室')
                    ->native(false)
                    ->options([
                        1 => '1室',
                        2 => '2室',
                        3 => '3室',
                        4 => '4室',
                    ]),
                SelectFilter::make('living_room_count')
                    ->label('厅')
                    ->native(false)
                    ->options([
                        0 => '0厅',
                        1 => '1厅',
                        2 => '2厅',
                    ]),
                SelectFilter::make('bathroom_count')
                    ->label('卫')
                    ->native(false)
                    ->options([
                        0 => '0卫',
                        1 => '1卫',
                        2 => '2卫',
                    ]),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->actions([
                Tables\Actions\Action::make('更新')
                    ->color('success')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->modalHeading('确认更新房源')
                    ->modalDescription('更新后房源的时间将变为当前时间，会排到列表最前面')
                    ->action(function (House $record) {
                        $record->created_at = now();
                        $record->last_updated_at = now();
                        $record->save();
                        
                        // 记录操作日志
                        HouseOperationLog::create([
                            'house_id' => $record->id,
                            'operator_id' => auth()->id(),
                            'operator_type' => 'admin',
                            'operation_type' => 'update',
                        ]);
                    })
                    ->successNotification(
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('更新成功')
                            ->body('房源已更新，现在会显示在列表最前面。')
                    ),
                Tables\Actions\Action::make('下架')
                    ->color('warning')
                    ->icon('heroicon-o-arrow-down')
                    ->visible(fn(House $record) => $record->is_show)
                    ->requiresConfirmation()
                    ->modalHeading('确认下架')
                    ->modalDescription('下架后房源将不对用户显示。')
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('下架原因')
                            ->required()
                            ->default('管理员下架')
                            ->rows(3),
                    ])
                    ->action(function (House $record, array $data) {
                        $record->is_show = false;
                        $record->save();
                        
                        // 记录操作日志
                        HouseOperationLog::create([
                            'house_id' => $record->id,
                            'operator_id' => auth()->id(),
                            'operator_type' => 'admin',
                            'operation_type' => 'offline',
                            'reason' => $data['reason'] ?? null,
                        ]);

                        // 发送下架通知
                        if ($record->user) {
                            (new NotificationService())->notifyHouseOffline(
                                $record->user,
                                $record,
                                $data['reason'] ?? '管理员下架'
                            );
                        }
                    }),
                Tables\Actions\Action::make('follow_up_logs')
                    ->label('跟进操作日志')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->modalWidth('7xl')
                    ->modalHeading(fn (House $record) => '跟进操作日志 - ' . $record->no)
                    ->modalContent(fn (House $record) => view('filament.modals.house-logs', ['house' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('关闭'),
                Tables\Actions\EditAction::make()
                    ->modalWidth('7xl')
                    ->modalHeading('编辑房源')
                    ->slideOver(false)
                    ->form(HouseResource::getFormSchema()),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePendingUpdateHouses::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}

