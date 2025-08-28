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
});


Route::get('test2', static function (\Illuminate\Http\Request $request) {
    // pdf转image url地址
    $pdfToImageUrl = 'https://demo.qiqippt.com/trans.php';
    $params = [
        'source' => 'https://qiniuoss.zcylovezhx.com/1756301544_9sjYLqwF6s.pdf',
    ];

    // 构建带参数的GET请求URL
    $url = $pdfToImageUrl . '?' . http_build_query($params);

    $ch = curl_init($url);
    // 设置返回结果不直接输出
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        throw new Exception('Curl error: ' . curl_error($ch));
    }
    curl_close($ch);

    $decodedResponse = json_decode($response, true);
    var_dump($decodedResponse);
    die;
});

