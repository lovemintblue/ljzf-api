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
        return new NotificationInfoResource($notification);
    }
}
