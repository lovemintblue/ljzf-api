<?php
/**
 * 微信小程序 Service
 */

namespace App\Services;

use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use EasyWeChat\MiniApp\Application;

class MiniAppService
{
    /**
     * @var mixed
     */
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
     * @throws InvalidArgumentException
     */
    public function setApp(): void
    {
        $config = [
            'app_id' => 'wx2a3e44e8b256b4ea',
            'secret' => 'bcbf66fc5a1099a7a8ad279a3f931a84',
        ];
        $this->app = new Application($config);
    }
}
