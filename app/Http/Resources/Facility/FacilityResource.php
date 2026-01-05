<?php

namespace App\Http\Resources\Facility;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $icon
 * @property mixed $selected_icon
 * @property mixed $name
 */
class FacilityResource extends JsonResource
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
            'icon' => formatUrl($this->icon),
            'selected_icon' => formatUrl($this->selected_icon),
            'name' => $this->name,
        ];
    }
}
