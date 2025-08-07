<?php

namespace App\Http\Resources\User;

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
        ];
    }
}
