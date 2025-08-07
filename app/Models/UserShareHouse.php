<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserShareHouse extends Model
{
    protected $fillable = [
        'user_id',
        'contact_phone',
        'house_ids',
    ];

    protected $casts = [
        'house_ids'
    ];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
