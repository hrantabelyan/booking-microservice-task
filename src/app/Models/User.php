<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasPrefixedUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasPrefixedUlid, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected static function getUlidPrefix(): string
    {
        return 'usr';
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'user_uid', 'id');
    }
}
