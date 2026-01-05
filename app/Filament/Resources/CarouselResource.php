<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CarouselResource\Pages;
use App\Filament\Resources\CarouselResource\RelationManagers;
use App\Models\Carousel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CarouselResource extends Resource
{
    protected static ?string $model = Carousel::class;

    protected static ?string $navigationIcon = 'heroicon-m-squares-2x2';

    protected static ?string $navigationLabel = '轮播广告';

    protected static ?string $label = '轮播广告';

    /**
     * @param Form $form
     * @return Form
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('image')
                    ->label('图片')
                    ->columnSpanFull()
                    ->image()
                    ->required(),
                Forms\Components\TextInput::make('url')
                    ->label('跳转链接')
                    ->columnSpanFull()
                    ->placeholder('例如：/pages/house-detail/index?id=123')
                    ->helperText('支持小程序内页面路径，如：/pages/house-detail/index?id=123，不填写则不跳转')
                    ->maxLength(255),
                Forms\Components\TextInput::make('sort')
                    ->label('排序')
                    ->columnSpanFull()
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Radio::make('status')
                    ->label('状态')
                    ->inline()
                    ->columnSpanFull()
                    ->required()
                    ->options([
                        0 => '禁用',
                        1 => '启用'
                    ])
                    ->default(1),
            ]);
    }

    /**
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('图片')
                    ->width(120)
                    ->height(80),
                Tables\Columns\TextColumn::make('url')
                    ->label('跳转链接')
                    ->searchable()
                    ->limit(40)
                    ->placeholder('无')
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (!$state || strlen($state) <= 40) {
                            return null;
                        }
                        return $state;
                    }),
                Tables\Columns\TextColumn::make('sort')
                    ->label('排序')
                    ->numeric()
                    ->badge()
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('status')
                    ->label('状态')
            ])
            ->recordUrl(null)
            ->recordAction(null)
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
            'index' => Pages\ManageCarousels::route('/'),
        ];
    }
}
