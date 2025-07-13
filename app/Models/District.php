<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'pinyin' => 'json',
        'location' => 'json',
        'license_plate_region' => 'json'
    ];
}
