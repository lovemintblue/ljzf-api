<?php

namespace App\Observers;

use App\Models\Community;
use App\Services\MapService;

class CommunityObserver
{
    /**
     * Handle the Community "saving" event.
     */
    public function saving(Community $community): void
    {
        if (!empty($community->address)) {
            $address = (new MapService())->geoCoder($community->address);
        }
    }
}
