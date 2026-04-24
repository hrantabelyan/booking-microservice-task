<?php

declare(strict_types=1);

namespace Database\Seeders;

use Kdabrow\SeederOnce\SeederOnce;

class DatabaseSeeder extends SeederOnce
{
    public bool $seedOnce = false;

    public function run(): void
    {
        $this->call([
            RoomSeeder::class,
        ]);
    }
}
