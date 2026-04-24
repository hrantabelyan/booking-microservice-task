<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Room;
use Kdabrow\SeederOnce\SeederOnce;

class RoomSeeder extends SeederOnce
{
    public function run(): void
    {
        $rooms = [
            ['name' => 'Alpha', 'capacity' => 4],
            ['name' => 'Beta', 'capacity' => 8],
            ['name' => 'Gamma', 'capacity' => 12],
            ['name' => 'Delta', 'capacity' => 20],
        ];

        foreach ($rooms as $data) {
            Room::query()->firstOrCreate(['name' => $data['name']], $data);
        }
    }
}
