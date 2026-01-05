<?php

namespace App\Http\Resources\House;

use App\Http\Resources\Community\CommunityInfoResource;
use App\Models\Facility;
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
 * @property mixed $video
 * @property mixed $cover_image
 * @property mixed $no
 * @property mixed $unit
 * @property mixed $deposit_method
 * @property mixed $backup_contact_name
 * @property mixed $backup_contact_phone
 * @property mixed $viewing_method
 * @property mixed $is_show
 * @property mixed $is_top
 * @property mixed $top_at
 * @property mixed $watermark_images
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
        // 优先从 token 获取用户，如果没有则从 URL 参数获取
        $user = $request->user();
        if (!$user && $request->input('user_id')) {
            $user = \App\Models\User::find($request->input('user_id'));
        }

        $images = [];

        if (!empty($this->watermark_images)) {
            $images = collect($this->watermark_images)->map(function ($image) {
                return [
                    'path' => $image,
                    'url' => formatUrl($image)
                ];
            });
        } else {
            $images = collect($this->images)->map(function ($image) {
                return [
                    'path' => $image,
                    'url' => formatUrl($image)
                ];
            });
        }



        $isFavor = 0;
        if ($user && $user->favoriteHouses()->find($this->id)) {
            $isFavor = 1;
        }

        if (!empty($this->facility_ids)){
            $facilities = Facility::query()->whereIn('id',$this->facility_ids)->get()->filter(function ($facility) {
                if (!empty($facility->type) && in_array(0,$facility->type)){
                    return $facility;
                }
            })->map(function ($facility) {
                return [
                    'icon' => formatUrl($facility->icon),
                    'name' => $facility->name,
                ];
            });
        }

        $video = $this->video;
        if (!empty($this->watermark_video)) {
            $video = $this->watermark_video;
        }

        return [
            'id' => $this->id,
            'no' => $this->no,
            'video' => formatUrl($video),
            'cover_image' => formatUrl($this->cover_image),
            'title' => $this->title,
            'contact_name' => $this->contact_name,
            'contact_phone' => $this->contact_phone,
            'type' => $this->type,
            'room_count' => $this->room_count,
            'living_room_count' => $this->living_room_count,
            'bathroom_count' => $this->bathroom_count,
            'area' => (int)$this->area,
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
            'facilities' => $facilities ?? [],
            'building_number' => $this->building_number,
            'room_number' => $this->room_number,
            'status' => $this->status,
            'longitude' => $this->longitude,
            'latitude' => $this->latitude,
            'unit' => $this->unit,
            'deposit_method' => $this->deposit_method,
            'is_favor' => $isFavor,
            'backup_contact_name' => $this->backup_contact_name,
            'backup_contact_phone' => $this->backup_contact_phone,
            'viewing_method' => $this->viewing_method,
            'is_show' => $this->is_show,
            'is_top' => (bool)$this->is_top,
            'top_at' => $this->top_at ? $this->top_at->format('Y-m-d H:i:s') : null,
            'created_at' => $this->created_at->format('Y-m-d'),
        ];
    }
}
