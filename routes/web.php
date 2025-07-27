<?php

use App\Models\House;
use App\Models\Shop;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::get('/', function () {
    return view('welcome');
});

Route::get('test', function () {
//    $data = [
//        [
//            'path' => '1751700054_xCSW5HaI9P.jpeg',
//            'url' => 'https://ljzf-api.zcylovezhx.com/storage/1751700054_xCSW5HaI9P.jpeg',
//        ],
//        [
//            'path' => '1751700054_xCSW5HaI9P.jpeg',
//            'url' => 'https://ljzf-api.zcylovezhx.com/storage/1751700054_xCSW5HaI9P.jpeg',
//        ],
//    ];
//    echo json_encode($data, JSON_UNESCAPED_UNICODE);

    $data = \App\Models\BusinessDistrict::query()->pluck('name', 'id')->toArray();

    Log::info($data);
});


Route::get('test2', static function (\Illuminate\Http\Request $request) {
    $communities = \App\Models\Community::query()->whereNull('longitude')->inRandomOrder()->get();
    foreach ($communities as $community) {
        $info = (new \App\Services\MapService())->geoCoder($community->address);

        Log::info('--打印信息--');
        Log::info($info);

        $community->longitude = $info['location']['lng'];
        $community->latitude = $info['location']['lat'];
        $community->save();
        Log::info('--更新成功--');
    }
});

