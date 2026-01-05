<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InvalidRequestException;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserInfoResource;
use App\Models\UsersViewPhoneLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class UsersController extends Controller
{
    /**
     * 登录信息
     * @param Request $request
     * @return UserInfoResource
     */
    public function me(Request $request): UserInfoResource
    {
        $user = $request->user();
        $user->latest_visit_at = Carbon::now();
        $user->save();

        //查询当日查看次数
        $use_num = UsersViewPhoneLog::where('user_id',$user->id)->where('created_at','>=',date('Y-m-d') . ' 00:00:00')->where('created_at','<=',date('Y-m-d') . ' 23:59:59')->count();

        // 从会员等级获取基础额度
        $baseQuota = $user->userLevel->view_phone_count ?? 0;
        
        // 保存原始的个人额度调整值（用于计算，不修改数据库）
        $personalAdjustment = $user->view_phone_count ?? 0;
        
        // 计算剩余次数 = 会员等级额度 + 个人调整值 - 已用次数 + 临时额度
        $num = $baseQuota + $personalAdjustment - $use_num;
        
        // 如果有当天的临时额度，加上临时额度
        if ($user->temp_quota_date == date('Y-m-d') && $user->temp_quota != 0) {
            $num += $user->temp_quota;
        }
        
        if ($num <= 0){
            $num = 0;
        }
        // 临时设置 view_phone_count 为剩余次数，用于 API 返回（不保存到数据库）
        $user->view_phone_count = $num;

        return new UserInfoResource($user->loadCount([
            'favoriteHouses',
            'favoriteShops',
            'houses' => function ($query) {
                $query->where('is_draft', 0);
            },
            'shops',
            'notifications',
            'userLevel'
        ]));
    }

    /**
     * 编辑
     * @param Request $request
     * @return UserInfoResource
     */
    public function update(Request $request): UserInfoResource
    {
        $user = $request->user();
        $user->fill($request->input());
        $user->update();
        return new UserInfoResource($user);
    }

    /**
     * 减少查看电话次数
     * @param Request $request
     * @return Response
     * @throws InvalidRequestException
     */
    public function decrementViewPhoneCount(Request $request,): Response
    {
        $user = $request->user();
        $house_id = $request->input('id');

        //查询当日查看次数
        $use_num = UsersViewPhoneLog::where('user_id',$user->id)->where('created_at','>=',date('Y-m-d') . ' 00:00:00')->where('created_at','<=',date('Y-m-d') . ' 23:59:59')->count();
        
        // 从会员等级获取基础额度
        $baseQuota = $user->userLevel->view_phone_count ?? 0;
        
        // 加上个人额度调整值（可正可负）
        $personalAdjustment = $user->view_phone_count ?? 0;
        
        // 计算剩余次数 = 会员等级额度 + 个人调整值 - 已用次数 + 临时额度
        $remaining = $baseQuota + $personalAdjustment - $use_num;
        
        // 如果有当天的临时额度，加上临时额度
        if ($user->temp_quota_date == date('Y-m-d') && $user->temp_quota != 0) {
            $remaining += $user->temp_quota;
        }
        
        if ($remaining <= 0 ) {
            throw new InvalidRequestException('查看次数不足！');
        }
        //查询房源是否查看过
        $use = UsersViewPhoneLog::query()->where('user_id',$user->id)->where('house_id',$house_id)->where('created_at','>=',date('Y-m-d') . ' 00:00:00')->where('created_at','<=',date('Y-m-d') . ' 23:59:59')->first();
        if (!$use){
            $usersViewPhoneLog = new UsersViewPhoneLog();
            $usersViewPhoneLog->user_id = $user->id;
            $usersViewPhoneLog->house_id = $house_id;
            $usersViewPhoneLog->save();
        }
        return response()->noContent();
    }
}
