<?php

namespace App\Filament\Resources\ShareCoverResource\Pages;

use App\Filament\Resources\ShareCoverResource;
use Filament\Resources\Pages\CreateRecord;

class CreateShareCover extends CreateRecord
{
    protected static string $resource = ShareCoverResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

