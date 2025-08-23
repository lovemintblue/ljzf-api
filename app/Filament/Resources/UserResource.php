<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use App\Models\UserLevel;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-m-squares-2x2';

    protected static ?string $navigationGroup = '用户';

    protected static ?string $navigationLabel = '用户列表';

    protected static ?string $label = '用户';

    protected static ?int $navigationSort = 1;

    /**
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->label('头像'),
                Tables\Columns\TextColumn::make('nickname')
                    ->label('用户昵称')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('手机号')
                    ->searchable(),
                Tables\Columns\TextColumn::make('userLevel.name')
                    ->label('VIP等级')
                    ->badge(),
                Tables\Columns\ToggleColumn::make('status')
                    ->label('状态'),
                Tables\Columns\ToggleColumn::make('is_staff')
                    ->label('是否为员工'),
                Tables\Columns\TextColumn::make('latest_visit_at')
                    ->label('上次访问时间')
                    ->since(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime('Y-m-d')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Action::make('设为会员')
                    ->form([
                        Select::make('user_level_id')
                            ->label('会员等级')
                            ->options(UserLevel::query()->pluck('name', 'id'))
                            ->native(false)
                            ->required(),
                        DatePicker::make('expired_at')
                            ->label('到期时间')
                            ->native(false)
                    ])
                    ->action(function (array $data, User $record): void {
                        $record->user_level_id = $data['user_level_id'];
                        $record->expired_at = $data['expired_at'];
                        $record->save();
                        Notification::make()->title('会员设置成功.')->success()->send();
                    })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * @param Form $form
     * @return Form
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('avatar')
                    ->label('头像')
                    ->image()
                    ->avatar()
                    ->columnSpanFull(),
                Forms\Components\Select::make('user_level_id')
                    ->label('VIP等级')
                    ->native(false)
                    ->columnSpanFull()
                    ->options(UserLevel::query()->pluck('name', 'id')->prepend([
                        0 => '无等级'
                    ])->toArray()),
                Forms\Components\TextInput::make('nickname')
                    ->label('昵称')
                    ->columnSpanFull()
                    ->required(),
                Forms\Components\Toggle::make('is_staff')
                    ->label('是否为员工')
                    ->default(0),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageUsers::route('/'),
        ];
    }
}
