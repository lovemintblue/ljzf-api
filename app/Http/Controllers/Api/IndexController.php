<?php
/**
 * 首页
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\House;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    /**
     * 统计数据
     * @return JsonResponse
     */
    public function statistics(): JsonResponse
    {
        $todayHouseCount = House::query()->whereDate('created_at', today())->count();
        $todayHiddenHouseCount = House::query()
            ->where('is_show', 0)
            ->whereDate('hidden_at', today())->count();
        $data = [
            'today_house_count' => $todayHouseCount,
            'today_hidden_house_count' => $todayHiddenHouseCount,
            'today_shop_count' => 0,
            'today_hidden_shop_count' => 0,
        ];
        return response()->json($data);
    }
}
