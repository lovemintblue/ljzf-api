<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\HouseFollowUpRequest;
use App\Http\Resources\HouseFollowUp\HouseFollowUpInfoResource;
use App\Models\HouseFollowUp;
use Illuminate\Http\Request;

class HouseFollowUpsController extends Controller
{
    public function index()
    {

    }

    /**
     * 新增
     * @param HouseFollowUpRequest $request
     * @param HouseFollowUp $houseFollowUp
     * @return HouseFollowUpInfoResource
     */
    public function store(HouseFollowUpRequest $request, HouseFollowUp $houseFollowUp): HouseFollowUpInfoResource
    {
        $user = $request->user();
        $houseFollowUp->fill($request->all());
        $houseFollowUp->user()->associate($user);
        $houseFollowUp->save();
        return new HouseFollowUpInfoResource($houseFollowUp);
    }
}
