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
        $capacity = RoomType::where('id', $this->room_type_id)->value('capacity');
        if ($capacity !== null && $value > $capacity) {
            $fail('The selected room type does not have enough capacity.')->translate();
        }
    }
}
