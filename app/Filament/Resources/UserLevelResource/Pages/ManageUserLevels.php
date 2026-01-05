<?php

namespace App\Filament\Resources\UserLevelResource\Pages;

use App\Filament\Resources\UserLevelResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageUserLevels extends ManageRecords
{
    protected static string $resource = UserLevelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
