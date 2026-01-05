<?php

namespace App\Http\Resources\House;

use App\Http\Resources\Community\CommunityInfoResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $images
 * @property mixed $id
 * @property mixed $title
 * @property mixed $room_count
 * @property mixed $contact_name
 * @property mixed $contact_phone
 * @property mixed $living_room_count
 * @property mixed $bathroom_count
 * @property mixed $area
 * @property mixed $rent_price
 * @property mixed $payment_method
 * @property mixed $min_rental_period
 * @property mixed $community
 * @property mixed $address
 * @property mixed $orientation
 * @property mixed $floor
 * @property mixed $total_floors
 * @property mixed $created_at
 * @property mixed $video
 * @property mixed $cover_image
 * @property mixed $no
 * @property mixed $longitude
 * @property mixed $latitude
 * @property mixed $distance
 * @property mixed $unit
 * @property mixed $building_number
 * @property mixed $room_number
 * @property mixed $deposit_method
 * @property mixed $is_show
 * @property mixed $is_locked
 * @property mixed $type
 * @property mixed $audit_status
 * @property mixed $is_top
 * @property mixed $top_at
 */
class HouseResource extends JsonResource
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
        $distance = round($this->distance / 1000, 1);
        return [
            'id' => $this->id,
            'no' => $this->no,
            'title' => $this->title,
            'video' => formatUrl($this->video),
            'cover_image' => formatUrl($this->cover_image),
            'images' => $images,
            'room_count' => $this->room_count,
            'contact_name' => $this->contact_name,
            'contact_phone' => $this->contact_phone,
            'living_room_count' => $this->living_room_count,
            'bathroom_count' => $this->bathroom_count,
            'area' => (int)$this->area,
            'rent_price' => (int)$this->rent_price,
            'payment_method' => $this->payment_method,
            'min_rental_period' => $this->min_rental_period,
            'community' => new CommunityInfoResource($this->community),
            'address' => $this->address,
            'orientation' => $this->orientation,
            'floor' => $this->floor,
            'total_floors' => $this->total_floors,
            'unit' => $this->unit,
            'building_number' => $this->building_number,
            'room_number' => $this->room_number,
            'deposit_method' => $this->deposit_method,
            'longitude' => $this->longitude,
            'latitude' => $this->latitude,
            'distance' => $distance,
            'is_show' => $this->is_show,
            'type' => $this->type,
            'audit_status' => $this->audit_status,
            'is_locked' => $this->is_locked,
            'is_top' => (bool)$this->is_top,
            'top_at' => $this->top_at,
            'created_at' => $this->created_at->diffForHumans(),
        ];
    }
}
