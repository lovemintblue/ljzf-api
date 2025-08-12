<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HouseFollowUpResource\Pages;
use App\Filament\Resources\HouseFollowUpResource\RelationManagers;
use App\Models\HouseFollowUp;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HouseFollowUpResource extends Resource
{
    protected static ?string $model = HouseFollowUp::class;

    protected static ?string $navigationIcon = 'heroicon-m-squares-2x2';

    protected static ?string $navigationGroup = '房源';

    protected static ?string $navigationLabel = '跟进记录';

    protected static ?string $label = '跟进记录';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'id')
                    ->required(),
                Forms\Components\Select::make('house_id')
                    ->relationship('house', 'title')
                    ->required(),
                Forms\Components\TextInput::make('result')
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
            ->actions([
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('下架房源')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (HouseFollowUp $record) {
                        $record->house->update(['is_show' => 1]);
                        Notification::make()->title('下架成功')->success()->send();
                    })
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
            'index' => Pages\ManageHouseFollowUps::route('/'),
        ];
    }
}
