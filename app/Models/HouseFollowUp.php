<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property mixed $house
 * @property bool $is_punished
 * @property bool $is_processed
 */
class HouseFollowUp extends Model
{
    protected $guarded = [];
    
    protected $casts = [
        'is_punished' => 'boolean',
        'is_processed' => 'boolean',
    ];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo
     */
    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }
}
