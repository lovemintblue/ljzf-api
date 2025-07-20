<?php

namespace App\Filament\Resources\ShopResource\Pages;

use App\Filament\Resources\ShopResource;
use Asmit\ResizedColumn\HasResizableColumn;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageShops extends ManageRecords
{
    use HasResizableColumn;

    protected static string $resource = ShopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
