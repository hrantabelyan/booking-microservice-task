<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingApiKeyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.api_key' => 'test-api-key']);
    }

    public function test_missing_api_key_returns_401(): void
    {
        $response = $this->getJson('/api/v1/bookings?user_uid=user-123');

        $response->assertStatus(401)->assertJson(['error' => 'Unauthorized']);
    }

    public function test_invalid_api_key_returns_401(): void
    {
        $response = $this->withHeaders(['X-API-Key' => 'wrong'])
            ->getJson('/api/v1/bookings?user_uid=user-123');

        $response->assertStatus(401)->assertJson(['error' => 'Unauthorized']);
    }
}
