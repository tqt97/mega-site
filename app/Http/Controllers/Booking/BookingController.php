<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class BookingController extends Controller
{
    public function index()
    {
        return Inertia::render('Booking/Index');
    }
}
