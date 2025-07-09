<?php

namespace App\Filament\Resources\CommunityResource\Pages;

use App\Filament\Resources\CommunityResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageCommunities extends ManageRecords
{
    protected static string $resource = CommunityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
