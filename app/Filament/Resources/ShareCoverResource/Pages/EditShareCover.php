<?php

namespace App\Filament\Resources\ShareCoverResource\Pages;

use App\Filament\Resources\ShareCoverResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditShareCover extends EditRecord
{
    protected static string $resource = ShareCoverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

