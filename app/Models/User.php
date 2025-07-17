<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property mixed $mini_app_openid
 * @property mixed $phone
 * @property mixed|string $nickname
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * 收藏房源
     * @return BelongsToMany
     */
    public function favoriteHouses(): BelongsToMany
    {
        return $this->belongsToMany(House::class, 'user_favorite_houses')
            ->withTimestamps()
            ->orderBy('user_favorite_houses.created_at', 'desc');
    }

    /**
     * 收藏商铺
     * @return BelongsToMany
     */
    public function favoriteShops(): BelongsToMany
    {
        return $this->belongsToMany(Shop::class, 'user_favorite_shops')
            ->withTimestamps()
            ->orderBy('user_favorite_shops.created_at', 'desc');
    }
    
    /**
     * 关联房源
     * @return HasMany
     */
    public function houses(): HasMany
    {
        return $this->hasMany(House::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
