<?php

namespace App\Observers;

use App\Models\House;

class HouseObserver
{
    /**
     * Handle the House "created" event.
     */
    public function creating(House $house): void
    {
        $house->no = 'H' . $house->id;
    }

    /**
     * Handle the House "saving" event.
     */
    public function saving(House $house): void
    {
        if (!$house->no) {
            $house->no = 'H' . $house->id;
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
