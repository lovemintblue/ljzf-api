<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Community extends Model
{
    protected $guarded = [];

    /**
     * 关联商圈
     * @return BelongsTo
     */
    public function businessDistrict(): BelongsTo
    {
        return $this->belongsTo(BusinessDistrict::class);
    }
}
