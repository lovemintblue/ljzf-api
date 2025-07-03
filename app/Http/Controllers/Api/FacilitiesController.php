<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Facility\FacilityResource;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FacilitiesController extends Controller
{
    /**
     * 列表
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $type = $request->input('type', 0);
        $facilities = Facility::query()
            ->where('type', $type)
            ->get();
        FacilityResource::wrap('data');
        return FacilityResource::collection($facilities);
    }
}
