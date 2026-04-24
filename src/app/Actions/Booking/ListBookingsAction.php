<?php

declare(strict_types=1);

namespace App\Actions\Booking;

use App\Models\Booking;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListBookingsAction
{
    public function __construct(
        private readonly BookingRepositoryInterface $bookings,
    ) {}

    /**
     * @return LengthAwarePaginator<int, Booking>
     */
    public function execute(
        ?string $userUid,
        ?string $roomId,
        int $perPage,
        ?string $from = null,
        ?string $to = null,
    ): LengthAwarePaginator {
        if ($userUid !== null) {
            return $this->bookings->listByUser($userUid, $perPage, $from, $to);
        }

        return $this->bookings->listByRoom((string) $roomId, $perPage, $from, $to);
    }
}
