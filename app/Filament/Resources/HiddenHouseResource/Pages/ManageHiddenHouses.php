<?php

namespace App\Filament\Resources\HiddenHouseResource\Pages;

use App\Filament\Resources\HiddenHouseResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageHiddenHouses extends ManageRecords
{
    protected static string $resource = HiddenHouseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
