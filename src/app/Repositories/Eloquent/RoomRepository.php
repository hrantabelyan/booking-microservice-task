<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Room;
use App\Repositories\Contracts\RoomRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class RoomRepository implements RoomRepositoryInterface
{
    private const CACHE_KEY = 'rooms.all';

    private const CACHE_TTL = 300;

    public function listAll(): Collection
    {
        /** @var Collection<int, Room> */
        return Cache::remember(
            self::CACHE_KEY,
            self::CACHE_TTL,
            fn (): Collection => Room::query()->orderBy('name')->get(),
        );
    }
}
