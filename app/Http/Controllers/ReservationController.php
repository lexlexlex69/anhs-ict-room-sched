<?php
// app/Http/Controllers/ReservationController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\WeeklySchedule;
use App\Models\Room;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
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
            'subject' => 'nullable|string|max:255' // Subject can be nullable
        ]);

        $user = Auth::user();

        // Only allow Non-ICT teachers to make reservations
        if (!($user && $user->user_type == 2 && $user->teacher_type == 'Non-ICT')) {
            return response()->json([
                'success' => false,
                'message' => 'Only Non-ICT teachers can reserve rooms.'
            ], 403); // Forbidden
        }

        // Determine status: Non-ICT teacher reservations are always 'approved'
        $status = 'approved';

        $reservation = Reservation::create([
            'reference_number' => Reservation::generateReferenceNumber(),
            'room_id' => $request->room_id,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'teacher_name' => $request->teacher_name,
            'subject' => $request->subject,
            'status' => $status // Use the determined status
        ]);

        return response()->json([
            'success' => true,
            'reference_number' => $reservation->reference_number,
            'status' => $reservation->status // Return the actual status
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

    public function adminIndex()
    {
        $reservations = Reservation::with('room')
            ->orderBy('date', 'desc')
            ->orderBy('start_time')
            ->get()
            ->groupBy(function ($item) {
                return Carbon::parse($item->date)->format('Y-m-d');
            });

        return view('admin.reservations.index', compact('reservations'));
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
