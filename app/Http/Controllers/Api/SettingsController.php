<?php
/**
 * 设置 Controller
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Settings\GeneralSettingsResource;
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
}
