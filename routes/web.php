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
    $houses = House::query()->whereNull('longitude')->inRandomOrder()->get();
    foreach ($houses as $house) {
        $info = (new \App\Services\MapService())->geoCoder($house->address);

        Log::info('--打印信息--');
        Log::info($info);

        $house->longitude = $info['location']['lng'];
        $house->latitude = $info['location']['lat'];
        $house->save();
        Log::info('--更新成功--');
    }
});

