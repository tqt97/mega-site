<?php

namespace App\Http\Requests;

use App\Models\Booking;
use App\Rules\GuestCapacityRule;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'room_type_id' => 'required|exists:room_types,id',
            'check_in' => 'required|date|after:today',
            'check_out' => [
                'required',
                'date',
                'after:check_in',
                function ($attribute, $value, $fail) {
                    if (Carbon::parse($value)->diffInDays($this->check_in) > Booking::MAX_DAYS_ALLOWED) {
                        $fail('The check-out date must be at most '.Booking::MAX_DAYS_ALLOWED.' days after the check-in date.');
                    }
                },
            ],
            'guests' => [
                'required',
                'integer',
                'min:1',
                'max:6',
                new GuestCapacityRule($this->room_type_id),
            ],
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'room_id' => 'required|exists:rooms,id',
        ];
    }
}
