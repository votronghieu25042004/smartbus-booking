<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Route;
use App\Models\Trip;
use App\Models\Seat;
use App\Models\Booking;
use App\Models\BookingSeat;
use App\Models\Driver;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Support\Str;

class BusApiController extends Controller
{
    // 1. API Lấy danh sách điểm đi và điểm đến
    public function getLocations()
    {
        $departures = Route::select('departure_location')->distinct()->pluck('departure_location');
        $arrivals = Route::select('arrival_location')->distinct()->pluck('arrival_location');

        return response()->json([
            'success' => true,
            'data' => [
                'departures' => $departures,
                'arrivals' => $arrivals,
            ]
        ]);
    }

    // 2. API Lấy danh sách tuyến đường
    public function getRoutes()
    {
        $routes = Route::withCount('trips')->get();
        return response()->json([
            'success' => true,
            'data' => $routes,
        ]);
    }

    // 3. API Tìm kiếm Chuyến xe FUTA (có lọc điểm đi, điểm đến, ngày)
    public function getTrips(Request $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        $busType = $request->input('bus_type');

        $query = Trip::with(['route', 'bus', 'driver', 'seats'])
            ->whereDate('departure_time', $date);

        if ($from) {
            $query->whereHas('route', fn($q) => $q->where('departure_location', 'like', "%{$from}%"));
        }
        if ($to) {
            $query->whereHas('route', fn($q) => $q->where('arrival_location', 'like', "%{$to}%"));
        }
        if ($busType) {
            $query->whereHas('bus', fn($q) => $q->where('bus_type', 'like', "%{$busType}%"));
        }

        $trips = $query->orderBy('departure_time', 'asc')->get()->map(function ($trip) {
            return [
                'id' => $trip->id,
                'from_location' => $trip->route->departure_location,
                'to_location' => $trip->route->arrival_location,
                'departure_time' => $trip->departure_time->format('H:i'),
                'departure_date' => $trip->departure_time->format('d/m/Y'),
                'arrival_time' => $trip->arrival_time->format('H:i'),
                'price' => (float) $trip->price,
                'deposit_30_percent' => (float) ($trip->price * 0.3),
                'remaining_70_percent' => (float) ($trip->price * 0.7),
                'bus_type' => $trip->bus->bus_type,
                'license_plate' => $trip->bus->license_plate,
                'amenities' => $trip->bus->amenities ?? [],
                'driver' => $trip->driver ? [
                    'id' => $trip->driver->id,
                    'name' => $trip->driver->name,
                    'rating' => (float) $trip->driver->avg_rating,
                    'avatar' => $trip->driver->avatar,
                ] : null,
                'available_seats' => $trip->seats->where('status', 'available')->count(),
                'total_seats' => $trip->bus->total_seats,
            ];
        });

        return response()->json([
            'success' => true,
            'total' => $trips->count(),
            'data' => $trips,
        ]);
    }

    // 4. API Chi tiết chuyến xe & Sơ đồ ghế 2 tầng
    public function getTripDetail($id)
    {
        $trip = Trip::with(['route', 'bus', 'driver', 'seats'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'trip_id' => $trip->id,
                'from' => $trip->route->departure_location,
                'to' => $trip->route->arrival_location,
                'departure_time' => $trip->departure_time->format('H:i - d/m/Y'),
                'price' => (float) $trip->price,
                'deposit_percent' => $trip->deposit_percent,
                'bus' => [
                    'license_plate' => $trip->bus->license_plate,
                    'bus_type' => $trip->bus->bus_type,
                    'amenities' => $trip->bus->amenities,
                ],
                'driver' => $trip->driver,
                'floor1_seats' => $trip->seats->where('floor', 1)->values(),
                'floor2_seats' => $trip->seats->where('floor', 2)->values(),
            ]
        ]);
    }

    // 5. API Đặt vé & Tính cọc 30%
    public function createBooking(Request $request)
    {
        $validated = $request->validate([
            'trip_id' => 'required|exists:trips,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'required|email|max:255',
            'seat_ids' => 'required|array|min:1',
            'seat_ids.*' => 'exists:seats,id',
            'notes' => 'nullable|string',
        ]);

        $trip = Trip::findOrFail($validated['trip_id']);
        $seats = Seat::whereIn('id', $validated['seat_ids'])
            ->where('trip_id', $trip->id)
            ->where('status', 'available')
            ->get();

        if ($seats->count() !== count($validated['seat_ids'])) {
            return response()->json([
                'success' => false,
                'message' => 'Một số ghế đã có người đặt, vui lòng chọn lại ghế khác.',
            ], 422);
        }

        $totalAmount = $seats->sum('price');
        $depositAmount = $totalAmount * 0.3;
        $remainingAmount = $totalAmount * 0.7;

        $bookingCode = 'FUTA-' . strtoupper(Str::random(5));
        $qrToken = 'QR-' . Str::uuid()->toString();

        $booking = Booking::create([
            'booking_code' => $bookingCode,
            'trip_id' => $trip->id,
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'customer_email' => $validated['customer_email'],
            'total_seats' => $seats->count(),
            'total_amount' => $totalAmount,
            'deposit_amount' => $depositAmount,
            'remaining_amount' => $remainingAmount,
            'payment_status' => 'deposit_paid',
            'checkin_status' => 'pending',
            'qr_token' => $qrToken,
            'notes' => $validated['notes'] ?? null,
        ]);

        foreach ($seats as $seat) {
            $seat->update(['status' => 'booked']);
            BookingSeat::create([
                'booking_id' => $booking->id,
                'seat_id' => $seat->id,
                'seat_number' => $seat->seat_number,
                'price' => $seat->price,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Đặt vé và cọc 30% thành công!',
            'data' => [
                'booking_code' => $booking->booking_code,
                'qr_token' => $booking->qr_token,
                'total_amount' => $totalAmount,
                'deposit_paid' => $depositAmount,
                'remaining_due' => $remainingAmount,
                'seats' => $seats->pluck('seat_number'),
            ]
        ]);
    }

    // 6. API Tra cứu vé & QR Token
    public function getTicket($code)
    {
        $booking = Booking::with(['trip.route', 'trip.bus', 'trip.driver', 'bookingSeats', 'review'])
            ->where('booking_code', $code)
            ->orWhere('qr_token', $code)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => [
                'booking_code' => $booking->booking_code,
                'customer_name' => $booking->customer_name,
                'customer_phone' => $booking->customer_phone,
                'customer_email' => $booking->customer_email,
                'seats' => $booking->bookingSeats->pluck('seat_number'),
                'total_amount' => (float) $booking->total_amount,
                'deposit_amount' => (float) $booking->deposit_amount,
                'remaining_amount' => (float) $booking->remaining_amount,
                'checkin_status' => $booking->checkin_status,
                'checked_in_at' => $booking->checked_in_at ? $booking->checked_in_at->format('H:i d/m/Y') : null,
                'trip' => [
                    'departure_time' => $booking->trip->departure_time->format('H:i - d/m/Y'),
                    'from' => $booking->trip->route->departure_location,
                    'to' => $booking->trip->route->arrival_location,
                    'driver' => $booking->trip->driver ? $booking->trip->driver->name : 'Đang phân công',
                    'license_plate' => $booking->trip->bus->license_plate,
                ],
                'review' => $booking->review,
            ]
        ]);
    }

    // 7. API Danh sách Tài xế & Đánh giá năng lực
    public function getDrivers()
    {
        $drivers = Driver::with('reviews')->withCount('trips')->get();
        return response()->json([
            'success' => true,
            'data' => $drivers,
        ]);
    }
}
