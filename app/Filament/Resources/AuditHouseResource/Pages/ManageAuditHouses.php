<?php

namespace App\Filament\Resources\AuditHouseResource\Pages;

use App\Filament\Resources\AuditHouseResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageAuditHouses extends ManageRecords
{
    protected static string $resource = AuditHouseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
