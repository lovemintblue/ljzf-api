<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property mixed $mini_app_openid
 * @property mixed $phone
 * @property mixed|string $nickname
 * @property mixed $user_level_id
 * @property mixed $expired_at
 * @property mixed $view_phone_count
 * @property int|mixed $status
 */
class UsersViewPhoneLog extends Model
{
    protected $table = 'users_view_phone_log';
}
