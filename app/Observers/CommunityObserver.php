<?php

namespace App\Observers;

use App\Models\Community;
use App\Models\House;
use App\Services\MapService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;

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
                Log::info('开始修改');
                $community->longitude = $address['location']['lng'];
                $community->latitude = $address['location']['lat'];
            }
        }
    }

    /**
     * saved
     * @param Community $community
     * @return void
     */
    public function saved(Community $community): void
    {
        // 如果经纬度发生变化，同步更新关联房源的经纬度
        if ($community->wasChanged(['longitude', 'latitude'])) {
            House::query()
                ->where('community_id', $community->id)
                ->update([
                    'longitude' => $community->longitude,
                    'latitude' => $community->latitude,
                ]);

            Log::info('小区经纬度已同步更新到关联房源', [
                'community_id' => $community->id,
                'longitude' => $community->longitude,
                'latitude' => $community->latitude
            ]);
        }
    }
}
