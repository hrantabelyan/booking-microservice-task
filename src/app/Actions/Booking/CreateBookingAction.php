<?php

declare(strict_types=1);

namespace App\Actions\Booking;

use App\DTOs\Booking\StoreBookingDTO;
use App\Exceptions\BookingConflictException;
use App\Mail\BookingCreatedMail;
use App\Models\Booking;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CreateBookingAction
{
    public function __construct(
        private readonly BookingRepositoryInterface $bookings,
    ) {}

    public function execute(StoreBookingDTO $dto): Booking
    {
        return DB::transaction(function () use ($dto): Booking {
            if ($this->bookings->hasConflict(
                $dto->roomId,
                $dto->startsAt->toDateTimeString(),
                $dto->endsAt->toDateTimeString(),
            )) {
                throw new BookingConflictException;
            }

            $booking = $this->bookings->create($dto->toArray());

            Mail::to($dto->userUid.'@booking-microservice.local')
                ->send(new BookingCreatedMail($booking->fresh('room')));

            return $booking;
        });
    }
}
