<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VipUserResource\Pages;
use App\Filament\Resources\VipUserResource\RelationManagers;
use App\Models\User;
use App\Models\UserLevel;
use App\Models\VipUser;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VipUserResource extends Resource
{
    protected static ?string $model = VipUser::class;

    protected static ?string $navigationIcon = 'heroicon-m-squares-2x2';

    protected static ?string $navigationGroup = '用户';

    protected static ?string $navigationLabel = '会员列表';

    protected static ?string $label = '用户';
    protected static ?int $navigationSort = 1;

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
            ->query(function (User $query) {
                return $query->where('user_level_id', '>', 0);
            })
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
                Tables\Columns\TextColumn::make('province')
                    ->label('省份')
                    ->badge(),
                Tables\Columns\TextColumn::make('city')
                    ->label('城市')
                    ->badge(),
                Tables\Columns\ToggleColumn::make('status')
                    ->label('状态'),
                Tables\Columns\TextColumn::make('latest_visit_at')
                    ->label('上次访问时间')
                    ->since(),
                Tables\Columns\TextColumn::make('view_phone_count')
                    ->badge()
                    ->label('查看电话次数'),
                Tables\Columns\TextColumn::make('expired_at')
                    ->label('到期时间')
                    ->dateTime('Y-m-d'),
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
            'index' => Pages\ManageVipUsers::route('/'),
        ];
    }
}
