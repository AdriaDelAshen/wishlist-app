<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WishlistItem extends Model
{
    /** @use HasFactory<\Database\Factories\WishlistItemFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'url_link',
        'price',
        'priority',
        'in_shopping_list',
        'is_bought',
        'wishlist_id',
        'user_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'name'             => 'string',
            'description'      => 'string',
            'url_link'         => 'string',
            'price'            => 'float',
            'priority'         => 'integer',
            'in_shopping_list' => 'boolean',
            'is_bought'        => 'boolean',
            'wishlist_id'      => 'integer',
            'user_id'          => 'integer',
        ];
    }

    /**
     * User who will buy the item.
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wishlist(): BelongsTo
    {
        return $this->belongsTo(Wishlist::class);
    }

    public function toDisplayData(): array
    {
        return [
            ...$this->toArray(),
            'wishlist' => $this->wishlist->load('user')
        ];
    }
}
