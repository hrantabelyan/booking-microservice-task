<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Room\ListRoomsAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\RoomCollection;

class RoomController extends Controller
{
    public function index(ListRoomsAction $action): RoomCollection
    {
        return new RoomCollection($action->execute());
    }
}
