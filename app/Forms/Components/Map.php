<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;

class Map extends Field
{
    protected string $view = 'forms.components.map';

    // 地图默认中心点（例如：北京）
    protected float $defaultLatitude = 39.9042;
    protected float $defaultLongitude = 116.4074;

    // 允许外部设置默认中心点
    public function defaultCenter(float $latitude, float $longitude): static
    {
        $this->defaultLatitude = $latitude;
        $this->defaultLongitude = $longitude;
        return $this;
    }

    // 传递参数到视图
    public function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'defaultLat' => $this->defaultLatitude,
            'defaultLng' => $this->defaultLongitude,
        ]);
    }

}
