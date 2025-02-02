<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
     */
    public function scopeWithAvailableRooms(Builder $query, ?string $checkIn = null, ?string $checkOut = null): Builder
    {
        return $query->with(['rooms' => function ($query) use ($checkIn, $checkOut) {
            $query->availableBetween($checkIn, $checkOut);
        }]);
    }

    /**
     * Retrieve a list of available rooms for the specified date range.
     * If no dates are provided, it defaults to the current date.
     *
     * @param  string|null  $checkIn  The check-in date. Defaults to the current date if not provided.
     * @param  string|null  $checkOut  The check-out date. Defaults to the current date if not provided.
     */
    public function availableRooms(?string $checkIn = null, ?string $checkOut = null): HasMany
    {
        $checkIn ??= now()->toDateString();
        $checkOut ??= now()->toDateString();

        return $this->hasMany(Room::class)->availableBetween($checkIn, $checkOut);
    }
}
