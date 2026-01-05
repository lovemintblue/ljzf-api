<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShopResource\Pages;
use App\Filament\Resources\ShopResource\RelationManagers;
use App\Models\Community;
use App\Models\Facility;
use App\Models\Industry;
use App\Models\Shop;
use App\Services\NotificationService;
use Filament\Forms;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ShopResource extends Resource
{
    protected static ?string $model = Shop::class;

    protected static ?string $navigationIcon = 'heroicon-m-squares-2x2';

    protected static ?string $navigationGroup = '商铺';

    protected static ?string $navigationLabel = '商铺列表';

    protected static ?string $label = '商铺';

    protected static ?int $navigationSort = 1;

    /**
     * @param Form $form
     * @return Form
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->columns()
                    ->columnSpanFull()
                    ->tabs([
                        Tabs\Tab::make('基础信息')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('商铺标题')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('例如：市中心旺铺出租'),
                                Forms\Components\Select::make('type')
                                    ->label('商铺类型')
                                    ->required()
                                    ->native(false)
                                    ->options([
                                        0 => '社区底商',
                                        1 => '购物百货中心',
                                        2 => '商业街店铺',
                                        3 => '临街门面',
                                        4 => '其他',
                                        5 => '写字楼配套',
                                        6 => '档口摊位'
                                    ])
                                    ->default(0),
                                Forms\Components\Select::make('rental_type')
                                    ->label('租赁类型')
                                    ->required()
                                    ->native(false)
                                    ->options([
                                        0 => '直租',
                                        1 => '转让'
                                    ])
                                    ->default(0),
                                Forms\Components\TextInput::make('contact_name')
                                    ->label('联系人')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('contact_phone')
                                    ->label('联系电话')
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
                                Forms\Components\TextInput::make('area')
                                    ->label('面积（平方米）')
                                    ->required()
                                    ->numeric()
                                    ->suffix('㎡')
                                    ->default(0),
                                Forms\Components\TextInput::make('floor')
                                    ->label('所在楼层')
                                    ->required()
                                    ->numeric()
                                    ->default(1),
                                Forms\Components\TextInput::make('total_floors')
                                    ->label('总楼层数')
                                    ->required()
                                    ->numeric()
                                    ->default(1),
                                Forms\Components\Select::make('renovation')
                                    ->label('装修情况')
                                    ->required()
                                    ->native(false)
                                    ->options([
                                        '毛坯房',
                                        '简装修',
                                        '精装修'
                                    ])
                                    ->default('精装修'),
                                Forms\Components\TextInput::make('rent_price')
                                    ->label('月租金')
                                    ->required()
                                    ->numeric()
                                    ->prefix('¥')
                                    ->suffix('元/月')
                                    ->default(0),
                                Forms\Components\TextInput::make('deposit_price')
                                    ->label('押金')
                                    ->required()
                                    ->numeric()
                                    ->prefix('¥')
                                    ->suffix('元')
                                    ->default(0),
                                Forms\Components\TextInput::make('property_fee')
                                    ->label('物业费')
                                    ->required()
                                    ->numeric()
                                    ->prefix('¥')
                                    ->suffix('元/月')
                                    ->default(0),
                                Forms\Components\Select::make('payment_method')
                                    ->label('付款方式')
                                    ->required()
                                    ->native(false)
                                    ->options([
                                        '月付',
                                        '季付',
                                        '半年付',
                                        '年付'
                                    ])
                                    ->default('月付'),
                                Forms\Components\Select::make('community_id')
                                    ->label('所属小区')
                                    ->options(Community::all()->pluck('name', 'id'))
                                    ->native(false)
                                    ->searchable()
                                    ->preload(),
                            ]),
                        Tabs\Tab::make('店铺尺寸')
                            ->schema([
                                Forms\Components\TextInput::make('floor_height')
                                    ->label('层高（米）')
                                    ->numeric()
                                    ->suffix('米')
                                    ->helperText('例如：3.5米，可做夹层'),
                                Forms\Components\TextInput::make('frontage')
                                    ->label('面宽（米）')
                                    ->numeric()
                                    ->suffix('米')
                                    ->helperText('临街或通道宽度'),
                                Forms\Components\TextInput::make('depth')
                                    ->label('进深（米）')
                                    ->numeric()
                                    ->suffix('米')
                                    ->helperText('店铺使用深度'),
                            ]),
                        Tabs\Tab::make('配套与描述')
                            ->schema([
                                ToggleButtons::make('facility_ids')
                                    ->label('配套设施')
                                    ->columnSpanFull()
                                    ->multiple()
                                    ->inline()
                                    ->options(Facility::query()->whereJsonContains('type', 1)->pluck('name', 'id')),
                                ToggleButtons::make('industry_ids')
                                    ->label('适合行业')
                                    ->columnSpanFull()
                                    ->multiple()
                                    ->inline()
                                    ->options(Industry::all()->pluck('name', 'id')),
                                ToggleButtons::make('suitable_businesses')
                                    ->label('适合经营')
                                    ->columnSpanFull()
                                    ->multiple()
                                    ->inline()
                                    ->options([
                                        0 => '餐饮美食',
                                        1 => '美容美发',
                                        2 => '公司办公',
                                        3 => '服饰鞋包',
                                        4 => '休闲娱乐',
                                        5 => '零售百货',
                                        6 => '生活服务',
                                        7 => '电器通讯',
                                        8 => '汽修美容',
                                        9 => '医疗器械',
                                        10 => '家居建材',
                                        11 => '教育培训',
                                        12 => '酒店宾馆',
                                    ]),
                                Forms\Components\Textarea::make('surroundings')
                                    ->label('周边环境')
                                    ->columnSpanFull()
                                    ->rows(3)
                                    ->placeholder('描述周边环境，如写字楼密集、人流量大等'),
                                Forms\Components\Textarea::make('description')
                                    ->label('商铺描述')
                                    ->columnSpanFull()
                                    ->rows(5)
                                    ->maxLength(5000)
                                    ->placeholder('详细描述商铺优势、适合行业等信息'),
                            ]),
                        Tabs\Tab::make('图片')
                            ->columns(4)
                            ->schema([
                                Forms\Components\FileUpload::make('cover_image')
                                    ->label('封面图')
                                    ->columnSpan(2)
                                    ->image()
                                    ->imageEditor()
                                    ->imagePreviewHeight('250')
                                    ->previewable()
                                    ->downloadable()
                                    ->openable(),
                                Forms\Components\FileUpload::make('images')
                                    ->label('商铺图片')
                                    ->columnSpanFull()
                                    ->multiple()
                                    ->image()
                                    ->imageEditor()
                                    ->imagePreviewHeight('200')
                                    ->panelLayout('grid')
                                    ->reorderable()
                                    ->previewable()
                                    ->downloadable()
                                    ->openable(),
                            ]),
                    ]),
            ]);
    }

    /**
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->query(function (Shop $query) {
                return $query->where('audit_status', 1);
            })
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->label('封面图'),
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('no')
                    ->label('编号')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.nickname')
                    ->label('发布人')
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('标题')
                    ->searchable(),
                Tables\Columns\TextColumn::make('contact_name')
                    ->label('联系人')
                    ->searchable()
                    ->description(fn (Shop $record) => $record->backup_contact_name ? "备用: {$record->backup_contact_name}" : null),
                Tables\Columns\TextColumn::make('contact_phone')
                    ->label('联系电话')
                    ->searchable(query: function ($query, $search) {
                        return $query->where(function ($q) use ($search) {
                            $q->where('contact_phone', 'like', "%{$search}%")
                              ->orWhere('backup_contact_phone', 'like', "%{$search}%");
                        });
                    })
                    ->description(fn (Shop $record) => $record->backup_contact_phone ? "备用: {$record->backup_contact_phone}" : null),
                Tables\Columns\TextColumn::make('is_show')
                    ->label('上架状态')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '1' => 'success',
                        '0' => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        '1' => '已上架',
                        '0' => '已下架',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
            ])
            ->recordUrl(null)
            ->recordAction(null)
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('更新时间')
                    ->color('info')
                    ->icon('heroicon-o-arrow-path')
                    ->visible(fn(Shop $record) => $record->is_show)
                    ->requiresConfirmation()
                    ->modalHeading('确认更新时间')
                    ->modalDescription('更新后商铺的创建时间将变为当前时间，会排到列表最前面。')
                    ->action(function (Shop $record) {
                        $record->created_at = now();
                        $record->save();
                    })
                    ->successNotification(
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('更新成功')
                            ->body('商铺时间已更新，现在会显示在列表最前面。')
                    ),
                Tables\Actions\Action::make('上架')
                    ->color('success')
                    ->icon('heroicon-o-arrow-up')
                    ->visible(fn(Shop $record) => !$record->is_show)
                    ->requiresConfirmation()
                    ->modalHeading('确认上架')
                    ->modalDescription('上架后商铺将对用户可见，同时会更新创建时间为当前时间。')
                    ->action(function (Shop $record) {
                        $record->is_show = true;
                        $record->created_at = now();
                        $record->save();
                    }),
                Tables\Actions\Action::make('下架')
                    ->color('warning')
                    ->icon('heroicon-o-arrow-down')
                    ->visible(fn(Shop $record) => $record->is_show)
                    ->requiresConfirmation()
                    ->modalHeading('确认下架')
                    ->modalDescription('下架后商铺将不对用户显示。')
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('下架原因')
                            ->required()
                            ->default('管理员下架')
                            ->rows(3),
                    ])
                    ->action(function (Shop $record, array $data) {
                        $record->is_show = false;
                        $record->save();
                        
                        // 发送下架通知
                        if ($record->user) {
                            (new NotificationService())->notifyShopOffline(
                                $record->user,
                                $record,
                                $data['reason'] ?? '管理员下架'
                            );
                        }
                    }),
                Tables\Actions\Action::make('通过')
                    ->color('success')
                    ->visible(fn(Shop $record) => (int)$record->audit_status === 0)
                    ->requiresConfirmation()
                    ->action(function (Shop $record) {
                        $record->audit_status = 1;
                        $record->save();
                        
                        // 发送审核通过通知
                        if ($record->user) {
                            (new NotificationService())->notifyShopAuditPassed($record->user, $record);
                        }
                    }),
                Tables\Actions\Action::make('驳回')
                    ->color('danger')
                    ->visible(fn(Shop $record) => (int)$record->audit_status === 0)
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('驳回原因')
                            ->placeholder('请输入驳回原因，将发送通知给用户')
                            ->required()
                            ->maxLength(500),
                    ])
                    ->modalHeading('驳回商铺')
                    ->modalDescription('请填写驳回原因，用户将收到违规通知')
                    ->action(function (Shop $record, array $data) {
                        $record->audit_status = 2;
                        $record->save();
                        
                        // 发送违规通知
                        if ($record->user) {
                            (new \App\Services\NotificationService())->notifyViolationWarning(
                                $record->user,
                                '商铺',
                                $record->title ?? '未命名商铺',
                                $data['reason']
                            );
                        }
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageShops::route('/'),
        ];
    }
}
