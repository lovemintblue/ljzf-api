<?php

namespace App\Http\Resources\House;

use App\Http\Resources\Community\CommunityInfoResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $images
 * @property mixed $title
 * @property mixed $contact_name
 * @property mixed $contact_phone
 * @property mixed $type
 * @property mixed $room_count
 * @property mixed $living_room_count
 * @property mixed $bathroom_count
 * @property mixed $area
 * @property mixed $floor
 * @property mixed $total_floors
 * @property mixed $orientation
 * @property mixed $renovation
 * @property mixed $rent_price
 * @property mixed $payment_method
 * @property mixed $min_rental_period
 * @property mixed $community
 * @property mixed $address
 * @property mixed $facility_ids
 * @property mixed $building_number
 * @property mixed $room_number
 * @property mixed $province
 * @property mixed $city
 * @property mixed $district
 * @property mixed $created_at
 * @property mixed $status
 * @property mixed $longitude
 * @property mixed $latitude
 */
class HouseInfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $images = collect($this->images)->map(function ($image) {
            return [
                'path' => $image,
                'url' => formatUrl($image)
            ];
        });
        $isFavor = 0;
        if ($user->favoriteHouses()->where('house_id', $this->id)->first()) {
            $isFavor = 1;
        }

        return [
            'id' => $this->id,
            'video' => formatUrl($this->video),
            'cover_image' => formatUrl($this->cover_image),
            'title' => $this->title,
            'contact_name' => $this->contact_name,
            'contact_phone' => $this->contact_phone,
            'type' => $this->type,
            'room_count' => $this->room_count,
            'living_room_count' => $this->living_room_count,
            'bathroom_count' => $this->bathroom_count,
            'area' => $this->area,
            'floor' => $this->floor,
            'total_floors' => $this->total_floors,
            'orientation' => $this->orientation,
            'renovation' => $this->renovation,
            'rent_price' => (int)$this->rent_price,
            'payment_method' => $this->payment_method,
            'min_rental_period' => $this->min_rental_period,
            'images' => $images,
            'community' => new CommunityInfoResource($this->community),
            'province' => $this->province,
            'city' => $this->city,
            'district' => $this->district,
            'address' => $this->address,
            'facility_ids' => $this->facility_ids,
            'building_number' => $this->building_number,
            'room_number' => $this->room_number,
            'status' => $this->status,
            'longitude' => $this->longitude,
            'latitude' => $this->latitude,
            'is_favor' => $isFavor,
            'created_at' => $this->created_at->format('Y-m-d'),
        ];
    }
}
