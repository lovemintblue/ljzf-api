<?php
/**
 * 微信支付 Service
 */

namespace App\Services;

use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use EasyWeChat\Pay\Application;

class PaymentService
{
    protected mixed $app;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct()
    {
        $this->setApp();
    }

    /**
     * @return mixed
     */
    public function getApp(): mixed
    {
        return $this->app;
    }

    /**
     * @return void
     *
     * @throws InvalidArgumentException
     */
    public function setApp(): void
    {
        $config = [
            'mch_id' => 1608322473,
            'private_key' => storage_path('/wechat/cert/apiclient_key.pem'),
            'certificate' => storage_path('/wechat/cert/apiclient_cert.pem'),
            // v3 API key
            'secret_key' => 'dd41a764d75748c6ea5b276bd1a724b4',
            // v2 API key
            'v2_secret_key' => '',
            'platform_certs' => [
                'PUB_KEY_ID_0117144350432025041700331608003004' => storage_path('wechat/cert/pub_key.pem')
            ],
        ];
        $this->app = new Application($config);
    }
}
