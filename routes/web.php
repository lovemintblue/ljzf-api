<?php

use App\Models\House;
use App\Models\Shop;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::get('/', function () {
    return view('welcome');
});

Route::get('test', function () {
    $data = [
        [
            'path' => '1751700054_xCSW5HaI9P.jpeg',
            'url' => 'https://ljzf-api.zcylovezhx.com/storage/1751700054_xCSW5HaI9P.jpeg',
        ],
        [
            'path' => '1751700054_xCSW5HaI9P.jpeg',
            'url' => 'https://ljzf-api.zcylovezhx.com/storage/1751700054_xCSW5HaI9P.jpeg',
        ],
    ];
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
});


Route::get('test2', static function (\Illuminate\Http\Request $request) {
//    $data = [
//        [
//            'path' => '1751700054_xCSW5HaI9P.jpeg',
//            'url' => ''
//        ],
//        [
//            'path' => '1751700054_xCSW5HaI9P.jpeg',
//            'url' => ''
//        ],
//    ];
//    echo json_encode($data, JSON_UNESCAPED_UNICODE);

//    $houses = House::all();
//    foreach ($houses as $house) {
//        $house->save();
//    }
//    $shops = Shop::all();
//    foreach ($shops as $shop) {
//        $shop->save();
//    }

    $api = 'http://api.tianditu.gov.cn/v2/search';

    // 将 postStr 参数转换为 JSON 字符串
    $postStr = json_encode([
        'keyword' => '小区',
        'specify' => '156110000', // 注意：specify 需为字符串格式
        'queryType' => 12,
        'start' => 0,
        'count' => 100,
//        'dataTypes' => '120201',
    ]);

    $response = Http::get($api, [
        'postStr' => $postStr,
        'type' => 'query',
        'tk' => '5731ae54a2b2ab10697a929c5b6b8e11'
    ]);
    dd($response->json());
});

