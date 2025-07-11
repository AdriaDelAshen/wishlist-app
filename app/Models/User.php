<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'birthday_date',
        'wants_birthday_notifications',
        'is_admin',
        'is_active',
        'preferred_locale',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'            => 'datetime',
            'password'                     => 'hashed',
            'birthday_date'                => 'date:Y-m-d',
            'wants_birthday_notifications' => 'boolean',
            'is_admin'                     => 'boolean',
            'is_active'                    => 'boolean',
            'preferred_locale'             => 'string',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(fn(User $user) => $user->wishlists()
            ->each(function(Wishlist $wishlist) {
                $wishlist->wishlistItems()->delete();
                $wishlist->delete();
            }));
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class)
//            ->withPivot('role')
            ->withTimestamps();
    }

    public function ownedGroups(): HasMany
    {
        return $this->hasMany(Group::class, 'user_id');
    }

    public function isAnActiveAdmin(): bool
    {
        return $this->is_active && $this->is_admin;
    }
}
