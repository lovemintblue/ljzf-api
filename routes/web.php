<?php

use App\Jobs\ProcessVideoWatermark;
use App\Models\House;
use App\Models\Shop;
use FFMpeg\Format\Video\X264;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLevelOrder;
use App\Notifications\UserNotification;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;

Route::get('/', function () {
    return view('welcome');
});

Route::get('test', function () {
//    ProcessVideoWatermark::dispatch();
    $house = House::query()->where('no','H93')->first();
//    $coverImage = formatUrl($house->cover_image);
//    $manager = ImageManager::gd();
//    $image = $manager->read(file_get_contents($coverImage));
//    $image->place(public_path('images/watermark.png'),'center',0,0);
//    $image->save(public_path('images/watermark111.png'));
//    dd("ok");
    $sourceVideoUrl = formatUrl($house->video);
    $ffmpeg = FFMpeg\FFMpeg::create([
        'ffmpeg.binaries'  => '/opt/homebrew/bin/ffmpeg',
        'ffprobe.binaries' => '/opt/homebrew/bin/ffprobe',
        'timeout'          => 3600, // The timeout for the underlying process
        'ffmpeg.threads'   => 4,   // The number of threads that FFMpeg should use
    ]);
    $video = $ffmpeg->open($sourceVideoUrl);
    $video->filters()->watermark(public_path('images/watermark.png'),[
        'position' => 'absolute', // 必须设为absolute，才能用坐标公式
        'x' => 'main_w/2 - overlay_w/2', // 水平居中：视频宽度/2 - 水印宽度/2
        'y' => 'main_h/2 - overlay_h/2', // 垂直居中：视频高度/2 - 水印高度/2
        'opacity' => 0.7, // 保留透明度，避免水印遮挡视频
        'size'     => ['width' => '400', 'height' => '200'], // 1.3版本仅支持数组格式！
    ]);
    $watermarkPath = public_path('images/watermark.png');
    $format = new X264();
    $format->setAudioCodec('aac')
        ->setAudioKiloBitrate(128)
        ->setKiloBitrate(1000)
        ->setAdditionalParameters([
            '-pix_fmt', 'yuv420p', // 必加！多数播放器只支持yuv420p像素格式
            '-profile:v', 'main'   // H.264主配置文件，兼容所有播放器
        ]);
    $video->save($format, public_path('images/video.mp4'));
    $path = time().'video.mp4';
    Storage::put($path, file_get_contents(public_path('images/video.mp4')));
    dd(Storage::url($path));
});

Route::get('test2', function () {

});
