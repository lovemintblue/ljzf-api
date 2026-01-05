<?php

namespace App\Observers;

use App\Models\House;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class HouseObserver
{
    /**
     * Handle the House "created" event.
     */
    public function creating(House $house): void
    {
        $house->no = 'H' . $house->id;
    }

    public function created(House $house): void
    {
        $house->no = 'H' . $house->id;
        $house->saveQuietly();
    }

    /**
     * Handle the House "saving" event.
     */
    public function saving(House $house): void
    {
        $house->no = 'H' . $house->id;

        $oldIsShow = $house->getOriginal('is_show');
        if ((int)$oldIsShow !== (int)$house->is_show && (int)$house->is_show === 0) {
            $house->hidden_at = Carbon::now();
            Log::info('--修改了隐藏时间--');
        } else {
            Log::info('--未修改隐藏时间--');
        }
    }

    /**
     * Handle the House "deleted" event.
     */
    public function deleted(House $house): void
    {
        //
    }

    /**
     * Handle the House "restored" event.
     */
    public function restored(House $house): void
    {
        //
    }

    /**
     * Handle the House "force deleted" event.
     */
    public function forceDeleted(House $house): void
    {
        //
    }
}
