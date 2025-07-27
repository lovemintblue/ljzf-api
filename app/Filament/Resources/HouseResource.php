<?php
/**
 * 管理后台 - 房源
 */

namespace App\Filament\Resources;

use App\Filament\Resources\HouseResource\Pages;
use App\Filament\Resources\HouseResource\RelationManagers;
use App\Models\House;
use App\Models\Shop;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
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
        return $form
            ->columns(4)
            ->schema([
                Forms\Components\FileUpload::make('video')
                    ->label('视频')->columnSpan(2),
                Forms\Components\FileUpload::make('cover_image')
                    ->label('封面图')->columnSpan(2),
                Forms\Components\FileUpload::make('images')
                    ->label('图片')
                    ->columnSpanFull()
                    ->multiple()
                    ->panelLayout('grid'),
                Forms\Components\TextInput::make('title')
                    ->label('标题')
                    ->columnSpanFull()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('contact_name')
                    ->label('联系人')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('contact_phone')
                    ->label('联系人电话')
                    ->tel()
                    ->required()
                    ->maxLength(255),
                Forms\Components\Radio::make('type')
                    ->label('类型')
                    ->required()
                    ->inline()
                    ->inlineLabel(false)
                    ->options([
                        0 => '整租',
                        1 => '合租'
                    ])
                    ->default(0),
                Forms\Components\Radio::make('status')
                    ->label('状态')
                    ->required()
                    ->inline()
                    ->inlineLabel(false)
                    ->options([
                        0 => '空置',
                        1 => '在租',
                        2 => '转租'
                    ])
                    ->default(0),
                Forms\Components\TextInput::make('room_count')
                    ->label('室')
                    ->required()
                    ->numeric()
                    ->default(1),
                Forms\Components\TextInput::make('living_room_count')
                    ->label('厅')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('bathroom_count')
                    ->label('卫')
                    ->required()
                    ->numeric()
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
                Forms\Components\TextInput::make('total_floors')
                    ->label('总楼层')
                    ->required()
                    ->numeric()
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
                Forms\Components\TextInput::make('payment_method')
                    ->label('付款方式')
                    ->required(),
                Forms\Components\TextInput::make('min_rental_period')
                    ->label('起租时长')
                    ->required()
                    ->maxLength(255)
                    ->default(0),
                Forms\Components\TextInput::make('community.name')
                    ->label('小区')
                    ->maxLength(255),
                Forms\Components\TextInput::make('address')
                    ->label('详细地址')
                    ->columnSpanFull()
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
                    ->label('发布人'),
                Tables\Columns\TextColumn::make('title')
                    ->label('标题')
                    ->searchable(),
                Tables\Columns\TextColumn::make('contact_name')
                    ->label('联系人')
                    ->searchable(),
                Tables\Columns\TextColumn::make('contact_phone')
                    ->label('联系电话')
                    ->searchable(),
                Tables\Columns\TextColumn::make('renovation')
                    ->label('装修')
                    ->searchable(),
                Tables\Columns\TextColumn::make('community.name')
                    ->label('小区')
                    ->searchable(),
                Tables\Columns\ToggleColumn::make('is_show')
                    ->label('是否显示'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime('Y-m-d H:i:s')
            ])
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
                    }),
                Tables\Actions\Action::make('驳回')
                    ->color('danger')
                    ->visible(fn(House $record) => (int)$record->audit_status === 0)
                    ->requiresConfirmation()
                    ->action(function (House $record) {
                        $record->audit_status = 2;
                        $record->save();
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
            'index' => Pages\ManageHouses::route('/'),
        ];
    }
}
