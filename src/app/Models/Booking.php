<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasPrefixedUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    /** @use HasFactory<\Database\Factories\BookingFactory> */
    use HasFactory, HasPrefixedUlid;

    protected $fillable = [
        'room_id',
        'user_uid',
        'title',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    protected static function getUlidPrefix(): string
    {
        return 'bkg';
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
