<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Amenity>
 */
class AmenityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Wi-Fi',
                'TV',
                'Mini Bar',
                'Air Conditioning',
                'Safe',
                'Balcony',
                'Ocean View',
                'Kitchen',
                'Living Room',
            ]),
        ];
    }
}
