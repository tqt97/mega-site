<?php

namespace App\Http\Requests;

use App\Rules\GuestCapacityRule;
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
            'check_out' => 'required|date|after:check_in',
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
