<?php
/**
 * 房源浏览记录 Controller
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\HouseViewHistory\HouseViewHistoryResource;
use App\Models\House;
use App\Models\HouseViewHistory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HouseViewHistoriesController extends Controller
{
    /**
     * 列表
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $houseViewHistories = HouseViewHistory::query()
            ->whereBelongsTo($user)
            ->latest()
            ->paginate();

        return HouseViewHistoryResource::collection($houseViewHistories);
    }
}
