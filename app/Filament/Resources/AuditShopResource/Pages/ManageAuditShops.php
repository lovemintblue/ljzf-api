<?php

namespace App\Filament\Resources\AuditShopResource\Pages;

use App\Filament\Resources\AuditShopResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageAuditShops extends ManageRecords
{
    protected static string $resource = AuditShopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
