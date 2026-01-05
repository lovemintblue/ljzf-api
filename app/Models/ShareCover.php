<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShareCover extends Model
{
    use HasFactory;

    protected $fillable = [
        'image',
        'sort',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort' => 'integer',
    ];

    /**
     * 获取随机封面图
     */
    public static function getRandomCover(): ?string
    {
        $cover = self::where('is_active', true)
            ->inRandomOrder()
            ->first();

        if (!$cover) {
            return null;
        }

        // 如果已经是完整 URL，直接返回
        if (str_starts_with($cover->image, 'http://') || str_starts_with($cover->image, 'https://')) {
            return $cover->image;
        }

        // 否则拼接完整 URL
        return \Storage::disk('qiniu')->url($cover->image);
    }

    /**
     * 获取默认封面图
     */
    public static function getDefaultCover(): string
    {
        return 'https://qiniuoss.lejia1.cn/1759918659_1l0VUNbXoT.png';
    }
}

