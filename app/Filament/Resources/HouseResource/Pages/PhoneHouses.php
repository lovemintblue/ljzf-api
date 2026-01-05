<?php

namespace App\Filament\Resources\HouseResource\Pages;

use App\Filament\Resources\HouseResource;
use App\Models\Community;
use App\Models\Facility;
use App\Models\House;
use App\Models\HouseOperationLog;
use App\Services\NotificationService;
use Filament\Forms;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Guava\FilamentModalRelationManagers\Actions\Table\RelationManagerAction;
use Illuminate\Database\Eloquent\Builder;

class PhoneHouses extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = HouseResource::class;

    protected static string $view = 'filament.resources.house-resource.pages.phone-houses';

    protected static ?string $title = '房东房源管理';

    public ?string $phone = null;
    public ?string $from = null;

    public function mount(): void
    {
        $this->phone = request()->query('phone');
        $this->from = request()->query('from');
        
        if (!$this->phone) {
            redirect()->route('filament.admin.resources.houses.index');
        }
    }

    public function getTitle(): string
    {
        return "房东房源管理 - {$this->phone}";
    }

    public function getHeading(): string
    {
        $count = House::where('contact_phone', $this->phone)
            ->where('audit_status', 1)
            ->where('is_draft', 0)
            ->count();
        
        return "房东：{$this->phone} （共 {$count} 套房源）";
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                House::query()
                    ->where('contact_phone', $this->phone)
                    ->where('audit_status', 1)
                    ->where('is_draft', 0)
            )
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->label('封面图'),
                Tables\Columns\TextColumn::make('no')
                    ->label('编号')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.nickname')
                    ->label('发布人'),
                Tables\Columns\TextColumn::make('community.name')
                    ->label('小区')
                    ->searchable(),
                Tables\Columns\TextColumn::make('building_number')
                    ->label('栋数')
                    ->searchable(),
                Tables\Columns\TextColumn::make('unit')
                    ->label('单元')
                    ->searchable(),
                Tables\Columns\TextColumn::make('floor')
                    ->label('楼层')
                    ->searchable(),
                Tables\Columns\TextColumn::make('room_number')
                    ->label('房号')
                    ->searchable(),
                Tables\Columns\TextColumn::make('room_config')
                    ->label('房间配置')
                    ->getStateUsing(function (House $record) {
                        return $record->room_count . '室|' . $record->living_room_count . '厅|' . $record->bathroom_count . '卫';
                    }),
                Tables\Columns\TextColumn::make('rent_price')
                    ->label('租金')
                    ->searchable(),
                Tables\Columns\TextColumn::make('contact_name')
                    ->label('联系人')
                    ->searchable()
                    ->description(fn (House $record) => $record->backup_contact_name ? "备用: {$record->backup_contact_name}" : null),
                Tables\Columns\TextColumn::make('contact_phone')
                    ->label('联系电话')
                    ->searchable()
                    ->description(fn (House $record) => $record->backup_contact_phone ? "备用: {$record->backup_contact_phone}" : null),
                Tables\Columns\TextColumn::make('is_show')
                    ->label('上架状态')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '1' => 'success',
                        '0' => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        '1' => '已上架',
                        '0' => '已下架',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
            ])
            ->recordUrl(null)
            ->recordAction(null)
            ->filters([
                // 第一排：小区、栋数、房号、上下架状态
                SelectFilter::make('community_id')
                    ->label('小区')
                    ->searchable()
                    ->native(false)
                    ->options(Community::all()->pluck('name', 'id')),
                Tables\Filters\Filter::make('building_number')
                    ->label('栋数')
                    ->form([
                        Forms\Components\TextInput::make('building_number')
                            ->label('栋数')
                            ->placeholder('输入栋数搜索')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['building_number'],
                                fn (Builder $query, $value): Builder => $query->where('building_number', 'like', "%{$value}%")
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['building_number']) {
                            return null;
                        }
                        return '栋数: ' . $data['building_number'];
                    }),
                Tables\Filters\Filter::make('room_number')
                    ->label('房号')
                    ->form([
                        Forms\Components\TextInput::make('room_number')
                            ->label('房号')
                            ->placeholder('输入房号搜索')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['room_number'],
                                fn (Builder $query, $value): Builder => $query->where('room_number', 'like', "%{$value}%")
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['room_number']) {
                            return null;
                        }
                        return '房号: ' . $data['room_number'];
                    }),
                SelectFilter::make('is_show')
                    ->label('房源上/下架状态')
                    ->native(false)
                    ->options([
                        1 => '上架中',
                        0 => '已下架',
                    ]),
                // 第二排：室、厅、卫
                SelectFilter::make('room_count')
                    ->label('室')
                    ->native(false)
                    ->options([
                        1 => '1室',
                        2 => '2室',
                        3 => '3室',
                        4 => '4室',
                    ]),
                SelectFilter::make('living_room_count')
                    ->label('厅')
                    ->native(false)
                    ->options([
                        0 => '0厅',
                        1 => '1厅',
                        2 => '2厅',
                    ]),
                SelectFilter::make('bathroom_count')
                    ->label('卫')
                    ->native(false)
                    ->options([
                        0 => '0卫',
                        1 => '1卫',
                        2 => '2卫',
                    ]),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->actions([
                Tables\Actions\Action::make('更新')
                    ->color('info')
                    ->icon('heroicon-o-arrow-path')
                    ->visible(fn(House $record) => $record->is_show)
                    ->requiresConfirmation()
                    ->modalHeading('确认更新时间')
                    ->modalDescription('更新后房源的创建时间将变为当前时间，会排到列表最前面。')
                    ->action(function (House $record) {
                        $record->created_at = now();
                        $record->last_updated_at = now();
                        $record->save();
                        
                        // 记录操作日志
                        HouseOperationLog::create([
                            'house_id' => $record->id,
                            'operator_id' => auth()->id(),
                            'operator_type' => 'admin',
                            'operation_type' => 'update',
                        ]);
                    })
                    ->successNotification(
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('更新成功')
                            ->body('房源时间已更新。如果从待更新房源页面跳转过来，请刷新待更新房源页面查看最新数据。')
                    ),
                Tables\Actions\Action::make('上架')
                    ->color('success')
                    ->icon('heroicon-o-arrow-up')
                    ->visible(fn(House $record) => !$record->is_show)
                    ->requiresConfirmation()
                    ->modalHeading('确认上架')
                    ->modalDescription('上架后房源将对用户可见，同时会更新创建时间为当前时间。')
                    ->action(function (House $record) {
                        $record->is_show = true;
                        $record->created_at = now();
                        $record->last_updated_at = now(); // 更新最后更新时间，以便待更新房源列表能正确刷新
                        $record->save();
                        
                        // 记录操作日志
                        HouseOperationLog::create([
                            'house_id' => $record->id,
                            'operator_id' => auth()->id(),
                            'operator_type' => 'admin',
                            'operation_type' => 'online',
                        ]);
                    })
                    ->successNotification(
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('上架成功')
                            ->body('房源已上架。如果从待更新房源页面跳转过来，请刷新待更新房源页面查看最新数据。')
                    ),
                Tables\Actions\Action::make('下架')
                    ->color('warning')
                    ->icon('heroicon-o-arrow-down')
                    ->visible(fn(House $record) => $record->is_show)
                    ->requiresConfirmation()
                    ->modalHeading('确认下架')
                    ->modalDescription('下架后房源将不对用户显示。')
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('下架原因')
                            ->required()
                            ->default('管理员下架')
                            ->rows(3),
                    ])
                    ->action(function (House $record, array $data) {
                        $record->is_show = false;
                        $record->last_updated_at = now(); // 更新最后更新时间，以便待更新房源列表能正确刷新
                        $record->save();
                        
                        // 记录操作日志
                        HouseOperationLog::create([
                            'house_id' => $record->id,
                            'operator_id' => auth()->id(),
                            'operator_type' => 'admin',
                            'operation_type' => 'offline',
                            'reason' => $data['reason'] ?? null,
                        ]);
                        
                        // 发送下架通知
                        if ($record->user) {
                            (new NotificationService())->notifyHouseOffline(
                                $record->user,
                                $record,
                                $data['reason'] ?? '管理员下架'
                            );
                        }
                    })
                    ->successNotification(
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('下架成功')
                            ->body('房源已下架。如果从待更新房源页面跳转过来，请刷新待更新房源页面查看最新数据。')
                    ),
                Tables\Actions\Action::make('follow_up_logs')
                    ->label('跟进操作日志')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->modalWidth('7xl')
                    ->modalHeading(fn (House $record) => '跟进操作日志 - ' . $record->no)
                    ->modalContent(fn (House $record) => view('filament.modals.house-logs', ['house' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('关闭'),
                Tables\Actions\Action::make('通过')
                    ->color('success')
                    ->visible(fn(House $record) => (int)$record->audit_status === 0)
                    ->requiresConfirmation()
                    ->action(function (House $record) {
                        $record->audit_status = 1;
                        $record->save();
                        
                        // 记录首次发布日志（如果之前没有发布记录）
                        $hasPublishLog = HouseOperationLog::where('house_id', $record->id)
                            ->where('operation_type', 'publish')
                            ->exists();
                        if (!$hasPublishLog) {
                            HouseOperationLog::create([
                                'house_id' => $record->id,
                                'operator_id' => auth()->id(),
                                'operator_type' => 'admin',
                                'operation_type' => 'publish',
                            ]);
                        }
                        
                        // 发送审核通过通知
                        if ($record->user) {
                            (new NotificationService())->notifyHouseAuditPassed($record->user, $record);
                        }
                    }),
                Tables\Actions\Action::make('驳回')
                    ->color('danger')
                    ->visible(fn(House $record) => (int)$record->audit_status === 0)
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('驳回原因')
                            ->placeholder('请输入驳回原因，将发送通知给用户')
                            ->required()
                            ->maxLength(500),
                    ])
                    ->modalHeading('驳回房源')
                    ->modalDescription('请填写驳回原因，用户将收到违规通知')
                    ->action(function (House $record, array $data) {
                        $record->audit_status = 2;
                        $record->save();
                        
                        // 发送违规通知
                        if ($record->user) {
                            (new \App\Services\NotificationService())->notifyViolationWarning(
                                $record->user,
                                '房源',
                                $record->title ?? '未命名房源',
                                $data['reason']
                            );
                        }
                    }),
                Tables\Actions\EditAction::make()
                    ->modalWidth('7xl')
                    ->modalHeading('编辑房源')
                    ->slideOver(false)
                    ->form(HouseResource::getFormSchema()),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        $backUrl = $this->from === 'pending' 
            ? \App\Filament\Resources\PendingUpdateHouseResource::getUrl('index')
            : HouseResource::getUrl('index');
            
        $backLabel = $this->from === 'pending' 
            ? '返回待更新房源'
            : '返回房源列表';
        
        return [
            \Filament\Actions\Action::make('back')
                ->label($backLabel)
                ->icon('heroicon-o-arrow-left')
                ->url($backUrl)
                ->color('gray'),
        ];
    }
}

