<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $code
 * @property mixed $name
 * @property mixed $title
 * @property mixed $content
 * @property mixed $is_enabled
 * @property mixed $variables
 */
class NotificationTemplate extends Model
{
    protected $fillable = [
        'code',
        'name',
        'title',
        'content',
        'is_enabled',
        'variables',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    /**
     * 替换模板变量
     * @param array $variables
     * @return array
     */
    public function renderNotification(array $variables = []): array
    {
        $title = $this->title;
        $content = $this->content;

        foreach ($variables as $key => $value) {
            $title = str_replace('{' . $key . '}', $value, $title);
            $content = str_replace('{' . $key . '}', $value, $content);
        }

        return [
            'title' => $title,
            'content' => $content,
        ];
    }
}
