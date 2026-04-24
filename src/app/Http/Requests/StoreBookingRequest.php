<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Room;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'room_id' => ['required', 'string', 'exists:'.Room::class.',id'],
            'user_uid' => ['required', 'string', 'max:64'],
            'title' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date', 'after:now'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            if ($v->errors()->hasAny(['starts_at', 'ends_at'])) {
                return;
            }

            $maxMinutes = (int) config('booking.max_duration_minutes');
            $durationMinutes = Carbon::parse((string) $this->input('starts_at'))
                ->diffInMinutes(Carbon::parse((string) $this->input('ends_at')));

            if ($durationMinutes > $maxMinutes) {
                $v->errors()->add(
                    'ends_at',
                    "Booking duration cannot exceed {$maxMinutes} minutes.",
                );
            }
        });
    }
}
