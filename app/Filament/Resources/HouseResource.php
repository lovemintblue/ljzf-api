<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HouseResource\Pages;
use App\Filament\Resources\HouseResource\RelationManagers;
use App\Models\House;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('contact_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('contact_phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('room_count')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('living_room_count')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bathroom_count')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('area')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('floor')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_floors')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('orientation'),
                Tables\Columns\TextColumn::make('renovation'),
                Tables\Columns\TextColumn::make('rent_price')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method'),
                Tables\Columns\TextColumn::make('min_rental_period')
                    ->searchable(),
                Tables\Columns\TextColumn::make('community')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ManageHouses::route('/'),
        ];
    }
}
