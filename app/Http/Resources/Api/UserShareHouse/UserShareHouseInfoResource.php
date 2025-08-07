<?php

namespace App\Http\Resources\Api\UserShareHouse;

use App\Http\Resources\House\HouseResource;
use App\Http\Resources\User\UserInfoResource;
use App\Models\House;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $contact_phone
 * @property mixed $created_at
 * @property mixed $house_ids
 * @property mixed $user
 */
class UserShareHouseInfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $houses = House::query()->whereIn('id', $this->house_ids)->get();
        return [
            'id' => $this->id,
            'user' => new UserInfoResource($this->user),
            'contact_phone' => $this->contact_phone,
            'houses' => HouseResource::collection($houses),
            'created_at' => $this->created_at->format('Y-m-d'),
        ];
    }
}
