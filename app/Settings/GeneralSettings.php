<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{

    /**
     * 会员权益
     * @var string|null
     */
    public ?string $user_level_benefit;

    /**
     * 会员规则
     * @var string|null
     */
    public ?string $user_level_rule;

    /**
     * 会员其他说明
     * @var string|null
     */
    public ?string $user_level_other;

    /**
     * 加入我们
     * @var string|null
     */
    public ?string $join_us;

    /**
     * 公司介绍
     * @var string|null
     */
    public ?string $company_intro;

    /**
     * 隐私协议
     * @var string|null
     */
    public ?string $privacy_policy;

    /**
     * @return string
     */
    public static function group(): string
    {
        return 'general';
    }
}
