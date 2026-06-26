<?php

namespace App\Models;

use App\Support\TicketTagColor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class TicketTag extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'color',
    ];

    protected static function booted(): void
    {
        static::creating(function (TicketTag $tag) {
            if (blank($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }

            if (blank($tag->color)) {
                $tag->color = TicketTagColor::next();
            }
        });
    }

    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class, 'ticket_tag_ticket')->withTimestamps();
    }
}
