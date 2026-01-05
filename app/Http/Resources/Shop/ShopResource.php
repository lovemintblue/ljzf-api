<?php

namespace App\Http\Resources\Shop;

use App\Http\Resources\Community\CommunityInfoResource;
use App\Models\Facility;
use App\Models\Industry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $images
 * @property mixed $id
 * @property mixed $title
 * @property mixed $type
 * @property mixed $area
 * @property mixed $floor
 * @property mixed $room_number
 * @property mixed $rent_price
 * @property mixed $deposit_price
 * @property mixed $property_fee
 * @property mixed $payment_method
 * @property mixed $address
 * @property mixed $surroundings
 * @property mixed $description
 * @property mixed $facility_ids
 * @property mixed $industry_ids
 * @property mixed $businessDistrict
 * @property mixed $province
 * @property mixed $city
 * @property mixed $district
 * @property mixed $video
 * @property mixed $cover_image
 * @property mixed $community
 * @property mixed $created_at
 * @property mixed $audit_status
 * @property mixed $is_show
 */
class ShopResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $images = collect($this->images)->map(function ($image) {
            return formatUrl($image);
        });
        $industries = Industry::query()->whereIn('id', $this->industry_ids)->pluck('name')->toArray();
        return [
            'id' => $this->id,
            'video' => formatUrl($this->video),
            'cover_image' => formatUrl($this->cover_image),
            'title' => $this->title,
            'type' => $this->type,
            'rental_type' => $this->rental_type ?? 0,
            'area' => $this->area,
            'floor' => $this->floor,
            'room_number' => $this->room_number,
            'floor_height' => $this->floor_height,
            'frontage' => $this->frontage,
            'depth' => $this->depth,
            'rent_price' => (int)$this->rent_price,
            'deposit_price' => $this->deposit_price,
            'property_fee' => $this->property_fee,
            'payment_method' => $this->payment_method,
            'images' => $images,
            'province' => $this->province,
            'city' => $this->city,
            'district' => $this->district,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'community' => new CommunityInfoResource($this->community),
            'surroundings' => $this->surroundings,
            'description' => $this->description,
            'industries' => $industries,
            'suitable_businesses' => $this->suitable_businesses ?? [],
            'audit_status' => $this->audit_status,
            'is_show' => $this->is_show,
            'created_at' => $this->created_at->diffForHumans(),
        ];
    }
}
