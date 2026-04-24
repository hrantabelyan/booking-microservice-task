<?php

declare(strict_types=1);

namespace App\DTOs\Booking;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

final readonly class StoreBookingDTO
{
    public function __construct(
        public string $roomId,
        public string $userUid,
        public string $title,
        public Carbon $startsAt,
        public Carbon $endsAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            roomId: (string) $data['room_id'],
            userUid: (string) $data['user_uid'],
            title: (string) $data['title'],
            startsAt: Carbon::parse((string) $data['starts_at']),
            endsAt: Carbon::parse((string) $data['ends_at']),
        );
    }

    public static function fromRequest(FormRequest $request): self
    {
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();

        return self::fromArray($validated);
    }

    public function toArray(): array
    {
        return [
            'room_id' => $this->roomId,
            'user_uid' => $this->userUid,
            'title' => $this->title,
            'starts_at' => $this->startsAt->toDateTimeString(),
            'ends_at' => $this->endsAt->toDateTimeString(),
        ];
    }
}
