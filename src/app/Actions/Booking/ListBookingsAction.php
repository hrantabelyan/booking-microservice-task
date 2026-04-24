<?php

declare(strict_types=1);

namespace App\Actions\Booking;

use App\Models\Booking;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ListBookingsAction
{
    public function __construct(
        private readonly BookingRepositoryInterface $bookings,
    ) {}

    /**
     * @return Collection<int, Booking>
     */
    public function execute(?string $userUid, ?string $roomId): Collection
    {
        if ($userUid !== null) {
            return $this->bookings->listByUser($userUid);
        }

        return $this->bookings->listByRoom((string) $roomId);
    }
}
