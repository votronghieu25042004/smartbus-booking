<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Trip;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\RouteFare;
use App\Models\Booking;
use App\Models\BookingSeat;
use App\Models\AuditLog;
use App\Services\SeatSegmentService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TripController extends Controller
{
    public function home()
    {
        $routes = Route::with(['stops' => fn($q) => $q->orderBy('stop_order', 'asc')])->get();
        $trips = Trip::with(['route.stops', 'bus', 'driver'])
            ->whereIn('status', ['OPEN_FOR_SALE', 'SCHEDULED', 'BOARDING', 'IN_TRANSIT'])
            ->orderBy('departure_time', 'asc')
            ->take(6)
            ->get()
            ->map(function ($t) {
                return [
                    'id' => $t->id,
                    'trip_code' => $t->trip_code,
                    'route_name' => $t->route ? $t->route->name : '',
                    'origin' => $t->route ? $t->route->origin : '',
                    'destination' => $t->route ? $t->route->destination : '',
                    'departure_time' => $t->departure_time->format('H:i'),
                    'departure_date' => $t->departure_time->format('d/m/Y'),
                    'arrival_time' => $t->arrival_time ? $t->arrival_time->format('H:i') : null,
                    'price' => (float) $t->base_price,
                    'bus_type' => $t->bus ? $t->bus->bus_type : 'Giường nằm',
                    'license_plate' => $t->bus ? $t->bus->license_plate : '',
                    'status' => $t->status,
                ];
            });

        return Inertia::render('Home', [
            'routes' => $routes,
            'featuredTrips' => $trips,
            'user' => Auth::user(),
        ]);
    }

    public function search(Request $request)
    {
        $origin = $request->input('origin');
        $destination = $request->input('destination');
        $date = $request->input('date');

        $query = Trip::with(['route.stops', 'bus', 'driver'])
            ->whereIn('status', ['OPEN_FOR_SALE', 'SCHEDULED', 'BOARDING']);

        if ($origin) {
            $query->whereHas('route', fn($q) => $q->where('origin', 'like', "%{$origin}%"));
        }
        if ($destination) {
            $query->whereHas('route', fn($q) => $q->where('destination', 'like', "%{$destination}%"));
        }
        if ($date) {
            $query->whereDate('departure_time', Carbon::parse($date));
        }

        $trips = $query->orderBy('departure_time', 'asc')->get()->map(function ($t) {
            return [
                'id' => $t->id,
                'trip_code' => $t->trip_code,
                'route_name' => $t->route->name,
                'origin' => $t->route->origin,
                'destination' => $t->route->destination,
                'departure_time' => $t->departure_time->format('H:i'),
                'departure_date' => $t->departure_time->format('d/m/Y'),
                'arrival_time' => $t->arrival_time ? $t->arrival_time->format('H:i') : null,
                'price' => (float) $t->base_price,
                'bus_type' => $t->bus->bus_type,
                'license_plate' => $t->bus->license_plate,
                'amenities' => $t->bus->amenities ?? [],
                'status' => $t->status,
            ];
        });

        return Inertia::render('Trips/Index', [
            'trips' => $trips,
            'filters' => [
                'origin' => $origin,
                'destination' => $destination,
                'date' => $date,
            ],
            'user' => Auth::user(),
        ]);
    }

    public function show(Request $request, $id)
    {
        $trip = Trip::with(['route.stops' => fn($q) => $q->orderBy('stop_order', 'asc'), 'bus', 'driver'])->findOrFail($id);

        $pickupStopId = $request->input('pickup_stop_id', $trip->route->stops->first()->id);
        $dropoffStopId = $request->input('dropoff_stop_id', $trip->route->stops->last()->id);

        $pStop = RouteStop::find($pickupStopId) ?? $trip->route->stops->first();
        $dStop = RouteStop::find($dropoffStopId) ?? $trip->route->stops->last();

        $occupiedSeats = SeatSegmentService::getOccupiedSeats($trip->id, $pStop->stop_order, $dStop->stop_order);
        $baseFare = SeatSegmentService::getFare($trip->route_id, $pStop->id, $dStop->id, $trip->bus->bus_type);

        // Giá vé và tên loại ghế cho từng tầng:
        // Tầng 1: Ghế VIP / Giường dưới (êm ái, tiện di chuyển)
        // Tầng 2: Giường tầng trên (thoáng mát, tiết kiệm hơn hoặc bằng giá)
        $floor1Price = $baseFare;
        $floor2Price = round($baseFare * 0.95); // Giảm nhẹ 5% cho tầng trên hoặc đồng giá nếu vé ngắn

        $floor1SeatType = str_contains(strtolower($trip->bus->bus_type), 'ngồi') ? 'Ghế Ngồi Cao Cấp' : 'Giường Nằm VIP (Tầng Dưới)';
        $floor2SeatType = 'Giường Nằm Tiêu Chuẩn (Tầng Trên)';

        // Chuẩn hóa sơ đồ xe 3 Dãy (Dãy A - Trái, Dãy B - Giữa, Dãy C - Phải) có lối đi ở giữa
        // Tầng 1: A01..A18
        $floor1Rows = [];
        $f1Count = 18;
        for ($row = 0; $row < 6; $row++) {
            $numLeft = sprintf('A%02d', $row * 3 + 1);
            $numMid = sprintf('A%02d', $row * 3 + 2);
            $numRight = sprintf('A%02d', $row * 3 + 3);

            $floor1Rows[] = [
                'left' => [
                    'seat_number' => $numLeft,
                    'floor' => 1,
                    'price' => $floor1Price,
                    'seat_type' => $floor1SeatType,
                    'status' => in_array($numLeft, $occupiedSeats) ? 'booked' : 'available',
                ],
                'middle' => [
                    'seat_number' => $numMid,
                    'floor' => 1,
                    'price' => $floor1Price,
                    'seat_type' => $floor1SeatType,
                    'status' => in_array($numMid, $occupiedSeats) ? 'booked' : 'available',
                ],
                'right' => [
                    'seat_number' => $numRight,
                    'floor' => 1,
                    'price' => $floor1Price,
                    'seat_type' => $floor1SeatType,
                    'status' => in_array($numRight, $occupiedSeats) ? 'booked' : 'available',
                ]
            ];
        }

        // Tầng 2: B01..B18
        $floor2Rows = [];
        for ($row = 0; $row < 6; $row++) {
            $numLeft = sprintf('B%02d', $row * 3 + 1);
            $numMid = sprintf('B%02d', $row * 3 + 2);
            $numRight = sprintf('B%02d', $row * 3 + 3);

            $floor2Rows[] = [
                'left' => [
                    'seat_number' => $numLeft,
                    'floor' => 2,
                    'price' => $floor2Price,
                    'seat_type' => $floor2SeatType,
                    'status' => in_array($numLeft, $occupiedSeats) ? 'booked' : 'available',
                ],
                'middle' => [
                    'seat_number' => $numMid,
                    'floor' => 2,
                    'price' => $floor2Price,
                    'seat_type' => $floor2SeatType,
                    'status' => in_array($numMid, $occupiedSeats) ? 'booked' : 'available',
                ],
                'right' => [
                    'seat_number' => $numRight,
                    'floor' => 2,
                    'price' => $floor2Price,
                    'seat_type' => $floor2SeatType,
                    'status' => in_array($numRight, $occupiedSeats) ? 'booked' : 'available',
                ]
            ];
        }

        return Inertia::render('Trips/Show', [
            'trip' => [
                'id' => $trip->id,
                'trip_code' => $trip->trip_code,
                'departure_time' => $trip->departure_time->format('H:i'),
                'departure_date' => $trip->departure_time->format('d/m/Y'),
                'arrival_time' => $trip->arrival_time ? $trip->arrival_time->format('H:i') : null,
                'base_price' => $baseFare,
                'floor1_price' => $floor1Price,
                'floor2_price' => $floor2Price,
                'floor1_type' => $floor1SeatType,
                'floor2_type' => $floor2SeatType,
                'bus_type' => $trip->bus->bus_type,
                'license_plate' => $trip->bus->license_plate,
                'amenities' => $trip->bus->amenities ?? [],
                'driver' => $trip->driver,
                'status' => $trip->status,
            ],
            'stops' => $trip->route->stops,
            'selectedPickup' => $pStop,
            'selectedDropoff' => $dStop,
            'floor1Rows' => $floor1Rows,
            'floor2Rows' => $floor2Rows,
            'occupiedSeats' => $occupiedSeats,
            'user' => Auth::user(),
        ]);
    }

    public function book(Request $request, $id)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'pickup_stop_id' => 'required|exists:route_stops,id',
            'dropoff_stop_id' => 'required|exists:route_stops,id',
            'seat_numbers' => 'required|array|min:1',
            'payment_choice' => 'required|in:FULL,DEPOSIT', // Thanh toán 100% hoặc Cọc 30%
        ]);

        $trip = Trip::with(['route.stops', 'bus'])->findOrFail($id);
        $pickupStop = RouteStop::findOrFail($request->pickup_stop_id);
        $dropoffStop = RouteStop::findOrFail($request->dropoff_stop_id);

        if ($pickupStop->stop_order >= $dropoffStop->stop_order) {
            return back()->with('error', 'Điểm trả phải nằm sau điểm đón!');
        }

        // Rule 8: Kiểm tra trạng thái xe
        if (!in_array($trip->status, ['SCHEDULED', 'OPEN_FOR_SALE', 'BOARDING'])) {
            return back()->with('error', 'Chuyến xe đã khởi hành, cổng đặt vé online đã đóng!');
        }

        // Check seat segment occupancy
        $occupiedSeats = SeatSegmentService::getOccupiedSeats($trip->id, $pickupStop->stop_order, $dropoffStop->stop_order);
        foreach ($request->seat_numbers as $sNum) {
            if (in_array($sNum, $occupiedSeats)) {
                return back()->with('error', "Ghế {$sNum} đã có người đặt trên đoạn đường này!");
            }
        }

        $baseFare = SeatSegmentService::getFare($trip->route_id, $pickupStop->id, $dropoffStop->id, $trip->bus->bus_type);
        $floor1Price = $baseFare;
        $floor2Price = round($baseFare * 0.95);

        $totalAmount = 0;
        foreach ($request->seat_numbers as $sNum) {
            $isFloor2 = str_starts_with($sNum, 'B');
            $totalAmount += ($isFloor2 ? $floor2Price : $floor1Price);
        }

        $isFull = ($request->payment_choice === 'FULL');
        $paidAmount = $isFull ? $totalAmount : ($totalAmount * 0.3);
        $paymentStatus = $isFull ? 'PAID' : 'PARTIALLY_PAID';

        $bookingCode = 'FUTA-' . strtoupper(Str::random(5));
        $qrToken = 'QR-' . Str::uuid()->toString();

        $booking = Booking::create([
            'booking_code' => $bookingCode,
            'user_id' => Auth::id(),
            'trip_id' => $trip->id,
            'booking_source' => 'ONLINE',
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_email' => $request->customer_email,
            'pickup_stop_id' => $pickupStop->id,
            'dropoff_stop_id' => $dropoffStop->id,
            'pickup_order' => $pickupStop->stop_order,
            'dropoff_order' => $dropoffStop->stop_order,
            'total_seats' => count($request->seat_numbers),
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'payment_status' => $paymentStatus,
            'payment_method' => 'ONLINE',
            'checkin_status' => 'PENDING',
            'qr_token' => $qrToken,
            'notes' => $request->notes,
        ]);

        foreach ($request->seat_numbers as $sNum) {
            $isFloor2 = str_starts_with($sNum, 'B');
            $seatFare = $isFloor2 ? $floor2Price : $floor1Price;

            BookingSeat::create([
                'booking_id' => $booking->id,
                'trip_id' => $trip->id,
                'seat_number' => $sNum,
                'floor' => $isFloor2 ? 2 : 1,
                'pickup_order' => $pickupStop->stop_order,
                'dropoff_order' => $dropoffStop->stop_order,
                'price' => $seatFare,
            ]);
        }

        AuditLog::log('CREATE_ONLINE_BOOKING', "Khách {$request->customer_name} đặt online vé {$booking->booking_code} (" . implode(',', $request->seat_numbers) . ", {$totalAmount}đ, Đã thanh toán: {$paidAmount}đ)", 'Booking', $booking->id);

        return redirect()->route('booking.ticket', ['code' => $booking->booking_code])
            ->with('success', 'Đặt vé thành công! Mã QR vé điện tử đã được kích hoạt.');
    }

    public function ticket($code)
    {
        $booking = Booking::with(['trip.route.stops', 'trip.bus', 'trip.driver', 'pickupStop', 'dropoffStop', 'bookingSeats', 'review'])
            ->where('booking_code', $code)
            ->firstOrFail();

        return Inertia::render('Bookings/Ticket', [
            'booking' => [
                'id' => $booking->id,
                'booking_code' => $booking->booking_code,
                'customer_name' => $booking->customer_name,
                'customer_phone' => $booking->customer_phone,
                'customer_email' => $booking->customer_email,
                'seats' => $booking->bookingSeats->pluck('seat_number')->toArray(),
                'from_location' => $booking->pickupStop ? $booking->pickupStop->stop_name : 'Trạm đón',
                'to_location' => $booking->dropoffStop ? $booking->dropoffStop->stop_name : 'Trạm trả',
                'total_amount' => (float) $booking->total_amount,
                'paid_amount' => (float) $booking->paid_amount,
                'remaining_due' => (float) ($booking->total_amount - $booking->paid_amount),
                'payment_status' => $booking->payment_status,
                'checkin_status' => $booking->checkin_status,
                'checked_in_at' => $booking->checked_in_at ? $booking->checked_in_at->format('H:i - d/m/Y') : null,
                'qr_token' => $booking->qr_token,
                'departure_time' => $booking->trip->departure_time->format('H:i'),
                'departure_date' => $booking->trip->departure_time->format('d/m/Y'),
                'bus_type' => $booking->trip->bus->bus_type,
                'license_plate' => $booking->trip->bus->license_plate,
                'driver' => $booking->trip->driver,
                'has_reviewed' => $booking->review !== null,
            ],
            'user' => Auth::user(),
        ]);
    }
}
