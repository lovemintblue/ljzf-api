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

    $postData = [
        'keyword' => '小区',
        'specify' => '156110000', // 行政区划代码
        'queryType' => 12,        // 修改为行政区划搜索（根据实际需求调整）
        'start' => 0,
        'count' => 100,
        'dataTypes' => '120100'   // 使用有效数据类型编码（兴趣点）
    ];

// 转换为JSON字符串
    $postStr = json_encode($postData);


    $response = Http::get($api, [
        'postStr' => $postStr,
        'type' => 'query',
        'tk' => '5731ae54a2b2ab10697a929c5b6b8e11'
    ]);
    dd($response->json());
});

