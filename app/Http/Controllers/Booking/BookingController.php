<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\SearchRequest;
use App\Http\Requests\CreateBookingRequest;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\RoomType;
use App\Services\Bookings\PricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function __construct(
        private PricingService $pricingService
    ) {}

    public function index(): Response
    {
        return Inertia::render('Booking/Index');
    }

    /**
     * Handle search request from the user
     */
    public function search(SearchRequest $request): JsonResponse
    {
        $roomTypes = RoomType::with([
            'amenities:id,name',
            'rooms' => fn ($query) => $query->availableBetween($request->check_in, $request->check_out),
        ])
            ->where('capacity', '>=', $request->guests)
            ->whereHas('rooms', fn ($query) => $query->availableBetween($request->check_in, $request->check_out))
            ->orderBy('price_per_night')
            ->select('id', 'name', 'capacity', 'price_per_night', 'size', 'description')
            ->get();

        return response()->json(['roomTypes' => $roomTypes]);
    }

    /**
     * Create a new booking.
     */
    public function create(CreateBookingRequest $request): JsonResponse
    {
        $roomType = RoomType::findOrFail($request->room_type_id);
        $rooms = $roomType->rooms()->availableBetween($request->check_in, $request->check_out)
            ->select('id', 'floor', 'room_number')
            ->groupBy('floor', 'room_number')
            ->orderBy('floor')
            ->orderBy('room_number')
            ->get();

        $pricing = $this->pricingService->calculateBookingPrice(
            $roomType,
            $request->check_in,
            $request->check_out
        );

        return response()->json([
            'roomType' => $roomType,
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'guests' => $request->guests,
            'pricing' => $pricing,
            'rooms' => $rooms,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreBookingRequest $request): JsonResponse
    {
        $roomType = RoomType::findOrFail($request->room_type_id);

        $room = $roomType->rooms()->availableBetween($request->check_in, $request->check_out)
            ->where('id', $request->room_id)
            ->first();

        if (! $room) {
            return response()->json(['success' => false, 'message' => 'Room is no longer available for the selected dates.']);
        }

        $pricing = $this->pricingService->calculateBookingPrice(
            $roomType,
            $request->check_in,
            $request->check_out
        );

        try {
            DB::beginTransaction();

            $customer = Customer::firstOrCreate(
                ['email' => $request->email],
                ['name' => $request->name]
            );

            $room->safelyBook([
                'room_type_id' => $request->room_type_id,
                'room_id' => $request->room_id,
                'customer_id' => $customer->id,
                'guests' => $request->guests,
                'check_in' => $request->check_in,
                'check_out' => $request->check_out,
                'total_price' => $pricing['total_price'],
            ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Booking created successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
