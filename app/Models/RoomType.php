<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price_per_night',
        'capacity',
        'size',
    ];

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Scope a query to include room types with available rooms
     * for the specified date range or for the current date if no dates are provided.
     *
     * @param  Builder  $query  The query builder instance.
     * @param  string|null  $checkIn  The check-in date.
     * @param  string|null  $checkOut  The check-out date.
     * @return Builder The updated query builder instance.
     */
    public function scopeWithAvailableRooms($query, $checkIn = null, $checkOut = null)
    {
        return $query->with(['rooms' => function ($query) use ($checkIn, $checkOut) {
            // Exclude rooms that have bookings overlapping with the given dates
            $query->whereDoesntHave('bookings', function ($query) use ($checkIn, $checkOut) {
                $query->where(function ($query) use ($checkIn, $checkOut) {
                    // Check if existing bookings overlap with the desired check-in/check-out range
                    $query->whereBetween('check_in', [$checkIn ?? now(), $checkOut ?? now()])
                        ->orWhereBetween('check_out', [$checkIn ?? now(), $checkOut ?? now()])
                        ->orWhere(function ($query) use ($checkIn, $checkOut) {
                            // Ensure no booking exists completely encompassing the desired range
                            $query->where('check_in', '<=', $checkIn ?? now())
                                ->where('check_out', '>=', $checkOut ?? now());
                        });
                });
            });
        }]);
    }

    /**
     * Retrieve a list of available rooms for the specified date range.
     * If no dates are provided, it defaults to the current date.
     *
     * @param  string|null  $checkIn  The check-in date.
     * @param  string|null  $checkOut  The check-out date.
     * @return HasMany The relationship query builder for available rooms.
     */
    public function availableRooms($checkIn = null, $checkOut = null): HasMany
    {
        return $this->hasMany(Room::class)
            // Filter out rooms that have overlapping bookings with the provided dates
            ->whereDoesntHave(
                'bookings',
                function ($query) use ($checkIn, $checkOut) {
                    $query->where(function ($query) use ($checkIn, $checkOut) {
                        // Check if existing bookings overlap with the desired check-in/check-out range
                        $query->whereBetween('check_in', [$checkIn ?? now(), $checkOut ?? now()])
                            ->orWhereBetween('check_out', [$checkIn ?? now(), $checkOut ?? now()])
                            ->orWhere(function ($query) use ($checkIn, $checkOut) {
                                // Ensure no booking exists completely encompassing the desired range
                                $query->where('check_in', '<=', $checkIn ?? now())
                                    ->where('check_out', '>=', $checkOut ?? now());
                            });
                    });
                }
            )->where('is_available', true);
    }
}
