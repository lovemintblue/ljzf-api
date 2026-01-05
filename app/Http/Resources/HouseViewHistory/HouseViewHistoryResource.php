<?php

namespace App\Http\Resources\HouseViewHistory;

use App\Http\Resources\House\HouseInfoResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $house
 * @property mixed $created_at
 */
class HouseViewHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'house' => new HouseInfoResource($this->house),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
