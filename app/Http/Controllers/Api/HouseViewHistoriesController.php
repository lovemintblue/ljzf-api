<?php
/**
 * 房源浏览记录 Controller
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\HouseViewHistory\HouseViewHistoryResource;
use App\Models\House;
use App\Models\HouseViewHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HouseViewHistoriesController extends Controller
{
    /**
     * 列表
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $houseViewHistories = HouseViewHistory::query()
            ->whereBelongsTo($user)
            ->withWhereHas('house', function ($query) {
                $query->where('is_show', 1);
            })
            ->latest()
            ->get();
        // 按日期分组（格式为Y-m-d）
        $houseViewHistories = $houseViewHistories->groupBy(function ($item) {
            return $item->created_at->format('Y-m-d');
        });

        $houseViewHistories = $houseViewHistories->map(function ($item) {
            dd($item);
        });

        return response()->json([
            'data' => $houseViewHistories
        ]);
    }
}
