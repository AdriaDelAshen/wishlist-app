<?php

namespace App\Events;

use App\Models\WishlistItem;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WishlistItemUserHasChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public WishlistItem $wishlistItem;

    public function __construct(WishlistItem $wishlistItem)
    {
        $this->wishlistItem = $wishlistItem;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('wishlistItem');
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->wishlistItem->id,
            'name' => $this->wishlistItem->name,
            'price' => $this->wishlistItem->price,
            'priority' => $this->wishlistItem->priority,
            'wishlist' => $this->wishlistItem->wishlist->load('user'),
            'in_shopping_list' => $this->wishlistItem->in_shopping_list,
            'is_bought' => $this->wishlistItem->is_bought,
            'user_id' => $this->wishlistItem->user_id,
        ];
    }
}
