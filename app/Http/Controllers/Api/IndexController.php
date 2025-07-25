<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function statistics()
    {
        $data = [
            'today_add_house_count' => 0,
        ];
    }
}
