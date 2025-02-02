<?php

namespace Database\Factories;

use App\Models\Amenity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RoomType>
 */
class RoomTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Standard', 'Superior', 'Deluxe', 'Suite']),
            'description' => fake()->paragraph(),
            'price_per_night' => fake()->numberBetween(100, 1000),
            'capacity' => fake()->numberBetween(1, 6),
            'size' => fake()->numberBetween(25, 120),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function ($roomType) {
            $amenities = Amenity::inRandomOrder()
                ->take(fake()->numberBetween(3, 8))
                ->get();

            $roomType->amenities()->attach($amenities);
        });
    }
}
