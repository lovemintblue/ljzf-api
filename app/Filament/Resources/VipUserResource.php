<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VipUserResource\Pages;
use App\Filament\Resources\VipUserResource\RelationManagers;
use App\Filament\Resources\UserResource;
use App\Models\User;
use App\Models\UserLevel;
use App\Models\UsersViewPhoneLog;
use App\Models\VipUser;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Symfony\Component\Console\Input\Input;

class VipUserResource extends Resource
{
    protected static ?string $model = VipUser::class;

    protected static ?string $navigationIcon = 'heroicon-m-squares-2x2';

    protected static ?string $navigationGroup = '用户';

    protected static ?string $navigationLabel = '会员列表';

    protected static ?string $label = '会员列表';
    protected static ?int $navigationSort = 1;

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
                Tables\Columns\TextColumn::make('latest_visit_at')
                    ->label('上次访问时间')
                    ->since(),
                Tables\Columns\TextColumn::make('view_phone_count')
                    ->badge()
                    ->label('每日基础电话额度次数')
                    ->getStateUsing(function ($record) {
                        // 会员等级额度
                        $baseQuota = $record->userLevel->view_phone_count ?? 0;
                        // 个人额度调整值
                        $personalAdjustment = $record->view_phone_count ?? 0;
                        // 实际基础额度
                        $actualQuota = $baseQuota + $personalAdjustment;
                        
                        if ($personalAdjustment > 0) {
                            return "{$actualQuota} 次 ({$baseQuota}+{$personalAdjustment})";
                        } elseif ($personalAdjustment < 0) {
                            return "{$actualQuota} 次 ({$baseQuota}{$personalAdjustment})";
                        } else {
                            return "{$actualQuota} 次";
                        }
                    })
                    ->color(function ($record) {
                        $personalAdjustment = $record->view_phone_count ?? 0;
                        if ($personalAdjustment > 0) return 'success';
                        if ($personalAdjustment < 0) return 'danger';
                        return 'info';
                    }),
                Tables\Columns\TextColumn::make('daily_remaining_count')
                    ->badge()
                    ->label('每日剩余查看次数')
                    ->getStateUsing(function ($record) {
                        $use_num = UsersViewPhoneLog::where('user_id',$record->id)->where('created_at','>=',date('Y-m-d') . ' 00:00:00')->where('created_at','<=',date('Y-m-d') . ' 23:59:59')->count();
                        // 从会员等级获取基础额度
                        $baseQuota = $record->userLevel->view_phone_count ?? 0;
                        // 加上个人额度调整值（可正可负）
                        $personalAdjustment = $record->view_phone_count ?? 0;
                        // 计算剩余次数 = 会员等级额度 + 个人调整值 - 已用次数 + 临时额度
                        $remaining = $baseQuota + $personalAdjustment - $use_num;
                        // 加上临时额度
                        if ($record->temp_quota_date == date('Y-m-d') && $record->temp_quota != 0) {
                            $remaining += $record->temp_quota;
                        }
                        return max(0, $remaining);
                    })
                    ->color(function ($state) {
                        if ($state <= 0) return 'danger';
                        if ($state <= 5) return 'warning';
                        return 'success';
                    }),
                Tables\Columns\TextColumn::make('expired_at')
                    ->label('到期时间')
                    ->dateTime('Y-m-d')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime('Y-m-d')
                    ->sortable()
            ])
            ->recordUrl(null)
            ->recordAction(null)
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('详情')
                    ->label('详情')
                    ->color('info')
                    ->icon('heroicon-o-eye')
                    ->button()
                    ->url(fn (User $record): string => UserResource::getUrl('detail', ['record' => $record]))
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
                    ->required()
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageVipUsers::route('/'),
        ];
    }
}
