<?php
// app/Http/Controllers/ReservationController.php
namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\WeeklySchedule;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    public function teacherReservationsIndex(Request $request)
    {
        $user = Auth::user();

        // Ensure only Non-ICT teachers can access this page
        if (!($user && $user->user_type == 2 && $user->teacher_type == 'Non-ICT')) {
            abort(403, 'Unauthorized access.');
        }

        // Get filter parameters from the request
        $statusFilter = $request->input('status', 'all'); // 'all', 'approved', 'cancelled'
        $selectedMonth = $request->input('month', now()->month);
        $selectedYear = $request->input('year', now()->year);

        $reservationsQuery = Reservation::with('room')
            ->where('teacher_name', $user->first_name . ' ' . $user->last_name); // Filter by authenticated teacher's name

        // Apply status filter
        if ($statusFilter !== 'all') {
            $reservationsQuery->where('status', $statusFilter);
        }

        // Apply month and year filters
        $reservationsQuery->whereMonth('date', $selectedMonth)
            ->whereYear('date', $selectedYear);

        $reservations = $reservationsQuery->orderBy('date', 'desc')
            ->orderBy('start_time', 'asc')
            ->paginate(10); // Or any pagination limit you prefer

        // Prepare data for month and year dropdowns
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = Carbon::create(null, $i, 1)->format('F');
        }

        $years = range(Carbon::now()->year - 5, Carbon::now()->year + 5); // Example: 5 years before and after current year

        return view('teacher.reservations.index', compact(
            'reservations',
            'statusFilter',
            'selectedMonth',
            'selectedYear',
            'months',
            'years'
        ));
    }



    public function teacherCancelReservation(Request $request, $id)
    {
        $user = Auth::user();

        if (!($user && $user->user_type == 2 && $user->teacher_type == 'Non-ICT')) {
            return back()->with('error', 'Unauthorized action.');
        }

        $reservation = Reservation::find($id);

        if (!$reservation) {
            return back()->with('error', 'Reservation not found.');
        }

        if ($reservation->status === 'cancelled') {
            return back()->with('error', 'This reservation is already cancelled.');
        }
        if ($reservation->status === 'completed') {
            return back()->with('error', 'This reservation has already been completed and cannot be cancelled.');
        }

        $reservationDateTime = Carbon::parse($reservation->date . ' ' . $reservation->start_time);

        if (Carbon::now()->greaterThanOrEqualTo($reservationDateTime)) {
            return back()->with('error', 'Cannot cancel a reservation that has already started or passed.');
        }

        $reservation->status = 'cancelled';
        $reservation->remarks = 'Cancelled by teacher: ' . ($user->first_name . ' ' . $user->last_name);
        $reservation->save();

        // Notification for admin about cancelled reservation
        $admin = User::where('user_type', '1')->first();
        if ($admin) {
            $room = Room::find($reservation->room_id); // Assuming room_id is available in reservation
            $roomName = $room ? $room->room_name : 'Unknown Room';
            $formattedDate = Carbon::parse($reservation->date)->format('F d, Y');
            $formattedStartTime = Carbon::parse($reservation->start_time)->format('h:i A');

            Notification::create([
                'user_id' => $admin->id,
                'message' => "Reservation '{$reservation->reference_number}' for '{$roomName}' on {$formattedDate} at {$formattedStartTime} has been cancelled by {$user->first_name} {$user->last_name}."
            ]);
        }

        return back()->with('success', 'Reservation ' . $reservation->reference_number . ' has been cancelled successfully.');
    }
    public function checkAvailability(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'end_time' => 'required'
        ]);

        $date = Carbon::parse($request->date);
        $dayOfWeek = strtolower($date->englishDayOfWeek);

        // Check weekly schedules
        $weeklyConflict = WeeklySchedule::where('room_id', $request->room_id)
            ->where('day', $dayOfWeek)
            ->where(function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('start_time', '<', $request->end_time)
                        ->where('end_time', '>', $request->start_time);
                });
            })->exists();

        // Check reservations
        $reservationConflict = Reservation::where('room_id', $request->room_id)
            ->where('date', $request->date)
            ->where('status', '!=', 'rejected')
            ->where(function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('start_time', '<', $request->end_time)
                        ->where('end_time', '>', $request->start_time);
                });
            })->exists();

        $available = !$weeklyConflict && !$reservationConflict;

        return response()->json([
            'available' => $available,
            'room' => Room::find($request->room_id),
            'message' => $available ? '' : 'The room is not available at the selected time due to a scheduling conflict.'
        ]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'end_time' => 'required',
            'teacher_name' => 'required|string|max:255',
            'subject' => 'nullable|string|max:255'
        ]);

        $user = Auth::user();

        if (!($user && $user->user_type == 2 && $user->teacher_type == 'Non-ICT')) {
            return response()->json([
                'success' => false,
                'message' => 'Only Non-ICT teachers can reserve rooms.'
            ], 403);
        }

        $status = 'approved';

        $reservation = Reservation::create([
            'reference_number' => Reservation::generateReferenceNumber(),
            'room_id' => $request->room_id,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'teacher_name' => $request->teacher_name,
            'subject' => $request->subject,
            'status' => $status
        ]);

        $admin = User::where('user_type', '1')->first();

        // Notification for admin about new reservation
        if ($admin) {
            $room = Room::find($request->room_id);
            $roomName = $room ? $room->room_name : 'Unknown Room';
            $formattedDate = Carbon::parse($request->date)->format('F d, Y');
            $formattedStartTime = Carbon::parse($request->start_time)->format('h:i A');
            $formattedEndTime = Carbon::parse($request->end_time)->format('h:i A');

            Notification::create([
                'user_id' => $admin->id,
                'message' => "A new reservation for '{$roomName}' on {$formattedDate} from {$formattedStartTime} to {$formattedEndTime} has been created by {$request->teacher_name} for the subject '{$request->subject}' (Reference: {$reservation->reference_number})."
            ]);
        }

        return response()->json([
            'success' => true,
            'reference_number' => $reservation->reference_number,
            'status' => $reservation->status
        ]);
    }
    // Add this method to your ReservationController
    public function suggestOptimalSlots(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'date' => 'required|date|after_or_equal:today',
            'duration' => 'required|integer|min:1' // duration in hours
        ]);

        $date = Carbon::parse($request->date);
        $dayOfWeek = strtolower($date->englishDayOfWeek);
        $roomId = $request->room_id;
        $duration = $request->duration;

        // Get all existing schedules for this room/day
        $weeklySchedules = WeeklySchedule::where('room_id', $roomId)
            ->where('day', $dayOfWeek)
            ->orderBy('start_time')
            ->get();

        // Get all reservations for this room/date
        $reservations = Reservation::where('room_id', $roomId)
            ->where('date', $request->date)
            ->where('status', '!=', 'rejected')
            ->orderBy('start_time')
            ->get();

        // Combine and sort all time slots
        $busySlots = collect()
            ->merge($weeklySchedules->map(fn($s) => [
                'start' => $s->start_time,
                'end' => $s->end_time
            ]))
            ->merge($reservations->map(fn($r) => [
                'start' => $r->start_time,
                'end' => $r->end_time
            ]))
            ->sortBy('start');

        // Greedy algorithm to find available slots
        $availableSlots = [];
        $previousEnd = '07:00:00'; // Assuming earliest possible time

        foreach ($busySlots as $slot) {
            $slotStart = $slot['start'];
            $slotEnd = $slot['end'];

            // Calculate time between previous end and current start
            $availableStart = Carbon::parse($previousEnd);
            $availableEnd = Carbon::parse($slotStart);
            $availableDuration = $availableStart->diffInHours($availableEnd);

            if ($availableDuration >= $duration) {
                $availableSlots[] = [
                    'start' => $previousEnd,
                    'end' => $slotStart,
                    'duration' => $availableDuration
                ];
            }

            // Update previous end time
            $previousEnd = max($previousEnd, $slotEnd);
        }

        // Check after last busy slot until end of day (18:00)
        $lastPossible = Carbon::parse('18:00:00');
        $lastEnd = Carbon::parse($previousEnd);
        $finalDuration = $lastEnd->diffInHours($lastPossible);

        if ($finalDuration >= $duration) {
            $availableSlots[] = [
                'start' => $previousEnd,
                'end' => '18:00:00',
                'duration' => $finalDuration
            ];
        }

        return response()->json([
            'available_slots' => $availableSlots,
            'requested_duration' => $duration
        ]);
    }

    public function adminIndex(Request $request)
    {
        // Get current month, year, and room filter or use default values
        $currentMonth = $request->input('month', 'All');
        $currentYear = $request->input('year', date('Y'));
        $currentRoom = $request->input('room_id', 'All');

        $reservationsQuery = Reservation::with('room')
            ->orderBy('date', 'desc')
            ->orderBy('start_time');

        // Apply month filter
        if ($currentMonth !== 'All') {
            $reservationsQuery->whereMonth('date', $currentMonth);
        }

        // Apply year filter
        if ($currentYear !== 'All') {
            $reservationsQuery->whereYear('date', $currentYear);
        }

        // Apply room filter
        if ($currentRoom !== 'All') {
            $reservationsQuery->where('room_id', $currentRoom);
        }

        $reservations = $reservationsQuery->get()
            ->groupBy(function ($item) {
                return Carbon::parse($item->date)->format('Y-m-d');
            });

        // Get all rooms for the filter dropdown
        $rooms = Room::all();

        return view('admin.reservations.index', compact('reservations', 'currentMonth', 'currentYear', 'currentRoom', 'rooms'));
    }

    public function cancel(Request $request, $id)
    {
        $request->validate([
            'remarks' => 'required|string|max:255'
        ]);

        $reservation = Reservation::findOrFail($id);
        $reservation->update([
            'status' => 'cancelled',
            'remarks' => $request->remarks
        ]);

        return redirect()->back()->with('success', 'Reservation cancelled successfully');
    }

    public function checkTicketStatus(Request $request)
    {
        $request->validate([
            'reference_number' => 'required|string'
        ]);

        $reservation = Reservation::with('room')->where('reference_number', $request->reference_number)->first();


        if (!$reservation) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'No reservation found with that reference number.'
            ]);
        }

        $now = now();
        $start = Carbon::parse($reservation->date . ' ' . $reservation->start_time);
        $end = Carbon::parse($reservation->date . ' ' . $reservation->end_time);

        $status = $reservation->status;
        $detailed_status = $status;

        if ($status === 'approved') {
            if ($now < $start) {
                $detailed_status = 'pending';
            } elseif ($now >= $start && $now <= $end) {
                $detailed_status = 'ongoing';
            } elseif ($now > $end) {
                $detailed_status = 'completed';
            }
        } else {
            $detailed_status = 'cancelled';
        }

        return response()->json([
            'status' => 'found',
            'reservation' => $reservation,
            'room_name' => $reservation->room->room_name ?? null,
            'detailed_status' => $detailed_status,
            'current_time' => $now->format('Y-m-d H:i:s')
        ]);
    }
    public function teacherCreate()
    {
        $rooms = Room::all(); // Fetch all rooms
        return view('teacher.reservations.create', compact('rooms'));
    }
}
