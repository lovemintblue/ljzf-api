<?php

namespace App\Observers;

use App\Models\Community;
use App\Services\MapService;
use Illuminate\Http\Client\ConnectionException;

class CommunityObserver
{
    /**
     * saving
     * @param Community $community
     * @return void
     * @throws ConnectionException
     */
    public function saving(Community $community): void
    {
        if ($community->address && $community->isDirty('address')) {
            $address = (new MapService())->geoCoder($community->address);
            if (count($address) > 0) {
                $community->longitude = $address['location']['lng'];
                $community->latitude = $address['location']['lat'];
            }
        }
    }
}
