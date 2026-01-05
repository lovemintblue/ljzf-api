<?php

namespace App\Models;

use App\Observers\ShopObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Random\RandomException;

/**
 * @property mixed $id
 * @property int|mixed $audit_status
 * @property mixed|string $no
 * @property int $rental_type
 * @property float|null $floor_height
 * @property float|null $frontage
 * @property float|null $depth
 * @property string|null $description
 * @property string|null $latitude
 * @property string|null $longitude
 */
#[ObservedBy(ShopObserver::class)]
class Shop extends Model
{
    protected $guarded = [];

    /**
     * @var string[]
     */
    protected $casts = [
        'images' => 'json',
        'facility_ids' => 'json',
        'industry_ids' => 'json',
        'suitable_businesses' => 'json',
        'floor_height' => 'float',
        'frontage' => 'float',
        'depth' => 'float',
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
