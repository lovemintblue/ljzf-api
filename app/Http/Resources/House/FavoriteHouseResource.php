<?php

namespace App\Http\Resources\House;

use App\Http\Resources\Community\CommunityInfoResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteHouseResource extends JsonResource
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
            'deposit_method' => $this->deposit_method,
            'longitude' => $this->longitude,
            'latitude' => $this->latitude,
            'distance' => $distance,
            'is_show' => $this->is_show,
            'type' => $this->type,
            'is_locked' => $this->is_locked,
            'created_at' => $this->created_at->diffForHumans(),
            'favorite_at' => $this->favorite->created_at->diffForHumans(),
        ];
    }
}
