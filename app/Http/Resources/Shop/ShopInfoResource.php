<?php

namespace App\Http\Resources\Shop;

use App\Models\Facility;
use App\Models\Industry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $title
 * @property mixed $type
 * @property mixed $area
 * @property mixed $floor
 * @property mixed $total_floors
 * @property mixed $renovation
 * @property mixed $rent_price
 * @property mixed $deposit_price
 * @property mixed $images
 * @property mixed $property_fee
 * @property mixed $payment_method
 * @property mixed $contact_name
 * @property mixed $contact_phone
 * @property mixed $business_district
 * @property mixed $address
 * @property mixed $surroundings
 * @property mixed $description
 * @property mixed $facility_ids
 * @property mixed $industry_ids
 * @property mixed $business_district_id
 * @property mixed $province
 * @property mixed $city
 * @property mixed $district
 */
class ShopInfoResource extends JsonResource
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
        $industries = Industry::query()->whereIn('id', $this->industry_ids)->pluck('name')->toArray();
        $isFavor = 0;
        if ($user->favoriteShops()->where('shop_id', $this->id)->first()) {
            $isFavor = 1;
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'area' => $this->area,
            'floor' => $this->floor,
            'total_floors' => $this->total_floors,
            'renovation' => $this->renovation,
            'rent_price' => $this->rent_price,
            'deposit_price' => $this->deposit_price,
            'property_fee' => $this->property_fee,
            'payment_method' => $this->payment_method,
            'contact_name' => $this->contact_name,
            'contact_phone' => $this->contact_phone,
            'images' => $images,
            'business_district_id' => $this->business_district_id,
            'province' => $this->province,
            'city' => $this->city,
            'district' => $this->district,
            'address' => $this->address,
            'surroundings' => $this->surroundings,
            'description' => $this->description,
            'facility_ids' => $this->facility_ids,
            'industries' => $industries,
            'industry_ids' => $this->industry_ids,
            'is_favor' => $isFavor,
        ];
    }
}
