<?php

namespace App\Http\Resources\Shop;

use App\Http\Resources\Community\CommunityInfoResource;
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
 * @property mixed $room_number
 * @property mixed $total_floors
 * @property mixed $renovation
 * @property mixed $rent_price
 * @property mixed $deposit_price
 * @property mixed $images
 * @property mixed $property_fee
 * @property mixed $payment_method
 * @property mixed $contact_name
 * @property mixed $contact_phone
 * @property mixed $backup_contact_name
 * @property mixed $backup_contact_phone
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
 * @property mixed $community
 * @property mixed $created_at
 * @property mixed $status
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
        // 优先从 token 获取用户，如果没有则从 URL 参数获取
        $user = $request->user();
        if (!$user && $request->input('user_id')) {
            $user = \App\Models\User::find($request->input('user_id'));
        }
        
        $images = collect($this->images)->map(function ($image) {
            return [
                'path' => $image,
                'url' => formatUrl($image)
            ];
        });
        $industries = Industry::query()->whereIn('id', $this->industry_ids)->pluck('name')->toArray();
        
        // 配套设施（与房源格式一致）
        if (!empty($this->facility_ids)) {
            $facilities = Facility::query()->whereIn('id', $this->facility_ids)->get()->filter(function ($facility) {
                if (!empty($facility->type) && in_array(1, $facility->type)) {  // type=1 是商铺配套
                    return $facility;
                }
            })->map(function ($facility) {
                return [
                    'icon' => formatUrl($facility->icon),
                    'name' => $facility->name,
                ];
            });
        }
        
        $isFavor = 0;
        if ($user && $user->favoriteShops()->find($this->id)) {
            $isFavor = 1;
        }

        return [
            'id' => $this->id,
            'video' => formatUrl($this->video),
            'cover_image' => formatUrl($this->cover_image),
            'title' => $this->title,
            'type' => $this->type,
            'rental_type' => $this->rental_type ?? 0,
            'area' => $this->area,
            'floor_height' => $this->floor_height,
            'frontage' => $this->frontage,
            'depth' => $this->depth,
            'floor' => $this->floor,
            'room_number' => $this->room_number,
            'total_floors' => $this->total_floors,
            'renovation' => $this->renovation,
            'rent_price' => (int)$this->rent_price,
            'deposit_price' => $this->deposit_price,
            'property_fee' => $this->property_fee,
            'payment_method' => $this->payment_method,
            'contact_name' => $this->contact_name,
            'contact_phone' => $this->contact_phone,
            'backup_contact_name' => $this->backup_contact_name,
            'backup_contact_phone' => $this->backup_contact_phone,
            'images' => $images,
            'business_district_id' => $this->business_district_id,
            'province' => $this->province,
            'city' => $this->city,
            'district' => $this->district,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'surroundings' => $this->surroundings,
            'description' => $this->description,
            'facility_ids' => $this->facility_ids,
            'facilities' => $facilities ?? [],
            'industries' => $industries,
            'suitable_businesses' => $this->suitable_businesses ?? [],
            'community' => new CommunityInfoResource($this->community),
            'industry_ids' => $this->industry_ids,
            'status' => $this->status,
            'is_favor' => $isFavor,
            'created_at' => $this->created_at->format('Y-m-d'),
        ];
    }
}
