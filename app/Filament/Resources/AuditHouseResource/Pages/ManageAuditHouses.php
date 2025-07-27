<?php

namespace App\Filament\Resources\AuditHouseResource\Pages;

use App\Filament\Resources\AuditHouseResource;
use Asmit\ResizedColumn\HasResizableColumn;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageAuditHouses extends ManageRecords
{
    use HasResizableColumn;

    protected static string $resource = AuditHouseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
