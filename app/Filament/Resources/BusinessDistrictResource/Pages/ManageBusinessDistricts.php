<?php

namespace App\Filament\Resources\BusinessDistrictResource\Pages;

use App\Filament\Resources\BusinessDistrictResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageBusinessDistricts extends ManageRecords
{
    protected static string $resource = BusinessDistrictResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
