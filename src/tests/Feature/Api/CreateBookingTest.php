<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Mail\BookingCreatedMail;
use App\Models\Booking;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CreateBookingTest extends TestCase
{
    use RefreshDatabase;

    private const API_KEY = 'test-api-key';

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.api_key' => self::API_KEY]);
        Mail::fake();
    }

    public function test_creates_a_booking(): void
    {
        $room = Room::factory()->create();

        $payload = [
            'room_id' => $room->id,
            'user_uid' => 'user-123',
            'title' => 'Sprint planning',
            'starts_at' => Carbon::now()->addDay()->setTime(10, 0)->toIso8601String(),
            'ends_at' => Carbon::now()->addDay()->setTime(11, 0)->toIso8601String(),
        ];

        $response = $this->withHeaders(['X-API-Key' => self::API_KEY])
            ->postJson('/api/v1/bookings', $payload);

        $response->assertCreated()
            ->assertJsonPath('room_id', $room->id)
            ->assertJsonPath('user_uid', 'user-123')
            ->assertJsonPath('title', 'Sprint planning')
            ->assertJsonPath('room.id', $room->id);

        $this->assertDatabaseHas('bookings', [
            'room_id' => $room->id,
            'user_uid' => 'user-123',
            'title' => 'Sprint planning',
        ]);

        Mail::assertSent(BookingCreatedMail::class);
    }

    public function test_rejects_overlapping_booking(): void
    {
        $room = Room::factory()->create();

        Booking::factory()->create([
            'room_id' => $room->id,
            'user_uid' => 'user-a',
            'starts_at' => Carbon::now()->addDay()->setTime(10, 0),
            'ends_at' => Carbon::now()->addDay()->setTime(11, 0),
        ]);

        $payload = [
            'room_id' => $room->id,
            'user_uid' => 'user-b',
            'title' => 'Overlapping meeting',
            'starts_at' => Carbon::now()->addDay()->setTime(10, 30)->toIso8601String(),
            'ends_at' => Carbon::now()->addDay()->setTime(11, 30)->toIso8601String(),
        ];

        $response = $this->withHeaders(['X-API-Key' => self::API_KEY])
            ->postJson('/api/v1/bookings', $payload);

        $response->assertStatus(409);
        $this->assertSame(1, Booking::query()->count());
    }

    public function test_allows_adjacent_non_overlapping_bookings(): void
    {
        $room = Room::factory()->create();

        Booking::factory()->create([
            'room_id' => $room->id,
            'starts_at' => Carbon::now()->addDay()->setTime(10, 0),
            'ends_at' => Carbon::now()->addDay()->setTime(11, 0),
        ]);

        $payload = [
            'room_id' => $room->id,
            'user_uid' => 'user-x',
            'title' => 'Right after',
            'starts_at' => Carbon::now()->addDay()->setTime(11, 0)->toIso8601String(),
            'ends_at' => Carbon::now()->addDay()->setTime(12, 0)->toIso8601String(),
        ];

        $response = $this->withHeaders(['X-API-Key' => self::API_KEY])
            ->postJson('/api/v1/bookings', $payload);

        $response->assertCreated();
        $this->assertSame(2, Booking::query()->count());
    }

    public function test_validates_required_fields(): void
    {
        $response = $this->withHeaders(['X-API-Key' => self::API_KEY])
            ->postJson('/api/v1/bookings', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['room_id', 'user_uid', 'title', 'starts_at', 'ends_at']);
    }

    public function test_end_must_be_after_start(): void
    {
        $room = Room::factory()->create();

        $payload = [
            'room_id' => $room->id,
            'user_uid' => 'user-123',
            'title' => 'Backwards time',
            'starts_at' => Carbon::now()->addDay()->setTime(11, 0)->toIso8601String(),
            'ends_at' => Carbon::now()->addDay()->setTime(10, 0)->toIso8601String(),
        ];

        $response = $this->withHeaders(['X-API-Key' => self::API_KEY])
            ->postJson('/api/v1/bookings', $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['ends_at']);
    }

    public function test_rejects_booking_exceeding_max_duration(): void
    {
        config(['booking.max_duration_minutes' => 60]);

        $room = Room::factory()->create();

        $payload = [
            'room_id' => $room->id,
            'user_uid' => 'user-123',
            'title' => 'Marathon meeting',
            'starts_at' => Carbon::now()->addDay()->setTime(10, 0)->toIso8601String(),
            'ends_at' => Carbon::now()->addDay()->setTime(12, 0)->toIso8601String(),
        ];

        $response = $this->withHeaders(['X-API-Key' => self::API_KEY])
            ->postJson('/api/v1/bookings', $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['ends_at']);
    }

    public function test_rejects_unknown_room(): void
    {
        $payload = [
            'room_id' => 'rom_unknown',
            'user_uid' => 'user-123',
            'title' => 'Ghost meeting',
            'starts_at' => Carbon::now()->addDay()->setTime(10, 0)->toIso8601String(),
            'ends_at' => Carbon::now()->addDay()->setTime(11, 0)->toIso8601String(),
        ];

        $response = $this->withHeaders(['X-API-Key' => self::API_KEY])
            ->postJson('/api/v1/bookings', $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['room_id']);
    }
}
