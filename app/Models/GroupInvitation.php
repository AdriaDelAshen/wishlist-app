<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupInvitation extends Model
{
    /** @use HasFactory<\Database\Factories\GroupInvitationFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'type',
        'token',
        'accepted_at',
        'expires_at',
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
            'email'       => 'string',
            'type'        => 'string',
            'token'       => 'string',
            'accepted_at' => 'datetime',
            'expires_at'  => 'datetime',
            'group_id'    => 'integer',
        ];
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function scopeNotExpired($query)
    {
        return $query->where('expires_at', '>', now());
    }

    public function scopeNotAccepted($query)
    {
        return $query->where('accepted_at', null);
    }

    public function isAccepted()
    {
        return !is_null($this->accepted_at);
    }
}
