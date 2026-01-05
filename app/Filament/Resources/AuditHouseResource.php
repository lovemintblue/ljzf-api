<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditHouseResource\Pages;
use App\Filament\Resources\AuditHouseResource\RelationManagers;
use App\Models\AuditHouse;
use App\Models\House;
use App\Models\HouseOperationLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AuditHouseResource extends Resource
{
    protected static ?string $model = AuditHouse::class;

    protected static ?string $navigationIcon = 'heroicon-m-squares-2x2';

    protected static ?string $navigationGroup = '房源';

    protected static ?string $navigationLabel = '审核列表';

    protected static ?string $label = '审核列表';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        return House::where('audit_status', 0)
            ->where('is_draft', 0)
            ->where('is_delegated', 0)
            ->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = House::where('audit_status', 0)
            ->where('is_draft', 0)
            ->where('is_delegated', 0)
            ->count();
        if ($count > 20) {
            return 'danger';
        } elseif ($count > 10) {
            return 'warning';
        }
        return 'success';
    }

    /**
     * @param Form $form
     * @return Form
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'nickname')
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('contact_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('contact_phone')
                    ->tel()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('type')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('room_count')
                    ->required()
                    ->numeric()
                    ->default(1),
                Forms\Components\TextInput::make('living_room_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('bathroom_count')
                    ->required()
                    ->numeric()
                    ->default(1),
                Forms\Components\TextInput::make('area')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('floor')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('total_floors')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('orientation')
                    ->required(),
                Forms\Components\TextInput::make('renovation')
                    ->required(),
                Forms\Components\TextInput::make('rent_price')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('payment_method')
                    ->required(),
                Forms\Components\TextInput::make('min_rental_period')
                    ->required()
                    ->maxLength(255)
                    ->default(0),
                Forms\Components\TextInput::make('images'),
                Forms\Components\TextInput::make('community')
                    ->maxLength(255),
                Forms\Components\TextInput::make('address')
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
            ->defaultSort('created_at', 'desc')
            ->query(function (House $query) {
                return $query->where('audit_status', 0)->where('is_draft', 0)->where('is_delegated', 0);
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
                    ->searchable(),
                Tables\Columns\TextColumn::make('contact_phone')
                    ->label('联系电话')
                    ->searchable(),
                ViewColumn::make('audit_status')
                    ->label('审核状态')
                    ->view('tables.columns.audit-status'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime('Y-m-d H:i:s')
            ])
            ->recordUrl(null)
            ->recordAction(null)
            ->filters([
                //
            ])
            ->actions([
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
                            (new \App\Services\NotificationService())->notifyHouseAuditPassed($record->user, $record);
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
                        $record->is_draft = 1;
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
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
            ]);
    }

    /**
     * 详情
     * @param Infolist $infolist
     * @return Infolist
     */
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->columns(4)
            ->schema([
                ImageEntry::make('cover_image')->label('封面图'),
                TextEntry::make('no')->label('房源编号'),
                TextEntry::make('user.nickname')->label('发布人'),
                TextEntry::make('title')->label('标题'),
                TextEntry::make('contact_name')->label('联系人'),
                TextEntry::make('contact_phone')->label('联系电话'),
                TextEntry::make('renovation')->label('装修'),
                TextEntry::make('community.name')->label('小区'),
                TextEntry::make('type')->label('类型'),
                TextEntry::make('room_count')->label('室'),
                TextEntry::make('living_room_count')->label('厅'),
                TextEntry::make('bathroom_count')->label('卫'),
                TextEntry::make('area')->label('面积'),
                TextEntry::make('floor')->label('楼层'),
                TextEntry::make('total_floors')->label('总楼层'),
                TextEntry::make('orientation')->label('朝向'),
                TextEntry::make('rent_price')->label('租金'),
                TextEntry::make('payment_method')->label('付款方式'),
                TextEntry::make('min_rental_period')->label('起租时长'),
                TextEntry::make('building_number')->label('栋数'),
                TextEntry::make('room_number')->label('房间号'),
                TextEntry::make('province')->label('省份'),
                TextEntry::make('city')->label('城市'),
                TextEntry::make('district')->label('区县'),
                TextEntry::make('address')->label('详细地址')->columnSpanFull(),
            ]);
    }

    /**
     * @return array|PageRegistration[]
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageAuditHouses::route('/'),
        ];
    }
}
