<?php

namespace App\Http\Resources\User;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $avatar
 * @property mixed $nickname
 * @property mixed $phone
 * @property mixed $houses_count
 * @property mixed $favorite_houses_count
 * @property mixed $favorite_shops_count
 * @property mixed $notifications_count
 * @property mixed $is_staff
 * @property mixed $userLevel
 * @property mixed $view_phone_count
 * @property mixed $expired_at
 */
class UserInfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $favoriteCount = $this->favorite_houses_count + $this->favorite_shops_count;
        $vipSurplusDays = 0;

        if (empty($this->expired_at)) {
            $expiredAt = Carbon::parse($this->expired_at);
            $vipSurplusDays = $expiredAt->diffInDays(Carbon::now());
        }

        return [
            'id' => $this->id,
            'user_level' => $this->userLevel,
            'avatar' => formatUrl($this->avatar),
            'nickname' => $this->nickname,
            'phone' => $this->phone,
            'favorite_count' => $favoriteCount,
            'houses_count' => $this->houses_count,
            'notifications_count' => $this->notifications_count,
            'view_phone_count' => $this->view_phone_count,
            'is_staff' => $this->is_staff,
            'expired_at' => $this->expired_at,
            'vip_surplus_days' => $vipSurplusDays,
        ];
    }
}
