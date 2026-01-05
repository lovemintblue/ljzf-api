<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * 格式化URL
 * @param string|null $url
 * @return string
 */
function formatUrl(string|null $url): string
{
    if (empty($url)) {
        return '';
    }
    return Storage::url($url);
}

/**
 * 格式化时间
 * @param $at
 * @return string
 */
function formatAt($at): string
{
    if (empty($at)) {
        return '';
    }
    return Carbon::parse($at)->format('Y-m-d H:i:s');
}
