import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import BookingLayout from '@/Layouts/BookingLayout';
import { useForm } from '@inertiajs/react';
import React, { FormEventHandler } from 'react';

const Index: React.FC = () => {
    const options = [
        { value: 1, label: '1 Guest' },
        { value: 2, label: '2 Guests' },
        { value: 3, label: '3 Guests' },
        { value: 4, label: '4 Guests' },
        { value: 5, label: '5 Guests' },
        { value: 6, label: '6 Guests' },
    ];

    // useForm hook to manage form state
    const { data, setData, post, processing, errors, reset } = useForm<{
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
        // Submit form data
        console.log(data);
        // If needed, you can make an API request or perform any actions here
        post(route('login'), {
            onFinish: () => {
                reset('check_in');
                reset('check_out');
                reset('guests');
            },
        });
    };

    return (
        <BookingLayout>
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
                            {processing ? (
                                <span className="flex items-center justify-center">
                                    <svg
                                        className="mr-3 h-5 w-5 animate-spin text-white"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <circle
                                            className="opacity-25"
                                            cx="12"
                                            cy="12"
                                            r="10"
                                            stroke="currentColor"
                                            strokeWidth="4"
                                        ></circle>
                                        <path
                                            className="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                        ></path>
                                    </svg>
                                    Searching...
                                </span>
                            ) : (
                                'Search'
                            )}
                        </button>
                    </form>
                </div>
            </div>
        </BookingLayout>
    );
};

export default Index;
