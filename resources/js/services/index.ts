import { BookingFormData, CreateBookingData, SearchData } from '@/types';
import axios from 'axios';

export const searchAvailableRooms = (searchData: SearchData) =>
    axios.post(route('bookings.search'), searchData);

export const createBooking = (bookingData: CreateBookingData) =>
    axios.post(route('bookings.create'), bookingData);

export const storeBooking = (bookingFormData: BookingFormData) =>
    axios.post(route('bookings.store'), bookingFormData);
