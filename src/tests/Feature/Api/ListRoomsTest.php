<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListRoomsTest extends TestCase
{
    use RefreshDatabase;

    private const API_KEY = 'test-api-key';

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.api_key' => self::API_KEY]);
    }

    public function test_lists_all_rooms(): void
    {
        Room::factory()->count(3)->create();

        $response = $this->withHeaders(['X-API-Key' => self::API_KEY])
            ->getJson('/api/v1/rooms');

        $response->assertOk();
        $this->assertCount(3, $response->json('data'));
        $this->assertArrayHasKey('id', $response->json('data.0'));
        $this->assertArrayHasKey('name', $response->json('data.0'));
        $this->assertArrayHasKey('capacity', $response->json('data.0'));
    }

    public function test_requires_api_key(): void
    {
        $response = $this->getJson('/api/v1/rooms');

        $response->assertStatus(401);
    }
}
