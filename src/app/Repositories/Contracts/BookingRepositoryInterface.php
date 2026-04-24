<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Booking;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface BookingRepositoryInterface
{
    public function create(array $attributes): Booking;

    public function hasConflict(string $roomId, string $startsAt, string $endsAt): bool;

    /**
     * @return LengthAwarePaginator<int, Booking>
     */
    public function listByUser(string $userUid, int $perPage, ?string $from = null, ?string $to = null): LengthAwarePaginator;

    /**
     * @return LengthAwarePaginator<int, Booking>
     */
    public function listByRoom(string $roomId, int $perPage, ?string $from = null, ?string $to = null): LengthAwarePaginator;
}
