<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed|string $name
 * @property int[]|mixed $type
 */
class Facility extends Model
{
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'type' => 'json'
    ];

    /**
     * 设置类型属性时确保为整数数组
     *
     * @param mixed $value
     * @return void
     */
    public function setTypeAttribute($value): void
    {
        if (is_array($value)) {
            $this->attributes['type'] = json_encode(array_map('intval', $value));
        } else {
            $this->attributes['type'] = json_encode([]);
        }
    }

    /**
     * 获取类型属性时确保为整数数组
     *
     * @param mixed $value
     * @return array
     */
    public function getTypeAttribute($value): array
    {
        if (is_null($value) || $value === '') {
            return [];
        }

        $decoded = json_decode($value, true);
        if (!is_array($decoded)) {
            return [];
        }

        return array_map('intval', $decoded);
    }
}
