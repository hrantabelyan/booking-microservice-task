<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $startsAt = Carbon::now()->addDays(fake()->numberBetween(1, 14))->setTime(fake()->numberBetween(9, 16), 0);

        return [
            'room_id' => Room::factory(),
            'user_uid' => 'user-'.Str::lower(Str::random(8)),
            'title' => fake()->sentence(3),
            'starts_at' => $startsAt,
            'ends_at' => (clone $startsAt)->addHour(),
        ];
    }
}
