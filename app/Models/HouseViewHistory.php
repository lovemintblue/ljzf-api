<?php
/**
 * 房源浏览记录
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HouseViewHistory extends Model
{
    public $guarded = [];

    /**
     * 关联用户
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
