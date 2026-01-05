<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TopHouseResource\Pages;
use App\Models\House;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TopHouseResource extends Resource
{
    protected static ?string $model = House::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = '房源';

    protected static ?string $navigationLabel = '房源推广';

    protected static ?string $label = '推广房源';

    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('is_top', true)
            ->where('audit_status', 1)
            ->where('is_draft', 0)
            ->orderBy('top_at', 'desc');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('置顶设置')
                    ->schema([
                        Forms\Components\Toggle::make('is_top')
                            ->label('置顶状态')
                            ->default(true)
                            ->disabled()
                            ->helperText('关闭置顶请使用下方的"取消置顶"按钮'),
                        Forms\Components\DateTimePicker::make('top_at')
                            ->label('置顶时间')
                            ->required()
                            ->native(false)
                            ->displayFormat('Y-m-d H:i')
                            ->helperText('修改时间可以调整排序，时间越晚排序越靠前'),
                        Forms\Components\DatePicker::make('top_expires_at')
                            ->label('到期日期')
                            ->native(false)
                            ->displayFormat('Y-m-d')
                            ->minDate(now())
                            ->helperText('到期后系统将在凌晨3点自动取消置顶。留空表示永久置顶'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('top_at', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->label('封面图'),
                Tables\Columns\TextColumn::make('no')
                    ->label('编号')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('community.name')
                    ->label('小区')
                    ->searchable(),
                Tables\Columns\TextColumn::make('building_number')
                    ->label('栋数')
                    ->searchable(),
                Tables\Columns\TextColumn::make('room_config')
                    ->label('房间配置')
                    ->getStateUsing(function (House $record) {
                        return $record->room_count . '室' . $record->living_room_count . '厅' . $record->bathroom_count . '卫';
                    }),
                Tables\Columns\TextColumn::make('rent_price')
                    ->label('租金')
                    ->money('CNY')
                    ->sortable(),
                Tables\Columns\TextColumn::make('is_show')
                    ->label('上架状态')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                    ->formatStateUsing(fn (bool $state): string => $state ? '已上架' : '已下架'),
                Tables\Columns\TextColumn::make('top_at')
                    ->label('置顶时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('top_expires_at')
                    ->label('到期日期')
                    ->date('Y-m-d')
                    ->sortable()
                    ->badge()
                    ->color(function (House $record): string {
                        if (!$record->top_expires_at) {
                            return 'gray';
                        }
                        $daysLeft = (int)now()->diffInDays($record->top_expires_at, false);
                        if ($daysLeft < 0) {
                            return 'danger';
                        } elseif ($daysLeft <= 3) {
                            return 'warning';
                        } elseif ($daysLeft <= 7) {
                            return 'info';
                        }
                        return 'success';
                    })
                    ->formatStateUsing(function (House $record): string {
                        if (!$record->top_expires_at) {
                            return '永久';
                        }
                        return $record->top_expires_at->format('Y-m-d');
                    })
                    ->description(function (House $record): ?string {
                        if (!$record->top_expires_at) {
                            return null;
                        }
                        $daysLeft = (int)now()->diffInDays($record->top_expires_at, false);
                        if ($daysLeft < 0) {
                            return '已过期';
                        } elseif ($daysLeft == 0) {
                            return '今天到期';
                        }
                        return "剩余 {$daysLeft} 天";
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_show')
                    ->label('上架状态')
                    ->options([
                        1 => '已上架',
                        0 => '已下架',
                    ])
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\Action::make('adjust_top_time')
                    ->label('调整时间')
                    ->icon('heroicon-o-clock')
                    ->color('info')
                    ->form([
                        Forms\Components\DateTimePicker::make('top_at')
                            ->label('置顶时间')
                            ->required()
                            ->native(false)
                            ->displayFormat('Y-m-d H:i')
                            ->default(fn (House $record) => $record->top_at),
                        Forms\Components\DatePicker::make('top_expires_at')
                            ->label('到期日期')
                            ->native(false)
                            ->displayFormat('Y-m-d')
                            ->minDate(now())
                            ->helperText('到期后系统将在凌晨3点自动取消置顶。留空表示永久置顶')
                            ->default(fn (House $record) => $record->top_expires_at),
                    ])
                    ->action(function (House $record, array $data) {
                        $record->top_at = $data['top_at'];
                        $record->top_expires_at = $data['top_expires_at'] ?? null;
                        $record->save();
                    })
                    ->successNotification(
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('时间已调整')
                            ->body('置顶时间已更新，排序已刷新。')
                    ),
                Tables\Actions\Action::make('cancel_top')
                    ->label('取消置顶')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('确认取消置顶')
                    ->modalDescription('取消后房源将不再在小程序首页置顶显示。')
                    ->action(function (House $record) {
                        $record->is_top = false;
                        $record->top_at = null;
                        $record->top_expires_at = null;
                        $record->save();
                    })
                    ->successNotification(
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('已取消置顶')
                            ->body('房源已从推广列表中移除。')
                    ),
                Tables\Actions\EditAction::make()
                    ->label('查看详情')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalWidth('7xl')
                    ->modalHeading('查看/编辑房源')
                    ->slideOver(false)
                    ->form(fn () => HouseResource::getFormSchema()),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('cancel_top_bulk')
                    ->label('批量取消置顶')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($records) {
                        foreach ($records as $record) {
                            $record->is_top = false;
                            $record->top_at = null;
                            $record->top_expires_at = null;
                            $record->save();
                        }
                    })
                    ->deselectRecordsAfterCompletion(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageTopHouses::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_top', true)
            ->where('audit_status', 1)
            ->where('is_draft', 0)
            ->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}

