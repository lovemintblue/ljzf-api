<?php

namespace App\Http\Resources\Community;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $name
 * @property mixed $address
 * @property mixed $longitude
 * @property mixed $latitude
 * @property mixed $album
 */
class CommunityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $album = collect($this->album)->map(function ($item) {
            return formatUrl($item);
        });
        return [
            'id' => $this->id,
            'name' => $this->name,
            'album' => $album,
            'address' => $this->address,
            'longitude' => $this->longitude,
            'latitude' => $this->latitude,
            'is_commercial_housing' => $this->is_commercial_housing,
            'is_apartment' => $this->is_apartment,
        ];
    }
}
