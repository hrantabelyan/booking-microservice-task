<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\Booking\CreateBookingAction;
use App\DTOs\Booking\StoreBookingDTO;
use App\Exceptions\BookingConflictException;
use App\Models\Booking;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CreateBookingActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_booking_when_no_conflict(): void
    {
        Mail::fake();

        $room = Room::factory()->create();

        $dto = new StoreBookingDTO(
            roomId: $room->id,
            userUid: 'user-1',
            title: 'Standup',
            startsAt: Carbon::now()->addDay()->setTime(9, 0),
            endsAt: Carbon::now()->addDay()->setTime(9, 30),
        );

        $action = app(CreateBookingAction::class);
        $booking = $action->execute($dto);

        $this->assertInstanceOf(Booking::class, $booking);
        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_throws_on_conflict(): void
    {
        Mail::fake();

        $room = Room::factory()->create();
        Booking::factory()->create([
            'room_id' => $room->id,
            'starts_at' => Carbon::now()->addDay()->setTime(9, 0),
            'ends_at' => Carbon::now()->addDay()->setTime(10, 0),
        ]);

        $dto = new StoreBookingDTO(
            roomId: $room->id,
            userUid: 'user-2',
            title: 'Conflict',
            startsAt: Carbon::now()->addDay()->setTime(9, 30),
            endsAt: Carbon::now()->addDay()->setTime(10, 30),
        );

        $this->expectException(BookingConflictException::class);
        app(CreateBookingAction::class)->execute($dto);

        $this->assertDatabaseCount('bookings', 1);
    }
}
