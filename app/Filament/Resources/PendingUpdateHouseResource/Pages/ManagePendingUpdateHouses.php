<?php

namespace App\Filament\Resources\PendingUpdateHouseResource\Pages;

use App\Filament\Resources\PendingUpdateHouseResource;
use App\Settings\GeneralSettings;
use Filament\Resources\Pages\ManageRecords;

class ManagePendingUpdateHouses extends ManageRecords
{
    protected static string $resource = PendingUpdateHouseResource::class;

    public function getTitle(): string
    {
        $settings = app(GeneralSettings::class);
        $days = $settings->house_update_days;
        
        $count = $this->getTableQuery()->count();
        
        return "待更新房源（共 {$count} 套，超过 {$days} 天）";
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    // 页面挂载时刷新表格，确保数据是最新的
    public function mount(): void
    {
        parent::mount();
        // 重置表格状态，强制重新查询数据库
        $this->resetTable();
    }
}

