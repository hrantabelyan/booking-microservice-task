<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Booking;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class BookingRepository implements BookingRepositoryInterface
{
    public function create(array $attributes): Booking
    {
        return Booking::query()->create($attributes);
    }

    public function hasConflict(string $roomId, string $startsAt, string $endsAt): bool
    {
        return Booking::query()
            ->where('room_id', $roomId)
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->exists();
    }

    public function listByUser(string $userUid): Collection
    {
        return Booking::query()
            ->with('room')
            ->where('user_uid', $userUid)
            ->orderBy('starts_at')
            ->get();
    }

    public function listByRoom(string $roomId): Collection
    {
        return Booking::query()
            ->with('room')
            ->where('room_id', $roomId)
            ->orderBy('starts_at')
            ->get();
    }
}
