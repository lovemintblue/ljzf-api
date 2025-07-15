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
}
