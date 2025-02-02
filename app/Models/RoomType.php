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

    public function scopeWithAvailableRooms($query)
    {
        return $query->with(['rooms' => function ($query) {
            $query->whereDoesntHave('bookings', function ($query) {
                $query->where(function ($query) {
                    $query->where('check_in', '<=', now())
                        ->where('check_out', '>=', now());
                });
            });
        }]);
    }

    public function availableRooms(): HasMany
    {
        return $this->rooms()
            ->whereDoesntHave('bookings', function ($query) {
                $query->where(function ($query) {
                    $query->where('check_in', '<=', now())
                        ->where('check_out', '>=', now());
                });
            });
    }

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
}
