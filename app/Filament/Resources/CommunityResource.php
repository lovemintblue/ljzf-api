<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommunityResource\Pages;
use App\Filament\Resources\CommunityResource\RelationManagers;
use App\Forms\Components\Map;
use App\Models\BusinessDistrict;
use App\Models\Community;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CommunityResource extends Resource
{
    protected static ?string $model = Community::class;

    protected static ?string $navigationIcon = 'heroicon-m-squares-2x2';

    protected static ?string $navigationLabel = '小区';

    protected static ?string $label = '小区';

    /**
     * @param Form $form
     * @return Form
     */
    public static function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                Forms\Components\FileUpload::make('image')
                    ->label('图片')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('name')
                    ->label('名称')
                    ->required()
                    ->columnSpanFull()
                    ->maxLength(255),
                Forms\Components\TextInput::make('province')
                    ->label('省份')
                    ->maxLength(255),
                Forms\Components\TextInput::make('city')
                    ->label('城市')
                    ->maxLength(255),
                Forms\Components\TextInput::make('district')
                    ->label('区县')
                    ->maxLength(255),
                Forms\Components\TextInput::make('built_year')
                    ->label('建成年代'),
                Forms\Components\TextInput::make('property_fee')
                    ->label('物业费(元/月/㎡)'),
                Forms\Components\TextInput::make('property_company')
                    ->label('物业公司'),
                Forms\Components\TextInput::make('developer')
                    ->label('开发商'),
                Forms\Components\TextInput::make('building_count')
                    ->label('楼栋总数'),
                Forms\Components\TextInput::make('house_count')
                    ->label('房屋总数'),
                Forms\Components\TextInput::make('average_rent_price')
                    ->label('租金均价(元/月)'),
                Forms\Components\TextInput::make('average_sale_price')
                    ->label('售价均价(元/㎡)'),
                Forms\Components\Textarea::make('address')
                    ->label('详细地址')
                    ->autosize()
                    ->columnSpanFull()
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('business_district_id')
                    ->label('关联商圈')
                    ->columnSpanFull()
                    ->options(BusinessDistrict::query()->pluck('name', 'id')),
                Map::make('map')
                    ->label('选择地址')
            ]);
    }

    /**
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('图片'),
                Tables\Columns\TextColumn::make('businessDistrict.name')
                    ->label('商圈')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('名称')
                    ->searchable(),
                Tables\Columns\TextColumn::make('province')
                    ->label('省份')
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->label('城市')
                    ->searchable(),
                Tables\Columns\TextColumn::make('district')
                    ->label('区县')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->label('详细地址')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('Y-m-d H:i:s')
                    ->label('创建时间'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * @return array|PageRegistration[]
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCommunities::route('/'),
        ];
    }
}
