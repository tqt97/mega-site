import RoomTypeCard from '@/Components/Bookings/RoomTypeCard';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import Loading from '@/icons';
import BookingLayout from '@/Layouts/BookingLayout';
import { SearchResults } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import axios from 'axios';
import React, { FormEventHandler, useState } from 'react';

const Index: React.FC = () => {
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
    ); // State to store search results
    const [loading, setLoading] = useState(false); // State to track loading
    const [showResults, setShowResults] = useState(false);

    // useForm hook to manage form state
    const { data, setData, errors } = useForm<{
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

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();

        // Validate dates
        const checkInDate = new Date(data.check_in);
        const checkOutDate = new Date(data.check_out);

        if (checkOutDate <= checkInDate) {
            errors.check_out = 'Check-out date must be after check-in date';
            return;
        }

        // Set loading state to true
        setLoading(true);

        // Call the API to search for available room types
        axios
            .post(route('bookings.search'), data)
            .then((response) => {
                setSearchResults(response.data);
                setLoading(false);
                setShowResults(true);
            })
            .catch((error) => {
                setLoading(false);
                setShowResults(false);
                console.error(error);
            });
    };

    return (
        <BookingLayout>
            <>
                <Head title="Booking" />

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
                                        value={data.check_in}
                                        min={
                                            new Date(Date.now() + 86400000)
                                                .toISOString()
                                                .split('T')[0]
                                        }
                                        onChange={(e) =>
                                            setData('check_in', e.target.value)
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
                                        value={data.check_out}
                                        min={
                                            new Date(Date.now() + 86400000 * 2)
                                                .toISOString()
                                                .split('T')[0]
                                        }
                                        onChange={(e) =>
                                            setData('check_out', e.target.value)
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
                                        value={data.guests}
                                        onChange={(e) =>
                                            setData(
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
                                    Searching for {data.guests}{' '}
                                    {data.guests > 1 ? 'guests' : 'guest'} from{' '}
                                    {new Date(
                                        data.check_in,
                                    ).toLocaleDateString()}{' '}
                                    to{' '}
                                    {new Date(
                                        data.check_out,
                                    ).toLocaleDateString()}
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
            </>
        </BookingLayout>
    );
};

export default Index;
