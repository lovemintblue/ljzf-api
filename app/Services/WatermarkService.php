<?php
/**
 * 视频水印 Service
 */
namespace App\Services;

use FFMpeg\Format\Video\X264;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use ProtoneMedia\LaravelFFMpeg\FFMpeg\CopyFormat;
use ProtoneMedia\LaravelFFMpeg\Filters\WatermarkFactory;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Spatie\TemporaryDirectory\TemporaryDirectory;

class WatermarkService
{
    /**
     * 处理水印图片
     * @param $watermarkPath
     * @return string
     * @throws \Spatie\TemporaryDirectory\Exceptions\PathAlreadyExists
     */
    public static function handleImage($watermarkPath)
    {
        $imageUrl = formatUrl($watermarkPath);
        $tempDir = (new TemporaryDirectory())->create();
        $manager = ImageManager::gd();
        try {
            $image = $manager->read(file_get_contents($imageUrl));
            $image->place(public_path('images/watermark.png'),'center',0,0);
            $tempFileName = 'watermark_' . Str::random(8) . time() . '.png';
            $tempFilePath = $tempDir->path($tempFileName);
            $image->save($tempFilePath);
            Storage::put($tempFileName, file_get_contents($tempFilePath));
            $tempDir->delete();
            return $tempFileName;
        } catch (\Exception $exception) {
            return '';
        }
    }

    /**
     * 处理视频图片
     * @param $watermarkPath
     * @return string
     */
    public static function handleVideo($watermarkPath): string
    {
        $tempFileName = 'watermark_' . Str::random(8) . time() . '.mp4';
        FFMpeg::fromDisk('qiniu')
            ->open($watermarkPath)
            ->addWatermark(function(WatermarkFactory $watermark) {
                $watermark->fromDisk('qiniu')
                    ->open('watermark.png')
                    ->horizontalAlignment('center')
                    ->verticalAlignment('center');
            })
            ->export()
            ->inFormat(new X264)
            ->toDisk('qiniu')
            ->save($tempFileName);
        return $tempFileName;
    }
}
