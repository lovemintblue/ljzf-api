<?php
/**
 * 商圈 Controller
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BusinessDistrict\BusinessDistrictResource;
use App\Models\BusinessDistrict;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;


class BusinessDistrictsController extends Controller
{
    /**
     * 列表
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $keyword = $request->input('keyword', '');
        $builder = BusinessDistrict::query();
        if (!empty($keyword)) {
            $builder = $builder->where('name', 'like', '%' . $keyword . '%');
        }
        $businessDistricts = $builder->get();
        BusinessDistrictResource::wrap('data');
        return BusinessDistrictResource::collection($businessDistricts);
    }
}
