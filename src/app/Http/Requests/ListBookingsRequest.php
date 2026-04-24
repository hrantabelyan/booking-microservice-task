<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Room;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ListBookingsRequest extends FormRequest
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
            'user_uid' => ['required_without:room_id', 'string', 'max:64'],
            'room_id' => ['required_without:user_uid', 'string', 'exists:'.Room::class.',id'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_uid.required_without' => 'Either user_uid or room_id must be provided.',
            'room_id.required_without' => 'Either user_uid or room_id must be provided.',
        ];
    }
}
