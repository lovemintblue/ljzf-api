<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserLevelResource\Pages;
use App\Filament\Resources\UserLevelResource\RelationManagers;
use App\Models\UserLevel;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserLevelResource extends Resource
{
    protected static ?string $model = UserLevel::class;

    protected static ?string $navigationIcon = 'heroicon-m-squares-2x2';

    protected static ?string $navigationGroup = '用户';

    protected static ?string $navigationLabel = '用户等级';

    protected static ?string $label = '用户等级';

    protected static ?int $navigationSort = 2;

    /**
     * @param Form $form
     * @return Form
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('名称')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('level')
                    ->label('等级')
                    ->required()
                    ->helperText('数字类型，数字越大等级越高')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_recommend')
                    ->required()
                    ->label('推荐')
                    ->default(0),
                Forms\Components\Toggle::make('is_good_value')
                    ->required()
                    ->label('超值')
                    ->default(0),
                Forms\Components\Select::make('privilege')
                    ->label('特权')
                    ->native(false)
                    ->columnSpanFull()
                    ->multiple()
                    ->options(UserLevel::$privilegeMap),
                Forms\Components\TextInput::make('view_phone_count')
                    ->label('查看电话次数')
                    ->numeric()
                    ->default(0),
                TableRepeater::make('userLevelPrices')
                    ->label('价格设置')
                    ->emptyLabel('未设置价格')
                    ->headers([
                        Header::make('时长')->width('150px'),
                        Header::make('价格')->width('150px'),
                    ])
                    ->schema([
                        Forms\Components\Select::make('cycle')
                            ->options([
                                0 => '月',
                                1 => '季',
                                2 => '年'
                            ])
                            ->native(false)
                            ->default(0),
                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->default(0)
                    ])
                    ->columnSpan('full')
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
                Tables\Columns\TextColumn::make('name')
                    ->label('名称')
                    ->searchable(),
                Tables\Columns\TextColumn::make('level')
                    ->label('等级')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_recommend')
                    ->label('推荐'),
                Tables\Columns\ToggleColumn::make('is_good_value')
                    ->label('超值'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageUserLevels::route('/'),
        ];
    }
}
