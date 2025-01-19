<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\SearchRequest;
use App\Models\RoomType;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Booking/Index');
    }

    public function search(SearchRequest $request): JsonResponse
    {
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
