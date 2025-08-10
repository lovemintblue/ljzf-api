<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property mixed $name
 * @property mixed $level
 * @property mixed $price
 * @property mixed $original_price
 * @property mixed $is_recommend
 * @property mixed $is_good_value
 * @property mixed $cycle
 * @property mixed $privilege
 */
class UserLevel extends Model
{
    /**
     * @var array|string[]
     */
    public static array $privilegeMap = [
        0 => '特价房源查看',
        1 => '免中介费',
        2 => '专属客服',
        3 => '优先房源推荐',
        4 => '免费搬家服务',
    ];

    /**
     * @var array|string[]
     */
    public static array $cycleMap = [
        0 => '月',
        1 => '季',
        2 => '年'
    ];

    public $timestamps = false;
    protected $guarded = [];
    protected $casts = [
        'privilege' => 'json'
    ];

    /**
     * 等级价格
     * @return HasMany
     */
    public function userLevelPrices(): HasMany
    {
        return $this->hasMany(UserLevelPrice::class);
    }
}
