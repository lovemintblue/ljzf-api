<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BusinessDistrictResource\Pages;
use App\Filament\Resources\BusinessDistrictResource\RelationManagers;
use App\Models\BusinessDistrict;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BusinessDistrictResource extends Resource
{
    protected static ?string $model = BusinessDistrict::class;

    protected static ?string $navigationIcon = 'heroicon-m-squares-2x2';
    
    protected static ?string $navigationLabel = '商圈';

    protected static ?string $label = '商圈';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('商圈')
                    ->columnSpanFull()
                    ->required()
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
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('商圈名称')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
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
            'index' => Pages\ManageBusinessDistricts::route('/'),
        ];
    }
}
