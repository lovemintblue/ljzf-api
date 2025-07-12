<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property mixed $id
 */
class Shop extends Model
{
    protected $guarded = [];

    protected $casts = [
        'images' => 'json',
        'facility_ids' => 'json',
        'industry_ids' => 'json',
    ];

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

    /**
     * 关联商圈
     * @return BelongsTo
     */
    public function businessDistrict(): BelongsTo
    {
        return $this->belongsTo(BusinessDistrict::class);
    }
}
