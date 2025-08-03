<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Random\RandomException;

/**
 * @property mixed $total_amount
 * @property mixed|string $no
 */
class UserLevelOrder extends Model
{
    protected $guarded = [];

    /**
     * @return string
     * @throws RandomException
     */
    public static function generateUniqueNO(): string
    {
        $prefix = date('YmdHis');
        do {
            $no = $prefix . str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::query()->where('no', $no)->exists());
        return $no;
    }

    /**
     * 关联用户
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 关联用户等级
     * @return BelongsTo
     */
    public function userLevel(): BelongsTo
    {
        return $this->belongsTo(UserLevel::class);
    }
}
