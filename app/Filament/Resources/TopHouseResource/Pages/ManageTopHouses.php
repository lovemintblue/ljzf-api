<?php

namespace App\Filament\Resources\TopHouseResource\Pages;

use App\Filament\Resources\TopHouseResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageTopHouses extends ManageRecords
{
    protected static string $resource = TopHouseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('help')
                ->label('使用说明')
                ->icon('heroicon-o-question-mark-circle')
                ->color('gray')
                ->modalHeading('房源推广使用说明')
                ->modalDescription('本页面用于管理置顶房源，控制小程序首页的推广展示。')
                ->modalContent(view('filament.modals.top-house-help'))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('关闭'),
        ];
    }
}

