<?php
/**
 * 行业
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Industry\IndustryResource;
use App\Models\Industry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IndustriesController extends Controller
{
    /**
     * 行业
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $industries = Industry::query()->get();
        IndustryResource::wrap('data');
        return IndustryResource::collection($industries);
    }
}
