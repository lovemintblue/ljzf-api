<?php

namespace App\Filament\Resources\DraftHouseResource\Pages;

use App\Filament\Resources\DraftHouseResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageDraftHouses extends ManageRecords
{
    protected static string $resource = DraftHouseResource::class;

    /**
     * @return array|Actions\Action[]|Actions\ActionGroup[]
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make()->label('创建草稿')
        ];
    }
}
