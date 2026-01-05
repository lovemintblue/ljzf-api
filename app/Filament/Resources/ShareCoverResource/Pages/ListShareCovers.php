<?php

namespace App\Filament\Resources\ShareCoverResource\Pages;

use App\Filament\Resources\ShareCoverResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListShareCovers extends ListRecords
{
    protected static string $resource = ShareCoverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

