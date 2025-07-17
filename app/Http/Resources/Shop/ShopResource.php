<?php

namespace App\Http\Resources\Shop;

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
            'area' => $this->area,
            'rent_price' => $this->rent_price,
            'deposit_price' => $this->deposit_price,
            'property_fee' => $this->property_fee,
            'payment_method' => $this->payment_method,
            'images' => $images,
            'province' => $this->province,
            'city' => $this->city,
            'district' => $this->district,
            'address' => $this->address,
            'surroundings' => $this->surroundings,
            'description' => $this->description,
            'industries' => $industries,
        ];
    }
}
