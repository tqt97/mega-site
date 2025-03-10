import { RoomType } from '@/types';

interface RoomTypeCardPropsWithModal {
    roomType: RoomType;
    onBookNow: (roomTypeId: number) => void; // Callback để hiển thị modal
}
export default function RoomTypeCard({
    roomType,
    onBookNow,
}: RoomTypeCardPropsWithModal) {
    // const { roomType } = props;
    return (
        <>
            <div className="mb-4 flex items-start justify-between">
                <h3 className="text-xl font-semibold text-gray-800">
                    {roomType.name}
                </h3>
                <p className="text-lg font-semibold text-indigo-600">
                    ${roomType.price_per_night}/night
                </p>
            </div>

            <div className="mb-4">
                <h4 className="mb-2 mt-2 text-sm font-medium text-gray-700">
                    Room Details:
                </h4>
                <div className="mb-3 flex gap-4 text-sm text-gray-600">
                    <span>{roomType.size} m² </span>
                    <span>•</span>
                    <span>Up to {roomType.capacity} guests</span>
                </div>
                <h4 className="mb-2 text-sm font-medium text-gray-700">
                    Amenities:
                </h4>
                <div className="flex flex-wrap gap-2">
                    {roomType.amenities.map((amenity, index) => {
                        return (
                            <span
                                key={index}
                                className="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-800"
                            >
                                {amenity.name}
                            </span>
                        );
                    })}
                </div>
            </div>

            <p className="mb-4 text-gray-600">{roomType.description}</p>
            <hr />
            <h2 className="text-md mb-2 mt-4 font-medium text-gray-700">
                {/* Available Rooms: {roomType.rooms.length}{' '}
                {roomType.rooms.length === 1 ? 'room' : 'rooms'} */}
            </h2>
            <button
                onClick={() => onBookNow(roomType.id)}
                className="block w-full rounded-md bg-indigo-600 px-4 py-2 text-center text-white transition duration-150 ease-in-out hover:bg-indigo-700"
            >
                Book Now
            </button>
        </>
    );
}
