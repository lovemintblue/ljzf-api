<?php

namespace App\Http\Resources\UserLevel;

use App\Models\UserLevel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $name
 * @property mixed $level
 * @property mixed $price
 * @property mixed $original_price
 * @property mixed $is_recommend
 * @property mixed $is_good_value
 * @property mixed $privilege
 * @property mixed $cycle
 */
class UserLevelInfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $privilege = [];
        foreach ($this->privilege as $item) {
            $privilege[] = UserLevel::$privilegeMap[$item];
        }
        $cycle = UserLevel::$cycleMap[$this->cycle];
        return [
            'id' => $this->id,
            'name' => $this->name,
            'level' => $this->level,
            'price' => $this->price,
            'original_price' => $this->original_price,
            'is_recommend' => $this->is_recommend,
            'is_good_value' => $this->is_good_value,
            'cycle' => $cycle,
            'privilege' => $privilege,
        ];
    }
}
