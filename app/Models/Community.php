<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int|mixed $business_district_id
 * @property mixed $name
 * @property mixed $province
 * @property mixed $city
 * @property mixed $district
 * @property mixed $address
 * @property mixed $longitude
 * @property mixed $latitude
 * @property array|mixed $business_district_ids
 */
class Community extends Model
{
    protected $guarded = [];

    protected $casts = [
        'business_district_ids' => 'json',
        'album' => 'json',
    ];

    /**
     * 关联商圈
     * @return BelongsTo
     */
    public function businessDistrict(): BelongsTo
    {
        return $this->belongsTo(BusinessDistrict::class);
    }
}
