<?php
/**
 * 公告
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Notice\NoticeInfoResource;
use App\Http\Resources\Notice\NoticeResource;
use App\Models\Notice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NoticesController extends Controller
{
    /**
     * 列表
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $notices = Notice::query()
            ->where('status', 1)
            ->latest()
            ->get();
        NoticeResource::wrap('data');
        return NoticeResource::collection($notices);
    }

    /**
     * 详情
     * @param Notice $notice
     * @return NoticeInfoResource
     */
    public function show(Notice $notice): NoticeInfoResource
    {
        return new NoticeInfoResource($notice);
    }
}
