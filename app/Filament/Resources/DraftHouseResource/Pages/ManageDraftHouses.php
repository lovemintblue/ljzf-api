<?php

namespace App\Filament\Resources\DraftHouseResource\Pages;

use App\Filament\Resources\DraftHouseResource;
use App\Models\DraftHouse;
use App\Models\House;
use Asmit\ResizedColumn\HasResizableColumn;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;

class ManageDraftHouses extends ManageRecords
{
    use HasResizableColumn;

    protected static string $resource = DraftHouseResource::class;

    /**
     * @return array|Actions\Action[]|Actions\ActionGroup[]
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('创建草稿')
                ->action(function () {
                    $house = new House();
                    $house->user_id = 0;
                    $house->is_draft = 1;
                    $house->save();

                    Notification::make()->title('草稿-创建成功')->success()->send();
                }),
        ];
    }
}
