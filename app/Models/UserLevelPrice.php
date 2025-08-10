<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property mixed $user_level_id
 * @property mixed $cycle
 * @property mixed $price
 */
class UserLevelPrice extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    /**
     * @return BelongsTo
     */
    public function userLevel(): BelongsTo
    {
        return $this->belongsTo(UserLevel::class);
    }
}
