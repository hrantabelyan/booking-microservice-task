<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasPrefixedUlid;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $room_id
 * @property string $user_uid
 * @property string $title
 * @property Carbon $starts_at
 * @property Carbon $ends_at
 * @property-read Room|null $room
 */
class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
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
