<?php

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
