<?php

namespace App\Http\Resources\Community;

use App\Models\BusinessDistrict;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $image
 * @property mixed $name
 * @property mixed $built_year
 * @property mixed $address
 * @property mixed $property_fee
 * @property mixed $property_company
 * @property mixed $developer
 * @property mixed $building_count
 * @property mixed $house_count
 * @property mixed $average_sale_price
 * @property mixed $average_rent_price
 * @property mixed $longitude
 * @property mixed $latitude
 * @property mixed $total_floors
 */
class CommunityInfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $businessDistrict = [];
        if (!empty($this->business_district_ids) && count($this->business_district_ids) > 0) {
            $businessDistrict = BusinessDistrict::query()->whereIn('id', $this->business_district_ids)->pluck('name')->toArray();
        }

        return [
            'id' => $this->id,
            'image' => formatUrl($this->image),
            'name' => $this->name,
            'address' => $this->address,
            'built_year' => $this->built_year,
            'property_fee' => $this->property_fee,
            'property_company' => $this->property_company,
            'developer' => $this->developer,
            'building_count' => $this->building_count,
            'house_count' => $this->house_count,
            'average_rent_price' => $this->average_rent_price,
            'average_sale_price' => $this->average_sale_price,
            'longitude' => $this->longitude,
            'latitude' => $this->latitude,
            'total_floors' => $this->total_floors,
            'business_district' => $businessDistrict
        ];
    }
}
