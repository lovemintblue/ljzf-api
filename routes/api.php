<?php

use App\Http\Controllers\Api\AuthorizationsController;
use App\Http\Controllers\Api\BusinessDistrictsController;
use App\Http\Controllers\Api\CommunitiesController;
use App\Http\Controllers\Api\DistrictsController;
use App\Http\Controllers\Api\FacilitiesController;
use App\Http\Controllers\Api\HousesController;
use App\Http\Controllers\Api\HouseViewHistoriesController;
use App\Http\Controllers\Api\IndexController;
use App\Http\Controllers\Api\IndustriesController;
use App\Http\Controllers\Api\NotificationsController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\ShopsController;
use App\Http\Controllers\Api\UploadsController;
use App\Http\Controllers\Api\UserLevelsController;
use App\Http\Controllers\Api\UsersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// 登录
Route::post('authorizations', [AuthorizationsController::class, 'store'])
    ->name('authorizations.store');

// 配套设施 - 列表
Route::get('facilities', [FacilitiesController::class, 'index'])
    ->name('facilities.index');

// 行业 - 列表
Route::get('industries', [IndustriesController::class, 'index'])
    ->name('industries.index');

// 上传 - 图片
Route::post('uploads/image', [UploadsController::class, 'image'])
    ->name('uploads.image');

// 上传 - 文件
Route::post('uploads/file', [UploadsController::class, 'file'])
    ->name('uploads.file');


// 小区 - 列表
Route::get('communities', [CommunitiesController::class, 'index'])
    ->name('communities.index');
// 小区 - 详情
Route::get('communities/{community}', [CommunitiesController::class, 'show'])
    ->name('communities.show');

// 商圈 - 列表
Route::get('business-districts', [BusinessDistrictsController::class, 'index'])
    ->name('business-districts.index');

// 行政区划 - 列表
Route::get('districts', [DistrictsController::class, 'index'])
    ->name('districts.index');
// 行政区划 - 根据名称获取ID
Route::get('districts/get-children-by-ip', [DistrictsController::class, 'getChildrenByIp'])
    ->name('districts.get-children-by-ip');

// 设置 - 基础
Route::get('settings/general', [SettingsController::class, 'general'])
    ->name('settings.general');

// 首页 - 统计数据
Route::get('index/statistics', [IndexController::class, 'statistics'])
    ->name('index.statistics');

// 令牌路由
Route::middleware('auth:sanctum')->group(function () {
    // 登录信息
    Route::get('me', [UsersController::class, 'me'])
        ->name('users.me');
    // 用户 - 编辑
    Route::put('users/update', [UsersController::class, 'update'])
        ->name('users.update');

    // 房源 - 列表
    Route::get('houses', [HousesController::class, 'index'])
        ->name('houses.index');
    // 房源 - 我的房源
    Route::get('houses/my-index', [HousesController::class, 'myIndex'])
        ->name('houses.my-index');
    // 房源 - 新增
    Route::post('houses', [HousesController::class, 'store'])
        ->name('houses.store');
    // 房源 - 详情
    Route::get('houses/{house}', [HousesController::class, 'show'])
        ->name('houses.show')->where('house', '[0-9]+');
    // 房源 - 编辑
    Route::put('houses/{house}', [HousesController::class, 'update'])
        ->name('houses.update')->where('house', '[0-9]+');
    // 房源 - 收藏
    Route::post('houses/{house}/favorite', [HousesController::class, 'favor'])
        ->name('houses.favor');
    // 房源 - 取消收藏
    Route::delete('houses/{house}/favorite', [HousesController::class, 'disfavor'])
        ->name('houses.disfavor');
    // 房源 - 收藏列表
    Route::get('houses/favorites', [HousesController::class, 'favorites'])
        ->name('houses.favorites');
    // 房源 - 附近列表
    Route::get('houses/nearby', [HousesController::class, 'nearby'])
        ->name('houses.nearby');

    // 商铺 - 列表
    Route::get('shops', [ShopsController::class, 'index'])
        ->name('shops.index');
    // 商铺 - 新增
    Route::post('shops', [ShopsController::class, 'store'])
        ->name('shops.store');
    // 商铺 - 详情
    Route::get('shops/{shop}', [ShopsController::class, 'show'])
        ->name('shops.show')->where('shop', '[0-9]+');
    // 商铺 - 收藏
    Route::post('shops/{shop}/favorite', [ShopsController::class, 'favor'])
        ->name('shops.favor');
    // 商铺 - 取消收藏
    Route::delete('shops/{shop}/favorite', [ShopsController::class, 'disfavor'])
        ->name('shops.disfavor');
    // 商铺 - 收藏列表
    Route::get('shops/favorites', [ShopsController::class, 'favorites'])
        ->name('shops.favorites');

    // 用户等级 - 列表
    Route::get('user-levels', [UserLevelsController::class, 'index'])
        ->name('user-levels.index');
    // 用户等级 - 详情
    Route::get('user-levels/{userLevel}', [UserLevelsController::class, 'show'])
        ->name('user-levels.show');

    // 消息通知 - 列表
    Route::get('notifications', [NotificationsController::class, 'index'])
        ->name('notifications.index');

    // 房源浏览记录 - 列表
    Route::get('house-view-histories', [HouseViewHistoriesController::class, 'index'])
        ->name('house-view-histories.index');
});

