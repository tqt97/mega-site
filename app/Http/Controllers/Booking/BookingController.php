<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BookingController extends Controller
{
    public function index(Request $request)
    {

        return Inertia::render('Booking/Index');
    }

    public function search(Request $request)
    {
        $request->validate([
            'check_in' => 'required|date|after:today',
            'check_out' => 'required|date|after:check_in',
            'guests' => 'required|integer|min:1|max:6',
        ]);

        $roomTypes = RoomType::with('amenities')
            ->where('capacity', '>=', $request->guests)
            ->whereHas('rooms', function ($query) use ($request) {
                $query->availableBetween($request->check_in, $request->check_out);
            })
            ->orderBy('price_per_night')
            ->get();

        return response()->json([
            'roomTypes' => $roomTypes,
        ]);
    }
}
