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
        try {
            $checkInDate = Carbon::parse($checkIn);
            $checkOutDate = Carbon::parse($checkOut);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Invalid date format provided.');
        }

        if ($checkInDate > $checkOutDate) {
            throw new \InvalidArgumentException('Check-in date must be before check-out date.');
        }

        if (! is_numeric($roomType->price_per_night) || $roomType->price_per_night <= 0) {
            throw new \InvalidArgumentException('Room type price must be a positive number.');
        }

        // Calculate the number of nights - minimum of 1
        $nights = max($checkInDate->diffInDays($checkOutDate), 1);
        $totalPrice = $roomType->price_per_night * $nights;

        return [
            'nights' => $nights,
            'price_per_night' => $roomType->price_per_night,
            'total_price' => $totalPrice,
        ];
    }
}
