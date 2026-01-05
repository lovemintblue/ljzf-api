<?php
/**
 * 管理后台 - 房源
 */

namespace App\Filament\Resources;

use App\Filament\Resources\HouseResource\Pages;
use App\Filament\Resources\HouseResource\RelationManagers;
use App\Models\Community;
use App\Models\Facility;
use App\Models\House;
use App\Models\HouseOperationLog;
use App\Models\Shop;
use App\Services\NotificationService;
use Filament\Forms;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Guava\FilamentModalRelationManagers\Actions\Table\RelationManagerAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HouseResource extends Resource
{
    protected static ?string $model = House::class;

    protected static ?string $navigationIcon = 'heroicon-m-squares-2x2';

    protected static ?string $navigationGroup = '房源';

    protected static ?string $navigationLabel = '房源列表';

    protected static ?string $label = '房源';

    protected static ?int $navigationSort = 1;

    /**
     * @param Form $form
     * @return Form
     */
    public static function form(Form $form): Form
    {
        return $form->schema(static::getFormSchema());
    }

    /**
     * @param Table $table
     * @return Table
     * @throws \Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->query(function (House $query) {
                return $query->where('audit_status', 1)->where('is_draft', 0);
            })
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
                    ->searchable(query: function ($query, $search) {
                        return $query->where(function ($q) use ($search) {
                            $q->where('contact_phone', 'like', "%{$search}%")
                              ->orWhere('backup_contact_phone', 'like', "%{$search}%");
                        });
                    })
                    ->description(function (House $record) {
                        $count = House::where('contact_phone', $record->contact_phone)
                            ->where('audit_status', 1)
                            ->where('is_draft', 0)
                            ->count();
                        $url = HouseResource::getUrl('phone', ['phone' => $record->contact_phone]);
                        $desc = '<a href="' . $url . '" target="_blank" style="color: #3b82f6; font-weight: 600; text-decoration: none;">共' . $count . '套房源</a>';
                        if (!empty($record->backup_contact_phone)) {
                            $desc .= ' | 备用: ' . e($record->backup_contact_phone);
                        }
                        return new \Illuminate\Support\HtmlString($desc);
                    }),
                Tables\Columns\TextColumn::make('is_show')
                    ->label('上架状态')
                    ->badge()
                    ->color(function (House $record): string {
                        // 如果有预约时间且未上架，显示橙色
                        if ($record->scheduled_publish_at && !$record->is_show) {
                            return 'warning';
                        }
                        // 已上架显示绿色
                        if ($record->is_show) {
                            return 'success';
                        }
                        // 已下架显示灰色
                        return 'gray';
                    })
                    ->formatStateUsing(function (House $record): string {
                        // 如果有预约时间且未上架，显示"预约发布"
                        if ($record->scheduled_publish_at && !$record->is_show) {
                            return '预约发布';
                        }
                        // 已上架
                        if ($record->is_show) {
                            return '已上架';
                        }
                        // 已下架
                        return '已下架';
                    })
                    ->description(function (House $record): ?string {
                        // 如果是预约发布，显示预约时间
                        if ($record->scheduled_publish_at && !$record->is_show) {
                            return '预约: ' . \Carbon\Carbon::parse($record->scheduled_publish_at)->format('Y-m-d H:i');
                        }
                        return null;
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
            ])
            ->recordUrl(null)
            ->recordAction(null)
            ->filters([
                // 第一排：小区、栋数、房号、上下架状态
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
                SelectFilter::make('is_show')
                    ->label('房源上/下架状态')
                    ->native(false)
                    ->options([
                        1 => '上架中',
                        0 => '已下架',
                    ]),
                Tables\Filters\Filter::make('created_at')
                    ->label('创建时间')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('开始日期')
                            ->placeholder('开始日期'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('结束日期')
                            ->placeholder('结束日期'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators[] = '创建时间从: ' . \Carbon\Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators[] = '创建时间至: ' . \Carbon\Carbon::parse($data['created_until'])->toFormattedDateString();
                        }
                        return $indicators;
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
                    ->color('info')
                    ->icon('heroicon-o-arrow-path')
                    ->visible(fn(House $record) => $record->is_show)
                    ->requiresConfirmation()
                    ->modalHeading('确认更新时间')
                    ->modalDescription('更新后房源的创建时间将变为当前时间，会排到列表最前面。')
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
                            ->body('房源时间已更新，现在会显示在列表最前面。')
                    ),
                Tables\Actions\Action::make('上架')
                    ->color('success')
                    ->icon('heroicon-o-arrow-up')
                    ->visible(fn(House $record) => !$record->is_show)
                    ->modalHeading('房源上架')
                    ->modalDescription('请选择上架方式')
                    ->form([
                        Forms\Components\Radio::make('publish_type')
                            ->label('上架方式')
                            ->options([
                                'immediate' => '立即上架',
                                'scheduled' => '定时上架',
                            ])
                            ->default('immediate')
                            ->required()
                            ->live()
                            ->inline(),
                        Forms\Components\DateTimePicker::make('scheduled_publish_at')
                            ->label('预约上架时间')
                            ->native(false)
                            ->displayFormat('Y-m-d H:i')
                            ->minDate(now())
                            ->helperText('系统将在每天凌晨2点自动上架到期的房源')
                            ->visible(fn (Forms\Get $get) => $get('publish_type') === 'scheduled')
                            ->required(fn (Forms\Get $get) => $get('publish_type') === 'scheduled'),
                    ])
                    ->action(function (House $record, array $data) {
                        if ($data['publish_type'] === 'immediate') {
                            // 立即上架
                            $record->is_show = true;
                            $record->created_at = now();
                            $record->scheduled_publish_at = null; // 清除可能存在的定时上架时间
                            $record->save();
                            
                            // 将该房源的所有跟进记录标记为已处理，避免出现在跟进记录列表中
                            // 无论之前是否已处理，都重新标记为已处理，确保不会再次出现
                            \App\Models\HouseFollowUp::where('house_id', $record->id)
                                ->where('result', '已出租')
                                ->update(['is_processed' => true]);
                            
                            // 记录操作日志
                            HouseOperationLog::create([
                                'house_id' => $record->id,
                                'operator_id' => auth()->id(),
                                'operator_type' => 'admin',
                                'operation_type' => 'online',
                            ]);
                            
                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('上架成功')
                                ->body('房源已立即上架')
                                ->send();
                        } else {
                            // 定时上架
                            $record->scheduled_publish_at = $data['scheduled_publish_at'];
                            $record->is_show = false; // 确保当前未上架
                            $record->save();
                            
                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('定时上架设置成功')
                                ->body('房源将在 ' . \Carbon\Carbon::parse($data['scheduled_publish_at'])->format('Y-m-d H:i') . ' 自动上架')
                                ->send();
                        }
                    }),
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
                Tables\Actions\Action::make('通过')
                    ->color('success')
                    ->visible(fn(House $record) => (int)$record->audit_status === 0)
                    ->requiresConfirmation()
                    ->action(function (House $record) {
                        $record->audit_status = 1;
                        $record->save();
                        
                        // 记录首次发布日志（如果之前没有发布记录）
                        $hasPublishLog = HouseOperationLog::where('house_id', $record->id)
                            ->where('operation_type', 'publish')
                            ->exists();
                        if (!$hasPublishLog) {
                            HouseOperationLog::create([
                                'house_id' => $record->id,
                                'operator_id' => auth()->id(),
                                'operator_type' => 'admin',
                                'operation_type' => 'publish',
                            ]);
                        }
                        
                        // 发送审核通过通知
                        if ($record->user) {
                            (new NotificationService())->notifyHouseAuditPassed($record->user, $record);
                        }
                    }),
                Tables\Actions\Action::make('驳回')
                    ->color('danger')
                    ->visible(fn(House $record) => (int)$record->audit_status === 0)
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('驳回原因')
                            ->placeholder('请输入驳回原因，将发送通知给用户')
                            ->required()
                            ->maxLength(500),
                    ])
                    ->modalHeading('驳回房源')
                    ->modalDescription('请填写驳回原因，用户将收到违规通知')
                    ->action(function (House $record, array $data) {
                        $record->audit_status = 2;
                        $record->save();
                        
                        // 发送违规通知
                        if ($record->user) {
                            (new \App\Services\NotificationService())->notifyViolationWarning(
                                $record->user,
                                '房源',
                                $record->title ?? '未命名房源',
                                $data['reason']
                            );
                        }
                    }),
                Tables\Actions\EditAction::make()
                    ->modalWidth('7xl')
                    ->modalHeading('编辑房源')
                    ->slideOver(false),
                Tables\Actions\DeleteAction::make(),
            ]);
//            ->bulkActions([
//                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
//                ]),
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageHouses::route('/'),
            'phone' => Pages\PhoneHouses::route('/phone'),
            // 'logs' => Pages\HouseFollowUpAndOperationLogs::route('/{record}/logs'), // 已改为弹窗形式
        ];
    }

    /**
     * 获取表单 Schema（供其他页面复用）
     * @return array
     */
    public static function getFormSchema(): array
    {
        return [
                Forms\Components\Section::make('房源推广')
                    ->description('将房源置顶显示，在小程序首页优先展示')
            ->schema([
                        Forms\Components\Toggle::make('is_top')
                            ->label('置顶显示')
                            ->inline(false)
                            ->helperText('开启后房源将在小程序首页置顶显示，并显示"推荐"标签')
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    // 开启置顶时，设置置顶时间为当前时间
                                    $set('top_at', now());
                                } else {
                                    // 关闭置顶时，清空置顶时间和到期时间
                                    $set('top_at', null);
                                    $set('top_expires_at', null);
                                }
                            }),
                        Forms\Components\DateTimePicker::make('top_at')
                            ->label('置顶时间')
                            ->helperText('置顶时间越晚，排序越靠前')
                            ->native(false)
                            ->displayFormat('Y-m-d H:i')
                            ->visible(fn (Forms\Get $get) => $get('is_top')),
                        Forms\Components\DatePicker::make('top_expires_at')
                            ->label('到期日期')
                            ->native(false)
                            ->displayFormat('Y-m-d')
                            ->minDate(now())
                            ->helperText('到期后系统将在凌晨3点自动取消置顶。留空表示永久置顶')
                            ->visible(fn (Forms\Get $get) => $get('is_top')),
                    ])
                    ->columns(2)
                    ->collapsible(),
                Tabs::make('Tabs')
                    ->columns()
                    ->columnSpanFull()
                    ->tabs([
                        Tabs\Tab::make('Tab 1')
                            ->label('基础信息')
                            ->schema([
                                Forms\Components\TextInput::make('contact_name')
                                    ->label('联系人')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('contact_phone')
                                    ->label('联系人电话')
                                    ->tel()
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('backup_contact_name')
                                    ->label('备用联系人')
                                    ->maxLength(255)
                                    ->helperText('可选，提供第二联系人'),
                                Forms\Components\TextInput::make('backup_contact_phone')
                                    ->label('备用联系电话')
                                    ->tel()
                                    ->maxLength(255),
                                Forms\Components\Select::make('type')
                                    ->label('类型')
                                    ->required()
                                    ->native(false)
                                    ->options([
                                        0 => '整租',
                                        1 => '合租',
                                        2 => '转租'
                                    ])
                                    ->default(0),
                                Forms\Components\Select::make('status')
                                    ->label('状态')
                                    ->required()
                                    ->native(false)
                                    ->options([
                                        0 => '空置',
                                        1 => '在租'
                                    ])
                                    ->default(0),
                                Forms\Components\Select::make('room_count')
                                    ->label('室')
                                    ->native(false)
                                    ->required()
                                    ->options([
                                        1 => '1室',
                                        2 => '2室',
                                        3 => '3室',
                                        4 => '4室',
                                    ])
                                    ->default(1),
                                Forms\Components\Select::make('living_room_count')
                                    ->label('厅')
                                    ->required()
                                    ->native(false)
                                    ->options([
                                        0 => '0厅',
                                        1 => '1厅',
                                        2 => '2厅',
                                    ])
                                    ->default(0),
                                Forms\Components\Select::make('bathroom_count')
                                    ->label('卫')
                                    ->native(false)
                                    ->required()
                                    ->options([
                                        0 => '0卫',
                                        1 => '1卫',
                                        2 => '2卫',
                                    ])
                                    ->default(1),
                                Forms\Components\TextInput::make('area')
                                    ->label('面积')
                                    ->required()
                                    ->numeric()
                                    ->default(0.00),
                                Forms\Components\TextInput::make('floor')
                                    ->label('楼层')
                                    ->required()
                                    ->numeric()
                                    ->default(0),
                                Forms\Components\TextInput::make('building_number')
                                    ->label('栋数')
                                    ->required()
                                    ->default(0),
                                Forms\Components\TextInput::make('unit')
                                    ->label('单元')
                                    ->required()
                                    ->default(0),
                                Forms\Components\TextInput::make('room_number')
                                    ->label('房间号')
                                    ->required()
                                    ->default(0),
                                Forms\Components\TextInput::make('orientation')
                                    ->label('朝向')
                                    ->required(),
                                Forms\Components\TextInput::make('renovation')
                                    ->label('装修')
                                    ->required(),
                                Forms\Components\TextInput::make('rent_price')
                                    ->label('租金')
                                    ->required()
                                    ->numeric()
                                    ->default(0.00),
                                Forms\Components\Select::make('payment_method')
                                    ->label('付款方式')
                                    ->native(false)
                                    ->options([
                                        '月付',
                                        '季付',
                                        '半年付',
                                        '年付'
                                    ])
                                    ->required(),
                                Forms\Components\Select::make('min_rental_period')
                                    ->label('起租时长')
                                    ->required()
                                    ->native(false)
                                    ->options([
                                        '年起租',
                                        '短租',
                                        '月起租',
                                        '季起租',
                                        '半年租'
                                    ])
                                    ->default(0),
                                Forms\Components\Select::make('community_id')
                                    ->label('小区')
                                    ->options(Community::all()->pluck('name', 'id'))
                                    ->native(false)
                                    ->searchable(),
                                Forms\Components\TextInput::make('address')
                                    ->label('详细地址')
                                    ->columnSpanFull()
                                    ->maxLength(255),
                                ToggleButtons::make('viewing_method')
                                    ->label('看房方式')
                                    ->inline()
                                    ->columnSpanFull()
                                    ->options([
                                        1 => '提前预约',
                                        2 => '密码',
                                        3 => '门口钥匙',
                                        4 => '物业钥匙'
                                    ]),
                                ToggleButtons::make('facility_ids')
                                    ->label('配套设置')
                                    ->columnSpanFull()
                                    ->multiple()
                                    ->inline()
                                    ->options(Facility::query()->whereJsonContains('type', 0)->pluck('name', 'id')),
                            ]),
                        Tabs\Tab::make('Tab 2')
                            ->label('图片视频')
                            ->schema([
                                Forms\Components\FileUpload::make('video')
                                    ->label('视频')
                                    ->columnSpanFull()
                                    ->acceptedFileTypes(['video/mp4', 'video/avi', 'video/mov', 'video/wmv'])
                                    ->maxSize(102400) // 100MB
                                    ->downloadable()
                                    ->openable(),
                                Forms\Components\FileUpload::make('cover_image')
                                    ->label('封面图')
                                    ->columnSpanFull()
                                    ->image()
                                    ->imageEditor()
                                    ->imagePreviewHeight('500')
                                    ->imageCropAspectRatio(null)
                                    ->imageResizeTargetWidth(null)
                                    ->imageResizeTargetHeight(null)
                                    ->downloadable()
                                    ->openable(),
                                Forms\Components\FileUpload::make('images')
                                    ->label('图片')
                                    ->columnSpanFull()
                                    ->multiple()
                                    ->image()
                                    ->imageEditor()
                                    ->imagePreviewHeight('400')
                                    ->imageCropAspectRatio(null)
                                    ->imageResizeTargetWidth(null)
                                    ->imageResizeTargetHeight(null)
                                    ->panelLayout('grid')
                                    ->reorderable()
                                    ->downloadable()
                                    ->openable(),
                            ]),
                    ]),
        ];
    }
}
