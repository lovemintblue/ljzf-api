<?php
/**
 * 轮播图 Controller
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Carousel\CarouselResource;
use App\Models\Carousel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CarouselsController extends Controller
{
    /**
     * 列表
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        $carousels = Carousel::query()->latest()->get();
        CarouselResource::wrap('data');
        return CarouselResource::collection($carousels);
    }
}
