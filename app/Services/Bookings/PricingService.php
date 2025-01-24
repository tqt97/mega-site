<?php

namespace App\Services\Bookings;

use App\Models\RoomType;
use Carbon\Carbon;

class PricingService
{
    /**
     * Calculates the price of a booking.
     */
    public function calculateBookingPrice(RoomType $roomType, string $checkIn, string $checkOut): array
    {
        $checkInDate = Carbon::parse($checkIn);
        $checkOutDate = Carbon::parse($checkOut);

        if ($checkInDate > $checkOutDate) {
            throw new \InvalidArgumentException('Check-in date must be before check-out date.');
        }

        $nights = $checkInDate->diffInDays($checkOutDate);
        $totalPrice = $roomType->price_per_night * $nights;

        return [
            'nights' => $nights,
            'price_per_night' => $roomType->price_per_night,
            'total_price' => $totalPrice,
        ];
    }
}
