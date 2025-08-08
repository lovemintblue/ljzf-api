<?php

namespace App\Filament\Resources\HouseFollowUpResource\Pages;

use App\Filament\Resources\HouseFollowUpResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageHouseFollowUps extends ManageRecords
{
    protected static string $resource = HouseFollowUpResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            Actions\CreateAction::make(),
        ];
    }
}
