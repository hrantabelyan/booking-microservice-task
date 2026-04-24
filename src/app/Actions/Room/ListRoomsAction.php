<?php

declare(strict_types=1);

namespace App\Actions\Room;

use App\Models\Room;
use App\Repositories\Contracts\RoomRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ListRoomsAction
{
    public function __construct(
        private readonly RoomRepositoryInterface $rooms,
    ) {}

    /**
     * @return Collection<int, Room>
     */
    public function execute(): Collection
    {
        return $this->rooms->listAll();
    }
}
