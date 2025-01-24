<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    private const TOTAL_FLOORS = 10;

    private const TOTAL_ROOMS_PER_FLOOR = 6;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roomTypes = RoomType::all();
        $floors = self::TOTAL_FLOORS;
        $roomsPerFloor = self::TOTAL_ROOMS_PER_FLOOR;

        for ($floor = 1; $floor <= $floors; $floor++) {
            for ($room = 1; $room <= $roomsPerFloor; $room++) {
                // Create room number (e.g., 101, 102, 201, 202, etc.)
                $roomNumber = $floor.str_pad($room, 2, '0', STR_PAD_LEFT);

                Room::factory()->create([
                    'room_number' => $roomNumber,
                    'floor' => $floor,
                    'room_type_id' => $roomTypes->random()->id,
                ]);
            }
        }
    }
}
