<?php

namespace App\Models;

use App\Observers\ShopObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Random\RandomException;

/**
 * @property mixed $id
 */
#[ObservedBy(ShopObserver::class)]
class Shop extends Model
{
    protected $guarded = [];

    protected $casts = [
        'images' => 'json',
        'facility_ids' => 'json',
        'industry_ids' => 'json',
    ];

    /**
     * @return string
     * @throws RandomException
     */
    public static function generateUniqueNO(): string
    {
        $prefix = 'S' . date('YmdHis');
        do {
            $no = $prefix . str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::query()->where('no', $no)->exists());
        return $no;
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 关联小区
     * @return BelongsTo
     */
    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }
}
