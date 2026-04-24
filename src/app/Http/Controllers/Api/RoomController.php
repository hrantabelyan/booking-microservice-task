<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Room\ListRoomsAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\RoomResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class RoomController extends Controller
{
    use ApiResponseTrait;

    public function index(ListRoomsAction $action): JsonResponse
    {
        return $this->respondWithSuccess(RoomResource::collection($action->execute()));
    }
}
