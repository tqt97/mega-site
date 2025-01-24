import FlashMessage from '@/Components/Bookings/FlashMessage';
import Modal from '@/Components/Bookings/Modal';
import RoomTypeCard from '@/Components/Bookings/RoomTypeCard';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import Loading from '@/icons';
import BookingLayout from '@/Layouts/BookingLayout';
import { createBooking, searchAvailableRooms, storeBooking } from '@/services';
import { BookingData, SearchResults } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';
export default function Index() {
    const options = [
        { value: 1, label: '1 Guest' },
        { value: 2, label: '2 Guests' },
        { value: 3, label: '3 Guests' },
        { value: 4, label: '4 Guests' },
        { value: 5, label: '5 Guests' },
        { value: 6, label: '6 Guests' },
    ];
    const [searchResults, setSearchResults] = useState<SearchResults | null>(
        null,
    );
    const [loading, setLoading] = useState(false);
    const [showResults, setShowResults] = useState(false);

    const [isModalOpen, setIsModalOpen] = useState(false);
    const [bookingData, setBookingData] = useState<BookingData | null>(null);

    const [flashMessage, setFlashMessage] = useState<string | null>(null);
    // useForm hook to manage form state
    const {
        data: searchData,
        setData: setSearchData,
        errors,
        reset: resetSearchData,
    } = useForm<{
        check_in: string;
        check_out: string;
        guests: number;
    }>({
        check_in: new Date(Date.now() + 86400000).toISOString().split('T')[0],
        check_out: new Date(Date.now() + 86400000 * 7)
            .toISOString()
            .split('T')[0],
        guests: 1,
    });

    const {
        data: bookingFormData,
        setData: setBookingFormData,
        reset: resetBookingFormData,
    } = useForm<{
        room_type_id: number | null;
        check_in: string;
        check_out: string;
        guests: number;
        name: string;
        email: string;
        room_id: number | null;
    }>({
        room_type_id: null,
        check_in: searchData.check_in,
        check_out: searchData.check_out,
        guests: searchData.guests,
        name: '',
        email: '',
        room_id: null,
    });

    const resetState = () => {
        setIsModalOpen(false);
        resetBookingFormData();
        resetSearchData();
        setBookingData(null);
        setSearchResults(null);
        setShowResults(false);
    };

    const validateSearchData = (data: {
        check_in: string;
        check_out: string;
        guests: number;
    }) => {
        const errors = {} as { check_out?: string; guests?: string };
        const checkInDate = new Date(data.check_in);
        const checkOutDate = new Date(data.check_out);

        if (checkOutDate <= checkInDate) {
            errors.check_out = 'Check-out date must be after check-in date';
        }

        if (!data.guests || data.guests <= 0) {
            errors.guests = 'Number of guests must be greater than 0';
        }

        return errors;
    };
    // Search for available room types
    const handleSubmit: FormEventHandler = async (e) => {
        e.preventDefault();

        const validationErrors = validateSearchData(searchData);
        if (Object.keys(validationErrors).length > 0) {
            errors.check_out = 'Check-out date must be after check-in date';
            return;
        }

        // Set loading state to true
        setLoading(true);

        // Call the API to search for available room types
        try {
            const response = await searchAvailableRooms(searchData);
            setSearchResults(response.data);
            setShowResults(true);
        } catch (error) {
            setFlashMessage('Failed to search for rooms.');
            console.error(error);
        } finally {
            setLoading(false);
        }
    };

    // Create
    const handleBookNow = async (roomTypeId: number) => {
        setBookingFormData((prevData) => ({
            ...prevData,
            room_type_id: roomTypeId,
        }));
        // call api to get pricing
        try {
            const response = await createBooking({
                room_type_id: roomTypeId,
                check_in: searchData.check_in,
                check_out: searchData.check_out,
                guests: searchData.guests,
            });
            setBookingData(response.data);
            setBookingFormData((prevData) => ({
                ...prevData,
                room_id: response.data.rooms[0]?.id || null,
            }));
            setIsModalOpen(true);
        } catch (error) {
            setFlashMessage('Booking failed.');
            console.error(error);
            resetState();
        }
    };

    const closeModal = () => {
        setIsModalOpen(false);
    };

    // Store
    const handleBooking: FormEventHandler = async (e) => {
        e.preventDefault();
        if (!bookingFormData.name || !bookingFormData.email) {
            alert('Please enter your name and email');
            return;
        }
        if (!confirm('Are you sure you want to book this room?')) {
            return;
        }

        setLoading(true);

        try {
            await storeBooking(bookingFormData);
            setFlashMessage('Booking successful!');
            setTimeout(() => {
                setFlashMessage(null);
            }, 3000);
            resetState();
        } catch (error) {
            setFlashMessage('Booking failed.');
            console.error(error);
            resetState();
        } finally {
            setLoading(false);
        }
    };
    return (
        <BookingLayout>
            <>
                <Head title="Booking" />
                {flashMessage && (
                    <FlashMessage message={flashMessage} type="success" />
                )}
                <div className="mx-auto max-w-3xl">
                    <div className="rounded-lg bg-white p-6 shadow-xl">
                        <h2 className="mb-6 text-2xl font-semibold text-gray-800">
                            Find Your Perfect Room
                        </h2>
                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div>
                                    <InputLabel
                                        htmlFor="check_in"
                                        value="Check In"
                                    />
                                    <TextInput
                                        type="date"
                                        name="check_in"
                                        id="check_in"
                                        value={searchData.check_in}
                                        min={
                                            new Date(Date.now() + 86400000)
                                                .toISOString()
                                                .split('T')[0]
                                        }
                                        onChange={(e) =>
                                            setSearchData(
                                                'check_in',
                                                e.target.value,
                                            )
                                        }
                                        className="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    {errors.check_in && (
                                        <div className="mt-1 text-sm text-red-500">
                                            {errors.check_in}
                                        </div>
                                    )}
                                </div>
                                <div>
                                    <InputLabel
                                        htmlFor="check_out"
                                        value="Check Out"
                                    />
                                    <TextInput
                                        type="date"
                                        name="check_out"
                                        id="check_out"
                                        value={searchData.check_out}
                                        min={
                                            new Date(Date.now() + 86400000 * 2)
                                                .toISOString()
                                                .split('T')[0]
                                        }
                                        onChange={(e) =>
                                            setSearchData(
                                                'check_out',
                                                e.target.value,
                                            )
                                        }
                                        className="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    {errors.check_out && (
                                        <div className="mt-1 text-sm text-red-500">
                                            {errors.check_out}
                                        </div>
                                    )}
                                </div>
                                <div>
                                    <label className="mb-1 block text-sm font-medium text-gray-700">
                                        Guests
                                    </label>
                                    <select
                                        name="guests"
                                        id="guests"
                                        value={searchData.guests}
                                        onChange={(e) =>
                                            setSearchData(
                                                'guests',
                                                Number(e.target.value),
                                            )
                                        }
                                        className="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        {options.map((option) => (
                                            <option
                                                key={option.value}
                                                value={option.value}
                                            >
                                                {option.label}
                                            </option>
                                        ))}
                                    </select>
                                    {errors.guests && (
                                        <div className="mt-1 text-sm text-red-500">
                                            {errors.guests}
                                        </div>
                                    )}
                                </div>
                            </div>
                            <button
                                type="submit"
                                className="w-full rounded-md bg-indigo-600 px-4 py-2 text-white transition duration-150 ease-in-out hover:bg-indigo-700"
                            >
                                {loading ? (
                                    <span className="flex items-center justify-center">
                                        <Loading />
                                        Searching...
                                    </span>
                                ) : (
                                    'Search'
                                )}
                            </button>
                        </form>
                    </div>
                </div>
                {/* Display search results */}
                {showResults && (
                    <div className="mx-auto mt-10 max-w-7xl">
                        <div className="rounded-lg bg-white/95 p-6 shadow-xl backdrop-blur-sm">
                            <h2 className="mb-6 text-2xl font-semibold text-gray-800">
                                Available Room Types
                            </h2>

                            <div className="mb-8 rounded-lg bg-gray-50 p-4">
                                <p className="text-gray-600">
                                    Searching for {searchData.guests}{' '}
                                    {searchData.guests > 1 ? 'guests' : 'guest'}{' '}
                                    from{' '}
                                    {new Date(
                                        searchData.check_in,
                                    ).toLocaleDateString()}{' '}
                                    to{' '}
                                    {new Date(
                                        searchData.check_out,
                                    ).toLocaleDateString()}{' '}
                                    :{' '}
                                    <span className="font-semibold">
                                        {searchResults?.roomTypes.length}{' '}
                                        {searchResults &&
                                        searchResults?.roomTypes?.length > 1
                                            ? 'room types'
                                            : 'room type'}
                                    </span>
                                </p>
                            </div>

                            {/* Render search results */}
                            <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                                {searchResults &&
                                searchResults?.roomTypes?.length > 0 ? (
                                    searchResults?.roomTypes.map((roomType) => (
                                        <div
                                            className="overflow-hidden rounded-lg border shadow-md"
                                            key={roomType.id}
                                        >
                                            <div className="aspect-[3/2] w-full">
                                                <img
                                                    src={`/images/room-placeholder.jpg`}
                                                    alt={roomType.name}
                                                    className="h-full w-full object-cover"
                                                />
                                            </div>
                                            <div className="p-6">
                                                <RoomTypeCard
                                                    onBookNow={handleBookNow}
                                                    roomType={roomType}
                                                    key={roomType.id}
                                                />
                                            </div>
                                        </div>
                                    ))
                                ) : (
                                    <div className="col-span-full py-12 text-center">
                                        <h3 className="mb-2 text-xl font-medium text-gray-900">
                                            No Available Rooms
                                        </h3>
                                        <p className="text-gray-600">
                                            Sorry, we couldn't find any room
                                            types matching your criteria.
                                        </p>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                )}
                {/* Modal */}
                {bookingData && isModalOpen && (
                    <Modal
                        show={true}
                        onClose={closeModal}
                        title="Confirm Your Booking"
                    >
                        <form onSubmit={handleBooking}>
                            {/* Summary */}
                            <div className="mb-8 rounded-lg bg-gray-50 p-4">
                                <h3 className="mb-2 text-lg font-semibold text-gray-800">
                                    {bookingData.roomType?.name}
                                </h3>
                                <div className="space-y-1 text-gray-600">
                                    <p>
                                        {bookingData?.pricing?.nights}{' '}
                                        {bookingData?.pricing?.nights > 1
                                            ? 'nights'
                                            : 'night'}
                                    </p>
                                    <p>
                                        {bookingData.guests}{' '}
                                        {bookingData.guests > 1
                                            ? 'guests'
                                            : 'guest'}
                                    </p>
                                    <p>
                                        Check-in:{' '}
                                        {new Date(
                                            bookingData.check_in,
                                        ).toLocaleDateString()}
                                    </p>
                                    <p>
                                        Check-out:{' '}
                                        {new Date(
                                            bookingData.check_out,
                                        ).toLocaleDateString()}
                                    </p>
                                </div>
                            </div>
                            {/* Price */}
                            <div className="mb-8 rounded-lg bg-indigo-50 p-4">
                                <div className="space-y-2">
                                    <div className="flex justify-between text-gray-600">
                                        <span>
                                            $
                                            {
                                                bookingData.pricing
                                                    ?.price_per_night
                                            }{' '}
                                            × {bookingData.pricing.nights}{' '}
                                            nights
                                        </span>
                                        <span>
                                            ${bookingData.pricing.total_price}
                                        </span>
                                    </div>
                                    <div className="flex justify-between border-t border-indigo-100 pt-2 text-lg font-semibold">
                                        <span>Total (USD)</span>
                                        <span>
                                            ${bookingData.pricing.total_price}
                                        </span>
                                    </div>
                                    <p className="mt-2 text-sm text-gray-500">
                                        Payment will be collected upon arrival
                                    </p>
                                </div>
                            </div>
                            {/* Booking form */}
                            <div className="grid grid-cols-1 gap-6">
                                <div>
                                    {bookingData.rooms.length > 0 && (
                                        <>
                                            <InputLabel
                                                htmlFor="customer_name"
                                                value="Select room"
                                                className="mb-1"
                                            />
                                            <div className="flex flex-wrap">
                                                {bookingData.rooms?.map(
                                                    (room, index) => (
                                                        <div
                                                            key={room.id}
                                                            className="mb-2 flex w-1/2 items-center"
                                                        >
                                                            <div className="text-gray-600">
                                                                <input
                                                                    className="mr-2 h-4 w-4 cursor-pointer text-indigo-600 focus:ring-offset-0"
                                                                    type="radio"
                                                                    name="room"
                                                                    value={
                                                                        room.id
                                                                    }
                                                                    checked={
                                                                        (bookingFormData.room_id ===
                                                                            null &&
                                                                            index ===
                                                                                0) ||
                                                                        bookingFormData.room_id ===
                                                                            room.id
                                                                    }
                                                                    onChange={(
                                                                        e,
                                                                    ) =>
                                                                        setBookingFormData(
                                                                            'room_id',
                                                                            Number(
                                                                                e
                                                                                    .target
                                                                                    .value,
                                                                            ),
                                                                        )
                                                                    }
                                                                />
                                                                <span className="ml-2">
                                                                    Floor{' '}
                                                                    {room.floor}{' '}
                                                                    - Room{' '}
                                                                    {
                                                                        room.room_number
                                                                    }
                                                                </span>
                                                            </div>
                                                        </div>
                                                    ),
                                                )}
                                            </div>
                                        </>
                                    )}
                                </div>
                                <div>
                                    <InputLabel
                                        htmlFor="customer_name"
                                        value="Name"
                                        className="mb-1"
                                    />
                                    <TextInput
                                        type="text"
                                        name="name"
                                        id="customer_name"
                                        required
                                        onChange={(e) =>
                                            setBookingFormData(
                                                'name',
                                                e.target.value,
                                            )
                                        }
                                        className="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                </div>

                                <div>
                                    <InputLabel
                                        htmlFor="customer_email"
                                        value="Email"
                                        className="mb-1 block text-sm font-medium text-gray-700"
                                    />
                                    <TextInput
                                        type="email"
                                        name="customer_email"
                                        id="customer_email"
                                        onChange={(e) =>
                                            setBookingFormData(
                                                'email',
                                                e.target.value,
                                            )
                                        }
                                        required
                                        className="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                </div>
                            </div>
                            <div className="mt-4 flex justify-end gap-2">
                                <button
                                    onClick={closeModal}
                                    className="rounded bg-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-400"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    className="rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700"
                                >
                                    Confirm
                                </button>
                            </div>
                        </form>
                    </Modal>
                )}
            </>
        </BookingLayout>
    );
}
