<?php
/**
 * 通知
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Notification\NotificationInfoResource;
use App\Http\Resources\Notification\NotificationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationsController extends Controller
{
    /**
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $notifications = $user->notifications()->orderByDesc('created_at')->paginate();
        return NotificationResource::collection($notifications);
    }

    /**
     * 详情
     * @param Request $request
     * @param $id
     * @return NotificationInfoResource
     */
    public function show(Request $request, $id): NotificationInfoResource
    {
        $user = $request->user();
        $notification = $user->notifications()->findOrFail($id);
        
        // 标记为已读
        if (!$notification->read_at) {
            $notification->markAsRead();
        }
        
        return new NotificationInfoResource($notification);
    }

    /**
     * 标记为已读
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        $notification = $user->notifications()->findOrFail($id);
        
        $notification->markAsRead();
        
        return response()->json(['message' => '标记成功']);
    }

    /**
     * 标记全部为已读
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllAsRead(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        $user->unreadNotifications()->update(['read_at' => now()]);
        
        return response()->json(['message' => '全部已读']);
    }

    /**
     * 未读数量
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unreadCount(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        $count = $user->unreadNotifications()->count();
        
        return response()->json(['count' => $count]);
    }
}
