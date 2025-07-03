<?php
/**
 * 上传 Controller
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadsController extends Controller
{
    /**
     * 上传图片
     * @param Request $request
     * @return JsonResponse
     */
    public function image(Request $request): JsonResponse
    {
        $file = $request->file('image');
        $extension = strtolower($file->getClientOriginalExtension()) ?: 'png';
        $filename = time() . '_' . Str::random(10) . '.' . $extension;
        Storage::put($filename, $file->getContent());
        return response()->json([
            'path' => $filename,
            'url' => Storage::url($filename),
        ]);
    }
}
