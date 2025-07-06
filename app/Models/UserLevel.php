<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    public $timestamps = false;
    protected $guarded = [];
}
