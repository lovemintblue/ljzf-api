<?php

namespace App\Http\Resources\House;

use App\Http\Resources\Community\CommunityInfoResource;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HouseEditInfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $images = collect($this->images)->map(function ($image) {
            return [
                'path' => $image,
                'url' => formatUrl($image)
            ];
        });
        return [
            'id' => $this->id,
            'no' => $this->no,
            'video' => [
                'path' => $this->video,
                'url' => formatUrl($this->video),
            ],
            'cover_image' => [
                'path' => $this->cover_image,
                'url' => formatUrl($this->cover_image),
            ],
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
            'community' => $this->community,
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
            'unit' => $this->unit,
            'deposit_method' => $this->deposit_method,
            'backup_contact_name' => $this->backup_contact_name,
            'backup_contact_phone' => $this->backup_contact_phone,
            'viewing_method' => $this->viewing_method,
            'is_show' => $this->is_show,
            'created_at' => $this->created_at->format('Y-m-d'),
        ];
    }
}
