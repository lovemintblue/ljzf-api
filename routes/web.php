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

    // 构建请求参数
    $postStr = json_encode([
        'specify' => '156360702',
        'queryType' => 13,
        'start' => 0,
        'count' => 1,
        'dataTypes' => '120201',
        'show' => 2
    ]);

    // 构建API URL
    $url = "http://api.tianditu.gov.cn/v2/search";
    $allData = [];
    $pageSize = 100;
    $start = 0;
    // 发送HTTP请求
    $response = Http::get($url, [
        'postStr' => $postStr,
        'type' => 'query',
        'tk' => '5731ae54a2b2ab10697a929c5b6b8e11'
    ]);
    $totalCount = $response->json()['count'] ?? 0; // 总数据条数
    // 若总条数≤100，支持分页；否则仅获取前100条
    $maxStart = max(0, $totalCount - $pageSize);
    do {
        Log::info($start);
        Log::info($maxStart);
        if ($start > $maxStart) {
            Log::info('------');
            Log::info($start);
            Log::info($maxStart);
            Log::info('------');
            break; // 超过最大允许的start，停止请求
        }
        Log::info('---循环---');
        $postStr = json_encode([
            'specify' => '156360702',
            'queryType' => 13,
            'start' => $start,
            'count' => $pageSize,
            'dataTypes' => '120201',
            'show' => 2
        ]);
        $response = Http::get("http://api.tianditu.gov.cn/v2/search", [
            'postStr' => $postStr,
            'type' => 'query',
            'tk' => '5731ae54a2b2ab10697a929c5b6b8e11'
        ]);
        $currentData = $response->json()['pois'] ?? [];
        $allData = array_merge($allData, $currentData);
        $start += $pageSize;
    } while (count($currentData) === (int)$pageSize); // 若返回数据不足pageSize，说明已无更多数据
    dd($allData);
    return $allData;
//    $list = [];
//    foreach ($response->json()['pois'] as $item) {
//        if (Str::contains($item['name'], '富力现代城')) {
//            $list[] = $item['city'] . $item['county'] . '-' . $item['name'] . $item['lonlat'];
//        }
//    }
//    dd($list);
});

