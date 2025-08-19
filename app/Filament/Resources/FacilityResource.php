<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FacilityResource\Pages;
use App\Filament\Resources\FacilityResource\RelationManagers;
use App\Models\Facility;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FacilityResource extends Resource
{
    protected static ?string $model = Facility::class;

    protected static ?string $navigationIcon = 'heroicon-m-squares-2x2';

    protected static ?string $navigationLabel = '配套';

    protected static ?string $label = '配套';

    /**
     * @param Form $form
     * @return Form
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('icon')
                    ->columnSpanFull()
                    ->image()
                    ->label('图标'),
                Forms\Components\FileUpload::make('selected_icon')
                    ->columnSpanFull()
                    ->image()
                    ->label('选中图标'),
                Forms\Components\TextInput::make('name')
                    ->label('名称')
                    ->columnSpanFull()
                    ->required(),
                Forms\Components\CheckboxList::make('type')
                    ->label('类型')
                    ->options([
                        0 => '房源',
                        1 => '商铺'
                    ])->default(0)
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
                Tables\Columns\ImageColumn::make('icon')
                    ->label('图标'),
                Tables\Columns\ImageColumn::make('selected_icon')
                    ->label('选中图标'),
                Tables\Columns\TextColumn::make('name')
                    ->label('名称')
                    ->searchable(),
                ViewColumn::make('type')
                    ->label('类型')
                    ->view('tables.columns.facility-type')
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
            'index' => Pages\ManageFacilities::route('/'),
        ];
    }
}
