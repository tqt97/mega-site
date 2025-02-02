<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_number',
        'floor',
        'room_type_id',
        'is_available',
        'notes',
    ];

    protected $casts = [
        'is_available' => 'boolean',
    ];

    /**
     * Scope a query to only include rooms that are available
     * between the given dates.
     *
     * @param  Builder  $query  The query builder instance.
     * @param  string  $checkIn  The check-in date.
     * @param  string  $checkOut  The check-out date.
     * @return Builder The updated query builder instance.
     */
    public function scopeAvailableBetween(Builder $query, string $checkIn, string $checkOut): Builder
    {
        return $query
            // Use lockForUpdate() to prevent concurrent modifications while checking availability
            ->lockForUpdate()
            ->where('is_available', true)
            // Exclude rooms that have bookings overlapping with the given dates
            ->whereDoesntHave('bookings', function ($query) use ($checkIn, $checkOut) {
                $query->where('check_in', '<', $checkOut)
                    ->where('check_out', '>', $checkIn);
            });
    }

    /**
     * Safely book a room using database transactions to prevent race conditions.
     *
     * @param  array  $bookingData  Additional booking data.
     *
     * @throws Exception If the room is no longer available
     */
    public function safelyBook(array $bookingData): Booking
    {
        return DB::transaction(function () use ($bookingData) {
            // Lock only this specific room row
            $isRoomAvailable = $this->newQuery()
                ->where('id', $this->id)
                ->lockForUpdate()
                ->availableBetween(
                    $bookingData['check_in'],
                    $bookingData['check_out']
                )
                ->exists();

            if (! $isRoomAvailable) {
                throw new Exception('Room is no longer available for the selected dates.');
            }

            return $this->bookings()->create($bookingData);
        });
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
