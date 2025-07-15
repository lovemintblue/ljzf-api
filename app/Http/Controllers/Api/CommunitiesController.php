<?php
/**
 * 小区 Controller
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Community\CommunityResource;
use App\Models\Community;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CommunitiesController extends Controller
{
    /**
     * 列表
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $keyword = $request->input('keyword', '');
        $builder = Community::query()->latest();
        if (!empty($keyword)) {
            $builder = $builder->whereLike('name', '%' . $keyword . '%');
        }
        $communities = $builder->paginate();
        return CommunityResource::collection($communities);
    }
}
