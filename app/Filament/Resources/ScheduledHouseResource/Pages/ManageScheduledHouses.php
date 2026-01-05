<?php

namespace App\Filament\Resources\ScheduledHouseResource\Pages;

use App\Filament\Resources\ScheduledHouseResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Notifications\Notification;

class ManageScheduledHouses extends ManageRecords
{
    protected static string $resource = ScheduledHouseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('execute_scheduled')
                ->label('立即执行检查')
                ->icon('heroicon-o-play')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('确认执行检查')
                ->modalDescription('此操作将立即检查并发布所有到期的房源（正常情况下每天凌晨2点自动执行）')
                ->modalSubmitActionLabel('确认执行')
                ->action(function () {
                    // 调用定时任务命令
                    \Illuminate\Support\Facades\Artisan::call('houses:publish-scheduled');
                    
                    $output = \Illuminate\Support\Facades\Artisan::output();
                    
                    Notification::make()
                        ->success()
                        ->title('执行完成')
                        ->body($output ?: '已执行定时任务检查')
                        ->send();
                    
                    // 刷新页面
                    $this->redirect(static::getResource()::getUrl('index'));
                }),
        ];
    }
}

