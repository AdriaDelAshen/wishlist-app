<?php

namespace App\Models;

use App\Enums\WishlistItemTypeEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'type',
        'priority',
        'in_shopping_list',
        'is_bought',
        'wishlist_id',
        'user_id',
        'group_id',
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
            'type'             => WishlistItemTypeEnum::class,
            'priority'         => 'integer',
            'in_shopping_list' => 'boolean',
            'is_bought'        => 'boolean',
            'wishlist_id'      => 'integer',
            'user_id'          => 'integer',
            'group_id'         => 'integer',
        ];
    }

    protected $appends = [
        'contributed_amount'
    ];

    /**
     * User who will buy the item.
     * @return BelongsTo
     */
    public function user(): BelongsTo//TODO change for buyer
    {
        return $this->belongsTo(User::class);
    }

    public function wishlist(): BelongsTo
    {
        return $this->belongsTo(Wishlist::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function toDisplayData(): array
    {
        return [
            ...$this->toArray(),
            'wishlist' => $this->wishlist->load('user')
        ];
    }

    public function contributedAmount(): Attribute
    {
        return Attribute::get(function () {
            if (!$this->group) {
                return 0;
            }

            return $this->group->members->sum(fn($member) => $member->pivot->contribution_amount ?? 0);
        });
    }
}
