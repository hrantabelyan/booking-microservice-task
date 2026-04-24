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
use App\Http\Resources\BookingCollection;
use App\Http\Resources\BookingResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class BookingController extends Controller
{
    use ApiResponseTrait;

    private const DEFAULT_PER_PAGE = 15;

    public function store(StoreBookingRequest $request, CreateBookingAction $action): JsonResponse
    {
        try {
            $booking = $action->execute(StoreBookingDTO::fromRequest($request));
        } catch (BookingConflictException $e) {
            return $this->respondError($e->getMessage(), 409);
        }

        return $this->respondCreated(new BookingResource($booking->load('room')));
    }

    public function index(ListBookingsRequest $request, ListBookingsAction $action): BookingCollection
    {
        $paginator = $action->execute(
            userUid: $request->query('user_uid'),
            roomId: $request->query('room_id'),
            perPage: (int) $request->query('per_page', (string) self::DEFAULT_PER_PAGE),
            from: $request->query('from'),
            to: $request->query('to'),
        );

        return new BookingCollection($paginator);
    }
}
