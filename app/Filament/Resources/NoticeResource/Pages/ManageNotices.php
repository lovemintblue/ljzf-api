<?php

namespace App\Filament\Resources\NoticeResource\Pages;

use App\Filament\Resources\NoticeResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageNotices extends ManageRecords
{
    protected static string $resource = NoticeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
