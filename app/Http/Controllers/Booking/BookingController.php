<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\SearchRequest;
use App\Http\Requests\CreateBookingRequest;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Customer;
use App\Models\RoomType;
use App\Services\Bookings\PricingService;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function __construct(
        private readonly PricingService $pricingService
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
        try {
            $roomTypes = RoomType::with([
                'amenities:id,name',
                'rooms' => fn ($query) => $query->availableBetween($request->check_in, $request->check_out)
                    ->select('id', 'floor', 'room_number', 'room_type_id', 'is_available'),
            ])
                ->where('capacity', '>=', $request->guests)
                ->whereHas('rooms', fn ($query) => $query->availableBetween($request->check_in, $request->check_out))
                ->orderBy('price_per_night')
                ->select('id', 'name', 'capacity', 'price_per_night', 'size', 'description')
                ->get();

            if ($roomTypes->isEmpty()) {
                return response()->json(['message' => 'No room types available for the given criteria', 'roomTypes' => []]);
            }

            return response()->json(['roomTypes' => $roomTypes]);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Database query error', 'error' => $e->getMessage()], 500);
        } catch (Exception) {
            return response()->json(['message' => 'An error occurred while processing your request'], 500);
        }
    }

    /**
     * Create a new booking.
     */
    public function create(CreateBookingRequest $request): JsonResponse
    {
        try {
            $roomType = RoomType::findOrFail($request->room_type_id);
            $rooms = $roomType->availableRooms($request->check_in, $request->check_out)
                ->select('id', 'floor', 'room_number')
                ->groupBy('floor', 'room_number')
                ->orderBy('floor')
                ->orderBy('room_number')
                ->get();

            if ($rooms->isEmpty()) {
                return response()->json(['message' => 'No rooms available for the given dates', 'rooms' => []]);
            }

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
        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred while processing your request',
                'errors' => $e->getMessage(),
            ], 500);
        }
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

        $room = $roomType->availableRooms($request->check_in, $request->check_out)
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
        } catch (ValidationException $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => 'Validation error: '.$e->getMessage()]);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => 'Database error: '.$e->getMessage()]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => 'An error occurred: '.$e->getMessage()]);
        }
    }
}
