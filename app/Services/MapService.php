<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MapService
{
    public static array $mapKeys = [
        'LBLBZ-AUCO3-2F23R-OQLFS-OY6BT-EDBWC',
    ];
    public string $key;

    public function __construct()
    {
        $this->key = 'LBLBZ-AUCO3-2F23R-OQLFS-OY6BT-EDBWC';
    }

    /**
     * 根据地址解析经纬度
     * @param string $address
     * @return array|mixed
     * @throws ConnectionException
     */
    public function geoCoder(string $address): mixed
    {
        // 根据地址解析经纬度
        $api = "https://apis.map.qq.com/ws/geocoder/v1/?address=$address&key=$this->key";
        $data = Http::get($api)->json();

        logger('打印地址解析经纬度');
        logger($data);

        if ((int)$data['status'] === 0) {
            return $data['result'];
        }

        return [];
    }

    /**
     * 解析ip
     * @param string $ip
     * @return array|mixed
     * @throws ConnectionException
     */
    public function ip(string $ip): mixed
    {
        $api = "https://apis.map.qq.com/ws/location/v1/ip?ip=$ip&key=$this->key";
        $data = Http::get($api)->json();
        if ((int)$data['status'] === 0) {
            return $data['result'];
        }
        Log::info('打印IP信息');
        Log::info(json_encode($data, JSON_UNESCAPED_UNICODE));
        return [];
    }
}
