<?php

namespace App\Http\Resources\Notification;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $data
 * @property mixed $read_at
 * @property mixed $created_at
 */
class NotificationInfoResource extends JsonResource
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
            'data' => $this->data,
            'read_at' => formatAt($this->read_at),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
