<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Booking;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

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

    public function listByUser(string $userUid, int $perPage, ?string $from = null, ?string $to = null): LengthAwarePaginator
    {
        return $this->baseListQuery($from, $to)
            ->where('user_uid', $userUid)
            ->paginate($perPage);
    }

    public function listByRoom(string $roomId, int $perPage, ?string $from = null, ?string $to = null): LengthAwarePaginator
    {
        return $this->baseListQuery($from, $to)
            ->where('room_id', $roomId)
            ->paginate($perPage);
    }

    /**
     * @return Builder<Booking>
     */
    private function baseListQuery(?string $from, ?string $to): Builder
    {
        return Booking::query()
            ->with('room')
            ->when($from, fn (Builder $q, string $f) => $q->where('ends_at', '>=', $f))
            ->when($to, fn (Builder $q, string $t) => $q->where('starts_at', '<=', $t))
            ->orderBy('starts_at');
    }
}
