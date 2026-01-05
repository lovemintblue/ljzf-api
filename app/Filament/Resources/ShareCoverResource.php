<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShareCoverResource\Pages;
use App\Models\ShareCover;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShareCoverResource extends Resource
{
    protected static ?string $model = ShareCover::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = '分享封面';

    protected static ?string $modelLabel = '分享封面';

    protected static ?string $pluralModelLabel = '分享封面';

    protected static ?string $navigationGroup = '商圈';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('image')
                    ->label('封面图片')
                    ->image()
                    ->required()
                    ->maxSize(5120)
                    ->imageEditor()
                    ->imageEditorAspectRatios([
                        '16:9',
                        '4:3',
                        '1:1',
                    ])
                    ->directory('share-covers')
                    ->columnSpanFull()
                    ->helperText('建议尺寸：750x500px，支持JPG、PNG格式，最大5MB'),

                Forms\Components\TextInput::make('sort')
                    ->label('排序')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->helperText('数字越小越靠前'),

                Forms\Components\Toggle::make('is_active')
                    ->label('启用状态')
                    ->default(true)
                    ->required()
                    ->helperText('只有启用的封面图才会被随机使用'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('封面图片')
                    ->height(80)
                    ->width(120),

                Tables\Columns\TextColumn::make('sort')
                    ->label('排序')
                    ->sortable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('状态')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('更新时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort', 'asc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('启用状态')
                    ->placeholder('全部')
                    ->trueLabel('已启用')
                    ->falseLabel('已禁用'),
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
            'index' => Pages\ListShareCovers::route('/'),
            'create' => Pages\CreateShareCover::route('/create'),
            'edit' => Pages\EditShareCover::route('/{record}/edit'),
        ];
    }
}

