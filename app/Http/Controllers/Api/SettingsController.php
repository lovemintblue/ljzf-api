<?php
/**
 * 设置 Controller
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Settings\GeneralSettingsResource;
use App\Models\ShareCover;
use App\Settings\GeneralSettings;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * @return GeneralSettingsResource
     */
    public function general(): GeneralSettingsResource
    {
        $generalSettings = new GeneralSettings();
        return new GeneralSettingsResource($generalSettings);
    }

    /**
     * 获取随机分享封面图
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function randomShareCover()
    {
        $coverImage = ShareCover::getRandomCover();
        
        // 如果没有启用的封面图，返回默认封面
        if (!$coverImage) {
            $coverImage = ShareCover::getDefaultCover();
        }

        return response()->json([
            'cover_image' => $coverImage
        ]);
    }
}
