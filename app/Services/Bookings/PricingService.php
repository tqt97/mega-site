<?php

namespace App\Services\Bookings;

use App\Models\RoomType;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

class PricingService
{
    /**
     * Calculates the price of a booking.
     */
    public function calculateBookingPrice(RoomType $roomType, string $checkIn, string $checkOut): array
    {
        $cacheKey = "pricing:room:{$roomType->id}:dates:{$checkIn}-{$checkOut}";

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($roomType, $checkIn, $checkOut) {
            try {
                $checkInDate = Carbon::parse($checkIn);
                $checkOutDate = Carbon::parse($checkOut);
            } catch (Exception) {
                throw new InvalidArgumentException('Invalid date format provided.');
            }

            if ($checkInDate > $checkOutDate) {
                throw new InvalidArgumentException('Check-in date must be before check-out date.');
            }

            if (! is_numeric($roomType->price_per_night) || $roomType->price_per_night <= 0) {
                throw new InvalidArgumentException('Room type price must be a positive number.');
            }

            // Calculate the number of nights - minimum of 1
            $nights = max($checkInDate->diffInDays($checkOutDate), 1);

            // Use BC Math for precise decimal calculation with 2 decimal places
            $totalPrice = bcmul((string) $roomType->price_per_night, (string) $nights, 2);

            return [
                'nights' => $nights,
                'price_per_night' => bcadd($roomType->price_per_night, '0', 2),
                'total_price' => bcadd($totalPrice, '0', 2),
            ];
        }, $roomType); // Add RoomType model to cache tags
    }
}
