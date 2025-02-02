<?php

namespace Database\Factories;

use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Room>
 */
class RoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_number' => 101,
            'floor' => 1,
            'room_type_id' => RoomType::factory(),
            'is_available' => fake()->boolean(90),
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }
}
