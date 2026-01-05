<?php

namespace App\Http\Resources\Carousel;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $image
 * @property mixed $url
 */
class CarouselResource extends JsonResource
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
            'image' => formatUrl($this->image),
            'url' => $this->url
        ];
    }
}
