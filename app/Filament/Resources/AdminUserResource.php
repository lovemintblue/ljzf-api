<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdminUserResource\Pages;
use App\Filament\Resources\AdminUserResource\RelationManagers;
use App\Models\AdminUser;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class AdminUserResource extends Resource
{
    protected static ?string $model = AdminUser::class;

    protected static ?string $navigationIcon = 'heroicon-m-squares-2x2';

    protected static ?string $navigationGroup = '系统';

    protected static ?string $navigationLabel = '账号';

    protected static ?string $label = '账号';

    /**
     * @param Form $form
     * @return Form
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\CheckboxList::make('roles')
                    ->relationship('roles', 'name')
                    ->label('角色')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('name')
                    ->label('名称')
                    ->required()
                    ->columnSpanFull()
                    ->maxLength(255),
                Forms\Components\TextInput::make('username')
                    ->label('用户名')
                    ->columnSpanFull()
                    ->required()
                    ->maxLength(255),
                TextInput::make('password')
                    ->label('密码')
                    ->columnSpanFull()
                    ->helperText('不修改默认为空')
                    ->placeholder('请输入登录密码')
                    ->dehydrated(fn($state): bool => filled($state))
                    ->dehydrateStateUsing(fn($state): string => Hash::make($state))
                    ->live(debounce: 500),
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
                Tables\Columns\TextColumn::make('name')
                    ->label('名称')
                    ->searchable(),
                Tables\Columns\TextColumn::make('username')
                    ->label('用户名')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime('Y-m-d H:i:s')
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
            'index' => Pages\ManageAdminUsers::route('/'),
        ];
    }
}
