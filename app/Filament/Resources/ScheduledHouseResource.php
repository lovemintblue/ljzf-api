<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduledHouseResource\Pages;
use App\Filament\Resources\HouseResource;
use App\Models\House;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ScheduledHouseResource extends Resource
{
    protected static ?string $model = House::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = '房源';

    protected static ?string $navigationLabel = '待发布列表';

    protected static ?string $label = '待发布房源';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereNotNull('scheduled_publish_at')
            ->where('is_show', 0)
            ->where('audit_status', 1)
            ->orderBy('scheduled_publish_at', 'asc');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('预约上架时间')
                    ->schema([
                        Forms\Components\DatePicker::make('scheduled_publish_at')
                            ->label('预约上架日期')
                            ->required()
                            ->native(false)
                            ->displayFormat('Y-m-d')
                            ->minDate(now())
                            ->helperText('房源将在选择日期的凌晨2点自动上架')
                            ->afterStateUpdated(function ($state, callable $set) {
                                // 自动设置为凌晨2点
                                if ($state) {
                                    $date = \Carbon\Carbon::parse($state)->setTime(2, 0, 0);
                                    $set('scheduled_publish_at', $date);
                                }
                            }),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('scheduled_publish_at', 'asc')
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->label('封面图'),
                Tables\Columns\TextColumn::make('no')
                    ->label('编号')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.nickname')
                    ->label('发布人')
                    ->searchable(),
                Tables\Columns\TextColumn::make('community.name')
                    ->label('小区')
                    ->searchable(),
                Tables\Columns\TextColumn::make('building_number')
                    ->label('栋数')
                    ->searchable(),
                Tables\Columns\TextColumn::make('unit')
                    ->label('单元'),
                Tables\Columns\TextColumn::make('floor')
                    ->label('楼层'),
                Tables\Columns\TextColumn::make('room_number')
                    ->label('房号')
                    ->searchable(),
                Tables\Columns\TextColumn::make('room_config')
                    ->label('户型')
                    ->getStateUsing(function (House $record) {
                        return $record->room_count . '室' . $record->living_room_count . '厅' . $record->bathroom_count . '卫';
                    }),
                Tables\Columns\TextColumn::make('rent_price')
                    ->label('租金')
                    ->money('CNY'),
                Tables\Columns\TextColumn::make('scheduled_publish_at')
                    ->label('预约上架时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->badge()
                    ->color(function ($state) {
                        if (!$state) return 'gray';
                        $now = now();
                        $scheduled = \Carbon\Carbon::parse($state);
                        
                        if ($scheduled->isPast()) {
                            return 'danger'; // 已过期，红色
                        } elseif ($scheduled->diffInHours($now) <= 24) {
                            return 'warning'; // 24小时内，黄色
                        } else {
                            return 'success'; // 未来时间，绿色
                        }
                    }),
                Tables\Columns\TextColumn::make('remaining_time')
                    ->label('剩余时间')
                    ->getStateUsing(function (House $record) {
                        if (!$record->scheduled_publish_at) return '-';
                        
                        $now = now();
                        $scheduled = \Carbon\Carbon::parse($record->scheduled_publish_at);
                        
                        if ($scheduled->isPast()) {
                            return '待执行';
                        }
                        
                        $diff = $now->diff($scheduled);
                        
                        if ($diff->days > 0) {
                            return $diff->days . '天';
                        } elseif ($diff->h > 0) {
                            return $diff->h . '小时';
                        } else {
                            return $diff->i . '分钟';
                        }
                    })
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('发布人')
                    ->relationship(
                        'user',
                        'nickname',
                        fn (Builder $query) => $query->whereNotNull('nickname')
                    )
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->nickname ?: '未设置昵称')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('修改时间')
                    ->modalHeading('修改预约上架时间')
                    ->modalWidth('md'),
                Tables\Actions\EditAction::make('edit_house')
                    ->label('编辑房源')
                    ->modalHeading('编辑房源信息')
                    ->modalWidth('7xl')
                    ->slideOver(false)
                    ->form(HouseResource::getFormSchema())
                    ->icon('heroicon-o-pencil-square')
                    ->color('info'),
                Tables\Actions\Action::make('publish_now')
                    ->label('立即上架')
                    ->icon('heroicon-o-arrow-up-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('确认立即上架')
                    ->modalDescription('此操作将立即上架该房源，并清除预约时间。')
                    ->action(function (House $record) {
                        $record->is_show = 1;
                        $record->scheduled_publish_at = null;
                        $record->save();
                        
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('上架成功')
                            ->body("房源 {$record->no} 已立即上架")
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->label('取消发布'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('批量取消发布'),
                    Tables\Actions\BulkAction::make('bulk_publish_now')
                        ->label('批量立即上架')
                        ->icon('heroicon-o-arrow-up-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $count = 0;
                            foreach ($records as $record) {
                                $record->is_show = 1;
                                $record->scheduled_publish_at = null;
                                $record->save();
                                $count++;
                            }
                            
                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('批量上架成功')
                                ->body("已成功上架 {$count} 个房源")
                                ->send();
                        }),
                ]),
            ])
            ->emptyStateHeading('暂无待发布房源')
            ->emptyStateDescription('当前没有设置了预约上架时间的房源')
            ->emptyStateIcon('heroicon-o-clock');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageScheduledHouses::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereNotNull('scheduled_publish_at')
            ->where('is_show', 0)
            ->where('audit_status', 1)
            ->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getNavigationBadge();
        
        if ($count > 10) {
            return 'danger';
        } elseif ($count > 5) {
            return 'warning';
        } elseif ($count > 0) {
            return 'success';
        }
        
        return 'gray';
    }
}

