<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\HouseFollowUp;
use App\Models\User;
use App\Models\UserLevel;
use App\Models\UsersViewPhoneLog;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class UserDetail extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = UserResource::class;

    protected static string $view = 'filament.resources.user-resource.pages.user-detail';

    protected static ?string $title = '用户详情';

    public ?User $record = null;

    public function mount(User $record): void
    {
        $this->record = $record->load(['userLevel', 'userLevelOrders.userLevel', 'houseFollowUps.house']);
    }

    public function getTitle(): string
    {
        return "用户详情 - {$this->record->nickname}";
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->record)
            ->schema([
                Section::make('基本信息')
                    ->schema([
                        ImageEntry::make('avatar')
                            ->label('头像')
                            ->circular()
                            ->default(null),
                        TextEntry::make('nickname')
                            ->label('用户昵称'),
                        TextEntry::make('phone')
                            ->label('手机号'),
                        TextEntry::make('userLevel.name')
                            ->label('当前VIP等级')
                            ->badge()
                            ->default('无等级'),
                        TextEntry::make('expired_at')
                            ->label('会员到期时间')
                            ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('Y-m-d H:i:s') : '无')
                            ->placeholder('无'),
                        TextEntry::make('id')
                            ->label('每日基础额度')
                            ->formatStateUsing(function ($state, $record) {
                                // 会员等级额度
                                $baseQuota = $record->userLevel->view_phone_count ?? 0;
                                // 个人额度调整值
                                $personalAdjustment = $record->view_phone_count ?? 0;
                                // 实际基础额度 = 会员等级额度 + 个人调整值
                                $actualQuota = $baseQuota + $personalAdjustment;
                                
                                if ($personalAdjustment > 0) {
                                    return "{$actualQuota} 次/天 ({$baseQuota}+{$personalAdjustment})";
                                } elseif ($personalAdjustment < 0) {
                                    return "{$actualQuota} 次/天 ({$baseQuota}{$personalAdjustment})";
                                } else {
                                    return "{$actualQuota} 次/天";
                                }
                            })
                            ->badge()
                            ->color(function ($state, $record) {
                                $personalAdjustment = $record->view_phone_count ?? 0;
                                if ($personalAdjustment > 0) return 'success';
                                if ($personalAdjustment < 0) return 'danger';
                                return 'primary';
                            })
                            ->helperText('会员等级额度 + 个人调整值')
                            ->default('0 次/天'),
                        TextEntry::make('id')
                            ->label('今日剩余次数')
                            ->formatStateUsing(function ($state, $record) {
                                // 查询今日已用次数
                                $use_num = UsersViewPhoneLog::where('user_id', $record->id)
                                    ->whereDate('created_at', today())
                                    ->count();
                                
                                // 从会员等级获取基础额度
                                $baseQuota = $record->userLevel->view_phone_count ?? 0;
                                
                                // 加上个人额度调整值（可正可负）
                                $personalAdjustment = $record->view_phone_count ?? 0;
                                
                                // 计算剩余次数 = 会员等级额度 + 个人调整值 - 已用次数 + 临时额度
                                $remaining = $baseQuota + $personalAdjustment - $use_num;
                                
                                // 加上临时额度
                                if ($record->temp_quota_date == today()->toDateString() && $record->temp_quota != 0) {
                                    $remaining += $record->temp_quota;
                                }
                                
                                $remaining = max(0, $remaining);
                                
                                // 存储到 record 对象中供 color 使用
                                $record->_remaining = $remaining;
                                
                                return $remaining . ' 次';
                            })
                            ->badge()
                            ->color(fn ($state, $record) => match(true) {
                                !isset($record->_remaining) || $record->_remaining <= 0 => 'danger',
                                $record->_remaining <= 10 => 'warning',
                                default => 'success'
                            }),
                        TextEntry::make('status')
                            ->label('账号状态')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state == 1 ? '正常' : '已禁用')
                            ->color(fn ($state) => $state == 1 ? 'success' : 'danger'),
                        TextEntry::make('is_staff')
                            ->label('是否员工')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state ? '是' : '否')
                            ->color(fn ($state) => $state ? 'success' : 'gray'),
                        TextEntry::make('created_at')
                            ->label('注册时间')
                            ->dateTime('Y-m-d H:i:s'),
                        TextEntry::make('latest_visit_at')
                            ->label('最近登录时间')
                            ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('Y-m-d H:i:s') : '暂无登录记录')
                            ->placeholder('暂无登录记录'),
                    ])
                    ->columns(3),

                Section::make('会员开通记录')
                    ->schema([
                        RepeatableEntry::make('userLevelOrders')
                            ->label('')
                            ->schema([
                                TextEntry::make('no')
                                    ->label('订单号'),
                                TextEntry::make('userLevel.name')
                                    ->label('会员等级')
                                    ->badge(),
                                TextEntry::make('total_amount')
                                    ->label('金额')
                                    ->money('CNY'),
                                TextEntry::make('cycle')
                                    ->label('周期')
                                    ->formatStateUsing(fn ($state) => match($state) {
                                        0 => '月付',
                                        1 => '季付',
                                        2 => '年付',
                                        default => '未知'
                                    }),
                                TextEntry::make('status')
                                    ->label('状态')
                                    ->badge()
                                    ->formatStateUsing(fn ($state) => match($state) {
                                        0 => '未支付',
                                        1 => '已支付',
                                        default => '未知'
                                    })
                                    ->color(fn ($state) => match($state) {
                                        0 => 'danger',
                                        1 => 'success',
                                        default => 'gray'
                                    }),
                                TextEntry::make('created_at')
                                    ->label('开通时间')
                                    ->dateTime('Y-m-d H:i:s'),
                            ])
                            ->columns(6)
                            ->columnSpanFull()
                            ->default([]),
                    ])
                    ->collapsible()
                    ->visible(fn () => $this->record->userLevelOrders->count() > 0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('房源跟进记录')
            ->query(
                HouseFollowUp::query()
                    ->where('user_id', $this->record->id)
                    ->with(['house.community'])
                    ->orderBy('created_at', 'desc')
            )
            ->columns([
                TextColumn::make('house.no')
                    ->label('房源编号')
                    ->searchable(),
                TextColumn::make('house.community.name')
                    ->label('所属小区'),
                TextColumn::make('house.building_number')
                    ->label('栋数'),
                TextColumn::make('house.room_number')
                    ->label('房号'),
                TextColumn::make('house.contact_phone')
                    ->label('房东电话')
                    ->searchable(),
                TextColumn::make('result')
                    ->label('跟进结果')
                    ->limit(50),
                TextColumn::make('is_punished')
                    ->label('是否处罚')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? '是' : '否')
                    ->color(fn ($state) => $state ? 'danger' : 'success'),
                TextColumn::make('created_at')
                    ->label('跟进时间')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50]);
    }
    
    public function hasFollowUps(): bool
    {
        return $this->record->houseFollowUps->count() > 0;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('返回用户列表')
                ->icon('heroicon-o-arrow-left')
                ->url(UserResource::getUrl('index'))
                ->color('gray'),
                
            Actions\Action::make('adjustQuota')
                ->label('调整个人额度')
                ->icon('heroicon-o-adjustments-horizontal')
                ->color('info')
                ->modalDescription('调整用户的个人每日基础额度（正数增加，负数扣减），可随时恢复')
                ->form([
                    TextInput::make('view_phone_count')
                        ->label('个人额度调整值')
                        ->helperText('正数=奖励增加，负数=处罚扣减，0=使用会员等级默认额度')
                        ->numeric()
                        ->minValue(-999)
                        ->maxValue(999)
                        ->suffix('次/天')
                        ->required()
                        ->default(fn () => $this->record->view_phone_count ?? 0)
                ])
                ->fillForm(fn (): array => [
                    'view_phone_count' => $this->record->view_phone_count ?? 0,
                ])
                ->action(function (array $data): void {
                    $adjustment = (int) $data['view_phone_count'];
                    
                    $this->record->view_phone_count = $adjustment;
                    $this->record->save();
                    
                    if ($adjustment > 0) {
                        $message = "已设置个人额度奖励 +{$adjustment} 次/天";
                    } else if ($adjustment < 0) {
                        $message = "已设置个人额度扣减 {$adjustment} 次/天";
                    } else {
                        $message = '已恢复到会员等级默认额度';
                    }
                    
                    Notification::make()
                        ->title('个人额度已调整')
                        ->body($message)
                        ->success()
                        ->send();
                }),
                
            Actions\Action::make('tempQuota')
                ->label('临时调整今日次数')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->modalDescription('仅影响今天的访问次数，明天自动恢复正常额度')
                ->form([
                    TextInput::make('temp_quota')
                        ->label('临时增加次数')
                        ->helperText('输入正数增加，输入负数减少（如：+5 或 -3）')
                        ->numeric()
                        ->minValue(-999)
                        ->maxValue(999)
                        ->suffix('次')
                        ->required()
                        ->default(0)
                ])
                ->action(function (array $data): void {
                    $today = now()->toDateString();
                    $tempQuota = (int) $data['temp_quota'];
                    
                    // 更新临时额度
                    $this->record->temp_quota = $tempQuota;
                    $this->record->temp_quota_date = $today;
                    $this->record->save();
                    
                    if ($tempQuota > 0) {
                        $message = '已临时增加 ' . $tempQuota . ' 次访问额度，明天自动恢复';
                    } else if ($tempQuota < 0) {
                        $message = '已临时减少 ' . abs($tempQuota) . ' 次访问额度，明天自动恢复';
                    } else {
                        $message = '已清除临时额度调整';
                    }
                    
                    Notification::make()
                        ->title('临时额度已调整')
                        ->body($message)
                        ->success()
                        ->send();
                }),
                
            Actions\Action::make('editExpiredAt')
                ->label('编辑到期时间')
                ->icon('heroicon-o-calendar')
                ->color('warning')
                ->form([
                    DateTimePicker::make('expired_at')
                        ->label('会员到期时间')
                        ->native(false)
                        ->seconds(false)
                        ->displayFormat('Y-m-d H:i')
                        ->default(fn () => $this->record->expired_at)
                        ->required()
                ])
                ->fillForm(fn (): array => [
                    'expired_at' => $this->record->expired_at,
                ])
                ->action(function (array $data): void {
                    $this->record->expired_at = $data['expired_at'];
                    $this->record->save();
                    
                    Notification::make()
                        ->title('到期时间已更新')
                        ->success()
                        ->send();
                }),
                
            Actions\Action::make('cancelVip')
                ->label('取消会员')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('确认取消会员')
                ->modalDescription('取消后该用户将失去会员权限，访问次数将清零')
                ->action(function (): void {
                    $this->record->user_level_id = 0;
                    $this->record->expired_at = null;
                    $this->record->view_phone_count = 0;
                    $this->record->save();
                    
                    Notification::make()
                        ->title('会员已取消')
                        ->success()
                        ->send();
                }),
                
            Actions\Action::make('disable')
                ->label('禁用')
                ->color('danger')
                ->icon('heroicon-o-no-symbol')
                ->visible(fn() => (int)$this->record->status === 1)
                ->requiresConfirmation()
                ->modalHeading('确认禁用用户')
                ->modalDescription('禁用后该用户将无法登录和使用系统')
                ->action(function () {
                    $this->record->status = 0;
                    $this->record->save();
                    
                    Notification::make()
                        ->title('禁用成功')
                        ->success()
                        ->send();
                }),
                
            Actions\Action::make('enable')
                ->label('取消禁用')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->visible(fn() => (int)$this->record->status === 0)
                ->requiresConfirmation()
                ->modalHeading('确认取消禁用')
                ->modalDescription('取消禁用后该用户可以正常登录使用系统')
                ->action(function () {
                    $this->record->status = 1;
                    $this->record->save();
                    
                    Notification::make()
                        ->title('取消禁用成功')
                        ->success()
                        ->send();
                }),
                
            Actions\Action::make('setVip')
                ->label('设为会员')
                ->color('primary')
                ->icon('heroicon-o-star')
                ->form([
                    Select::make('user_level_id')
                        ->label('会员等级')
                        ->options(UserLevel::query()->pluck('name', 'id'))
                        ->native(false)
                        ->required(),
                    DatePicker::make('expired_at')
                        ->label('到期时间')
                        ->native(false)
                        ->required()
                ])
                ->action(function (array $data): void {
                    $this->record->user_level_id = $data['user_level_id'];
                    $this->record->expired_at = $data['expired_at'];
                    $this->record->save();
                    
                    Notification::make()
                        ->title('会员设置成功')
                        ->success()
                        ->send();
                }),
                
            Actions\DeleteAction::make()
                ->record($this->record)
                ->requiresConfirmation()
                ->modalHeading('确认删除用户')
                ->modalDescription('删除后该用户的所有数据将被永久删除，此操作不可恢复')
                ->successRedirectUrl(UserResource::getUrl('index'))
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('删除成功')
                        ->body('用户已被删除')
                ),
        ];
    }
}

