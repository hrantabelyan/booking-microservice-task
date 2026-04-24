<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Collection;

interface BookingRepositoryInterface
{
    public function create(array $attributes): Booking;

    public function hasConflict(string $roomId, string $startsAt, string $endsAt): bool;

    /**
     * @return Collection<int, Booking>
     */
    public function listByUser(string $userUid): Collection;

    /**
     * @return Collection<int, Booking>
     */
    public function listByRoom(string $roomId): Collection;
}
