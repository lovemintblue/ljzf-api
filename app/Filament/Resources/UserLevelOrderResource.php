<?php
/**
 * 管理后台 - 会员订单 Controller
 */

namespace App\Filament\Resources;

use App\Filament\Resources\UserLevelOrderResource\Pages;
use App\Filament\Resources\UserLevelOrderResource\RelationManagers;
use App\Models\UserLevelOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserLevelOrderResource extends Resource
{
    protected static ?string $model = UserLevelOrder::class;

    protected static ?string $navigationIcon = 'heroicon-m-squares-2x2';

    protected static ?string $navigationGroup = '财务';

    protected static ?string $navigationLabel = '开通会员订单';

    protected static ?string $label = '开通会员订单';

    /**
     * @param Form $form
     * @return Form
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('no')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'id')
                    ->required(),
                Forms\Components\Select::make('user_level_id')
                    ->relationship('userLevel', 'name')
                    ->required(),
                Forms\Components\TextInput::make('total_amount')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\Select::make('status')
                    ->label('支付状态')
                    ->options([
                        0 => '未支付',
                        1 => '已支付',
                    ])
                    ->required()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(function (UserLevelOrder $query) {
                return $query->orderBy('created_at', 'DESC');
            })
            ->columns([
                Tables\Columns\TextColumn::make('no')
                    ->label('订单编号')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.id')
                    ->numeric()
                    ->label('用户ID')
                    ->sortable(),
                Tables\Columns\ImageColumn::make('user.avatar')
                    ->label('头像'),
                Tables\Columns\TextColumn::make('user.nickname')
                    ->label('用户')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.phone')
                    ->label('用户电话')
                    ->searchable(),
                Tables\Columns\TextColumn::make('userLevel.name')
                    ->numeric()
                    ->label('会员等级')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->numeric()
                    ->label('订单金额')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('支付状态')
                    ->formatStateUsing(fn (string $state): string => match ((int) $state) {
                        0 => '未支付',
                        1 => '已支付',
                        default => '未知状态',
                    })
                    ->colors([
                        'danger' => 0,
                        'success' => 1,
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime()
                    ->sortable()
                    ->dateTime('Y-m-d H:i:s'),
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
            'index' => Pages\ManageUserLevelOrders::route('/'),
        ];
    }
}
