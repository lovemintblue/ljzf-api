<?php

namespace App\Observers;

use App\Models\Shop;

class ShopObserver
{
    /**
     * Handle the Shop "created" event.
     */
    public function creating(Shop $shop): void
    {
        $shop->no = Shop::generateUniqueNO();
    }

    /**
     * Handle the Shop "updated" event.
     */
    public function saving(Shop $shop): void
    {
        if (!$shop->no) {
            $shop->no = Shop::generateUniqueNO();
        }
    }

    /**
     * Handle the Shop "deleted" event.
     */
    public function deleted(Shop $shop): void
    {
        //
    }

    /**
     * Handle the Shop "restored" event.
     */
    public function restored(Shop $shop): void
    {
        //
    }

    /**
     * Handle the Shop "force deleted" event.
     */
    public function forceDeleted(Shop $shop): void
    {
        //
    }
}
