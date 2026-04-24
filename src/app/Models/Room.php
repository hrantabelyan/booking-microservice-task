<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasPrefixedUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    /** @use HasFactory<\Database\Factories\RoomFactory> */
    use HasFactory, HasPrefixedUlid;

    protected $fillable = [
        'name',
        'capacity',
    ];

    protected $casts = [
        'capacity' => 'integer',
    ];

    protected static function getUlidPrefix(): string
    {
        return 'rom';
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
