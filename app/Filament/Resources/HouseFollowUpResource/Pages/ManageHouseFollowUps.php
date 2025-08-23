<?php

namespace App\Filament\Resources\HouseFollowUpResource\Pages;

use App\Filament\Resources\HouseFollowUpResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ManageHouseFollowUps extends ManageRecords
{
    protected static string $resource = HouseFollowUpResource::class;

    public function getTabs(): array
    {
        return [
            '在租' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('result', '在租')),
            '已出租' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('result', '已出租')),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
//            Actions\CreateAction::make(),
        ];
    }
}
