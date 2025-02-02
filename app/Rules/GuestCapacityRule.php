<?php

namespace App\Rules;

use App\Models\RoomType;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class GuestCapacityRule implements ValidationRule
{
    public function __construct(
        private int $room_type_id
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $roomType = RoomType::find($this->room_type_id);

        if ($roomType && $value > $roomType->capacity) {
            $fail('The selected room type does not have enough capacity.')->translate();
        }
    }
}
