<?php

use App\Http\Controllers\Api\AuthorizationsController;
use App\Http\Controllers\Api\FacilitiesController;
use App\Http\Controllers\Api\HousesController;
use App\Http\Controllers\Api\IndustriesController;
use App\Http\Controllers\Api\ShopsController;
use App\Http\Controllers\Api\UploadsController;
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

// 上传图片
Route::post('uploads/image', [UploadsController::class, 'image'])
    ->name('uploads.image');

// 令牌路由
Route::middleware('auth:sanctum')->group(function () {
    // 登录信息
    Route::get('me', [UsersController::class, 'me'])
        ->name('users.me');
    // 用户 - 编辑
    Route::put('users/update', [UsersController::class, 'update'])
        ->name('users.update');

    // 房源 - 新增
    Route::post('houses', [HousesController::class, 'store'])
        ->name('houses.store');

    // 商铺 - 新增
    Route::post('shops', [ShopsController::class, 'store'])
        ->name('shops.store');
});

