<?php

namespace App\Observers;

use App\Models\House;
use Carbon\Carbon;

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
        if ($oldIsShow !== $house->is_show && (int)$house->is_show === 0) {
            $house->hidden_at = Carbon::now();
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
