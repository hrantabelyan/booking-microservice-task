<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Booking\CreateBookingAction;
use App\Actions\Booking\ListBookingsAction;
use App\DTOs\Booking\StoreBookingDTO;
use App\Exceptions\BookingConflictException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ListBookingsRequest;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class BookingController extends Controller
{
    use ApiResponseTrait;

    public function store(StoreBookingRequest $request, CreateBookingAction $action): JsonResponse
    {
        try {
            $booking = $action->execute(StoreBookingDTO::fromRequest($request));
        } catch (BookingConflictException $e) {
            return $this->respondError($e->getMessage(), 409);
        }

        return $this->respondCreated(new BookingResource($booking->load('room')));
    }

    public function index(ListBookingsRequest $request, ListBookingsAction $action): JsonResponse
    {
        $bookings = $action->execute(
            userUid: $request->query('user_uid'),
            roomId: $request->query('room_id'),
        );

        return $this->respondWithSuccess(BookingResource::collection($bookings));
    }
}
