<?php

namespace App\Services;

use Spatie\TemporaryDirectory\Exceptions\PathAlreadyExists;

class HouseService
{
    /**
     * @param $house
     * @return void
     * @throws PathAlreadyExists
     */
    public static function handleWatermark($house): void
    {
        if (empty($house->watermark_images) || !empty($house->images)) {
            $watermarkImages = collect($house->images)->map(function ($image) {
                return WatermarkService::handleImage($image);
            });
            $watermarkImages = array_values(array_filter($watermarkImages->toArray()));
            $house->watermark_images = $watermarkImages;
        }
        if (!empty($house->watermark_video) || !empty($house->video)) {
            $house->watermark_video = WatermarkService::handleVideo($house->video);
        }
        $house->save();
    }
}
