<?php

namespace App\Filament\Resources\HouseResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HouseFollowUpsRelationManager extends RelationManager
{
    protected static string $relationship = 'houseFollowUps';

    protected static ?string $title = '跟进记录';

    /**
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('result')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    /**
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('result')
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('user.avatar')
                    ->label('头像'),
                Tables\Columns\TextColumn::make('user.nickname')
                    ->label('用户')
                    ->searchable(),
                Tables\Columns\TextColumn::make('house.title')
                    ->label('跟进房源')
                    ->searchable(),
                Tables\Columns\TextColumn::make('result')
                    ->label('跟进结果'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('跟进时间')
                    ->dateTime('Y-m-d H:i:s')
            ])
            ->filters([
                //
            ])
            ->headerActions([
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
