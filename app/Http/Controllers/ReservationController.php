<?php
// app/Http/Controllers/ReservationController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\WeeklySchedule;
use App\Models\Room;
use Illuminate\Support\Str;
use Carbon\Carbon;

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
            'subject' => 'required|string|max:255'
        ]);

        $reservation = Reservation::create([
            'reference_number' => Reservation::generateReferenceNumber(),
            'room_id' => $request->room_id,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'teacher_name' => $request->teacher_name,
            'subject' => $request->subject,
            'status' => 'approved'
        ]);

        return response()->json([
            'success' => true,
            'reference_number' => $reservation->reference_number
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
}
