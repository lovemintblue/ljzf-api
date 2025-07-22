<?php

namespace App\Filament\Resources\VipUserResource\Pages;

use App\Filament\Resources\VipUserResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageVipUsers extends ManageRecords
{
    protected static string $resource = VipUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
