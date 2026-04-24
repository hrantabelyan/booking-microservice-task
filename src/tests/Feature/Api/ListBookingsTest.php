<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Booking;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListBookingsTest extends TestCase
{
    use RefreshDatabase;

    private const API_KEY = 'test-api-key';

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.api_key' => self::API_KEY]);
    }

    public function test_lists_bookings_for_a_user(): void
    {
        $room = Room::factory()->create();

        Booking::factory()->count(2)->create(['user_uid' => 'user-a', 'room_id' => $room->id]);
        Booking::factory()->count(3)->create(['user_uid' => 'user-b', 'room_id' => $room->id]);

        $response = $this->withHeaders(['X-API-Key' => self::API_KEY])
            ->getJson('/api/v1/bookings?user_uid=user-a');

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
        foreach ($response->json('data') as $booking) {
            $this->assertSame('user-a', $booking['user_uid']);
        }
    }

    public function test_lists_bookings_for_a_room(): void
    {
        $roomA = Room::factory()->create();
        $roomB = Room::factory()->create();

        Booking::factory()->count(2)->create(['room_id' => $roomA->id]);
        Booking::factory()->count(4)->create(['room_id' => $roomB->id]);

        $response = $this->withHeaders(['X-API-Key' => self::API_KEY])
            ->getJson('/api/v1/bookings?room_id='.$roomA->id);

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
        foreach ($response->json('data') as $booking) {
            $this->assertSame($roomA->id, $booking['room_id']);
        }
    }

    public function test_requires_user_uid_or_room_id(): void
    {
        $response = $this->withHeaders(['X-API-Key' => self::API_KEY])
            ->getJson('/api/v1/bookings');

        $response->assertStatus(422)->assertJsonValidationErrors(['user_uid', 'room_id']);
    }
}
