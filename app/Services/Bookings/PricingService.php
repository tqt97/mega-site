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
     * Generate cache key for pricing.
     * Consider including more parameters in the key if pricing logic evolves.
     */
    protected function generateCacheKey(RoomType $roomType, string $checkIn, string $checkOut): string
    {
        return "pricing:room:{$roomType->id}:dates:{$checkIn}-{$checkOut}"; // Consider adding more parameters if needed
    }

    /**
     * Get cache TTL in minutes. Make it configurable via .env or config file.
     */
    protected function getCacheTTLMinutes(): int
    {
        return config('booking.pricing_cache_ttl', 5); // Default to 5 minutes, configurable
    }

    /**
     * Get cache store name. Make it configurable via .env or config file.
     */
    protected function getCacheStore(): string
    {
        return config('booking.pricing_cache_store', 'default'); // Default to default store, configurable
    }

    /**
     * Calculates the price of a booking.
     */
    public function calculateBookingPrice(RoomType $roomType, string $checkIn, string $checkOut): array
    {
        $cacheKey = $this->generateCacheKey($roomType, $checkIn, $checkOut); // Use a dedicated method for key generation
        $ttlMinutes = $this->getCacheTTLMinutes(); // Use a configurable TTL

        return Cache::remember($cacheKey, now()->addMinutes($ttlMinutes), function () use ($roomType, $checkIn, $checkOut) {
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

    public static function updatePriceAndInvalidateCache(int $roomTypeId, float $newPrice): void
    {
        RoomType::where('id', $roomTypeId)->update(['price_per_night' => $newPrice]);

        // Invalidate cache for all date ranges for this room type.
        // For simplicity, we can use cache tags if supported by the cache driver (Redis, Memcached tagged cache).
        // If tags are not available, a more complex invalidation strategy might be needed, e.g., deleting cache entries with a prefix.

        Cache::tags(['pricing'])->invalidate(); // Using cache tags for invalidation

        // Alternative (if tags are not feasible or for more precise invalidation - requires knowing date ranges):
        // Cache::forget("pricing:room:{$roomTypeId}:dates:{$startDate}-{$endDate}"); // Need to know date ranges to invalidate precisely.
    }
}
