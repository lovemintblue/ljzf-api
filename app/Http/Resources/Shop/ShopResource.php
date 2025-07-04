<?php

namespace App\Http\Resources\Shop;

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
 * @property mixed $business_district
 * @property mixed $address
 * @property mixed $surroundings
 * @property mixed $description
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
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'area' => $this->area,
            'rent_price' => $this->rent_price,
            'deposit_price' => $this->deposit_price,
            'property_fee' => $this->property_fee,
            'payment_method' => $this->payment_method,
            'images' => $images,
            'business_district' => $this->business_district,
            'address' => $this->address,
            'surroundings' => $this->surroundings,
            'description' => $this->description,
        ];
    }
}
