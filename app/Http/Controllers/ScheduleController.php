<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\WeeklySchedule;

class ScheduleController extends Controller
{

    //teacher
    public function schedulelist(Request $request)
    {
        $user = Auth::user();

        // Fetch only rooms for this subject (efficient query)
        $getRoom = Room::where('subject', $user->subject)->get();

        // Add pagination to reduce data load
        $schedulesQuery = Schedule::with(['room', 'teacher'])
            ->whereIn('status', ['upcoming', 'completed', 'ongoing'])
            ->where('teacher_id', $user->id);

        // Apply date filter if provided
        if ($request->has('month') && $request->has('year')) {
            $schedulesQuery->whereYear('date', $request->year)
                ->whereMonth('date', $request->month);
        }

        // Get paginated results
        $paginatedSchedules = $schedulesQuery->paginate(10);

        // Process only paginated data
        $schedules = $paginatedSchedules->map(function ($schedule) {
            return [
                'id' => $schedule->id, // Include ID for reference
                'date' => $schedule->date,
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
                'status' => $schedule->status,
                'room_id' => $schedule->room_id,
                'room_name' => optional($schedule->room)->room_name,
                'subject' => optional($schedule->teacher)->subject,
            ];
        });

        // Only fetch relevant room schedules - those in the next 30 days
        $allRoomSchedules = Schedule::with(['room'])
            ->whereIn('status', ['upcoming', 'ongoing'])
            ->whereHas('room', function ($query) use ($user) {
                $query->where('subject', $user->subject);
            })
            ->where('date', '>=', now()->format('Y-m-d'))
            ->where('date', '<=', now()->addDays(30)->format('Y-m-d'))
            ->get()
            ->map(function ($schedule) {
                return [
                    'room_id' => $schedule->room_id,
                    'date' => $schedule->date,
                    'start_time' => $schedule->start_time,
                    'end_time' => $schedule->end_time,
                ];
            });

        return view('teacher.schedule.list', compact(
            'getRoom',
            'schedules',
            'allRoomSchedules',
            'paginatedSchedules' // Pass this to enable pagination links
        ));
    }


    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'room_id' => 'required|exists:rooms,id',
        ]);


        // Save the new schedule if valid
        $schedule = Schedule::create([
            'teacher_id' => Auth::id(),
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'room_id' => $request->room_id,
        ]);

        // Convert start and end times to 12-hour format
        $formattedStartTime = date('h:i A', strtotime($schedule->start_time));
        $formattedEndTime = date('h:i A', strtotime($schedule->end_time));

        // Notify Admin
        $admins = User::where('user_type', '1')->get(); // Assuming '1' is the admin user type
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'message' => "A new schedule has been requested by " . Auth::user()->first_name .
                    " on {$schedule->date} from {$formattedStartTime} to {$formattedEndTime}.",
            ]);
        }

        return redirect()->back()->with('success', 'Schedule created successfully!');
    }



    private function GreedyAlgorithm($date, $roomId)
    {
        // Fetch existing schedules for the given date and room
        $existingSchedules = Schedule::where('date', $date)
            ->where('room_id', $roomId)
            ->orderBy('start_time')
            ->get();

        // Initialize available time slots (Assume 8 AM to 6 PM as the full range)
        $availableSlots = [['00:00:00', '23:59:59']];

        foreach ($existingSchedules as $schedule) {
            $newSlots = [];

            foreach ($availableSlots as $slot) {
                [$slotStart, $slotEnd] = $slot;

                // If the scheduled time conflicts, adjust available slots
                if ($schedule->start_time > $slotStart) {
                    $newSlots[] = [$slotStart, $schedule->start_time];
                }

                if ($schedule->end_time < $slotEnd) {
                    $newSlots[] = [$schedule->end_time, $slotEnd];
                }
            }

            $availableSlots = $newSlots;
        }

        return $availableSlots;
    }

    //admin


    public function adminschedulelist()
    {
        $getRoom = Room::all(); // Fetch all rooms
        $schedules = Schedule::with(['room', 'teacher']) // Load room and teacher details
            ->whereIn('status', ['upcoming', 'completed', 'ongoing'])
            ->get()
            ->map(function ($schedule) {
                return [
                    'teacher_name' => optional($schedule->teacher)->first_name, // Get teacher name
                    'subject' => optional($schedule->teacher)->subject,
                    'date' => $schedule->date,
                    'start_time' => $schedule->start_time,
                    'end_time' => $schedule->end_time,
                    'status' => $schedule->status,
                    'room_name' => optional($schedule->room)->room_name, // Get room name safely
                ];
            });

        return view('admin.schedule.list', compact('getRoom', 'schedules'));
    }


    public function accept(Request $request, $id)
    {
        $data = Schedule::findOrFail($id);
        $data->status = 'accepted';
        $data->save();

        // Convert start and end times to 12-hour format
        $start_time = date('h:i A', strtotime($data->start_time));
        $end_time = date('h:i A', strtotime($data->end_time));

        // Store notification for the teacher
        Notification::create([
            'user_id' => $data->teacher_id,
            'message' => "Your schedule on {$data->date} from {$start_time} to {$end_time} has been accepted.",
        ]);

        return redirect()->back()->with('success', "Schedule Accepted");
    }




    public function decline(Request $request, $id)
    {
        $request->validate([
            'remarks' => 'required|string|max:255'
        ]);

        $data = Schedule::findOrFail($id);
        $data->status = 'declined';
        $data->remarks = $request->remarks;
        $data->save();

        // Convert times
        $start_time = date('h:i A', strtotime($data->start_time));
        $end_time = date('h:i A', strtotime($data->end_time));

        // Notification
        Notification::create([
            'user_id' => $data->teacher_id,
            'message' => "Your schedule on {$data->date} from {$start_time} to {$end_time} has been declined. Reason: {$request->remarks}",
        ]);

        return redirect()->back()->with('error', "Schedule Declined");
    }



    public function delete($id)
    {
        $data = Schedule::findOrFail($id);
        $data->delete();

        return redirect()->back()->with('success', "Schedule Successfully Deleted");
    }


    public function getNotifications()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($notifications);
    }


    public function updateOngoingSchedules()
    {
        $now = Carbon::now('Asia/Manila');
        $currentTime = $now->format('H:i:s');
        $today = $now->format('Y-m-d');

        // Only update today's schedules to reduce processing
        // 1. Update "upcoming" schedules that should now be "ongoing"
        $startingSchedules = Schedule::where('status', 'upcoming')
            ->where('date', $today) // Only process today's schedules
            ->where('start_time', '<=', $currentTime)
            ->where('end_time', '>', $currentTime)
            ->get();

        foreach ($startingSchedules as $schedule) {
            $schedule->status = 'ongoing';
            $schedule->save();

            // Create just one notification
            Notification::create([
                'user_id' => $schedule->teacher_id,
                'message' => "Your schedule on {$schedule->date} at {$schedule->start_time} is now ongoing.",
            ]);
        }

        // 2. Update "ongoing" schedules that should now be "completed"
        $endingSchedules = Schedule::where('status', 'ongoing')
            ->where('date', $today) // Only process today's schedules
            ->where('end_time', '<=', $currentTime)
            ->get();

        foreach ($endingSchedules as $schedule) {
            $schedule->status = 'completed';
            $schedule->save();

            // Send notification to teacher
            Notification::create([
                'user_id' => $schedule->teacher_id,
                'message' => "Your schedule on {$schedule->date} from {$schedule->start_time} to {$schedule->end_time} has been marked as completed.",
            ]);

            // Get room name once
            $roomName = $schedule->room->room_name;

            // Notify only admins and relevant teachers instead of ALL users
            $relevantUsers = User::where(function ($query) {
                $query->where('user_type', '1') // Admins
                    ->orWhere('subject', $schedule->room->subject); // Teachers with matching subject
            })->get();

            foreach ($relevantUsers as $user) {
                Notification::create([
                    'user_id' => $user->id,
                    'message' => "Room {$roomName} is now available (finished at {$schedule->end_time}).",
                ]);
            }
        }

        return response()->json(['message' => 'Schedules updated based on current time.']);
    }



    public function markNotificationsAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    // In ScheduleController.php

    public function weeklyScheduleList()
    {
        $user = Auth::user();
        $getRoom = Room::where('subject', $user->subject)->get();

        $weeklySchedules = WeeklySchedule::with(['room', 'teacher'])
            ->where('teacher_id', $user->id)
            ->orderByRaw("FIELD(day, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday')")
            ->orderBy('start_time')
            ->get();

        // Define days of the week
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

        return view('teacher.schedule.weekly', compact('getRoom', 'weeklySchedules', 'days'));
    }

    public function storeWeeklySchedule(Request $request)
    {
        $request->validate([
            'day' => 'required|in:monday,tuesday,wednesday,thursday,friday',
            'start_time' => 'required',
            'end_time' => 'required',
            'room_id' => 'required|exists:rooms,id',
        ]);

        // Check for conflicts
        $conflict = WeeklySchedule::where('room_id', $request->room_id)
            ->where('day', $request->day)
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                    ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                    ->orWhere(function ($q) use ($request) {
                        $q->where('start_time', '<', $request->start_time)
                            ->where('end_time', '>', $request->end_time);
                    });
            })->exists();

        if ($conflict) {
            return back()->with('error', 'There is a schedule conflict for this room and time slot.');
        }

        WeeklySchedule::create([
            'teacher_id' => Auth::id(),
            'room_id' => $request->room_id,
            'day' => $request->day,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        return redirect()->back()->with('success', 'Weekly schedule created successfully!');
    }

    public function deleteWeeklySchedule($id)
    {
        $schedule = WeeklySchedule::findOrFail($id);

        // Optional: Check if the schedule belongs to the current teacher
        if ($schedule->teacher_id != Auth::id()) {
            return redirect()->back()->with('error', 'You can only delete your own schedules.');
        }

        $schedule->delete();

        return redirect()->back()->with('success', 'Schedule deleted successfully!');
    }

    public function viewTeacherSchedule($id)
    {
        $teacher = User::findOrFail($id);
        $weeklySchedules = WeeklySchedule::with(['room'])
            ->where('teacher_id', $id)
            ->orderByRaw("FIELD(day, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday')")
            ->orderBy('start_time')
            ->get();

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

        return view('admin.teacher.schedule', compact('teacher', 'weeklySchedules', 'days'));
    }

    public function allSchedules(Request $request)
    {
        // Get all rooms for the dropdown
        $rooms = Room::all();

        // Base query for schedules
        $query = WeeklySchedule::with(['teacher', 'room'])
            ->orderByRaw("FIELD(day, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday')")
            ->orderBy('start_time');

        // Filter by room if selected
        if ($request->has('room_id') && $request->room_id) {
            $query->where('room_id', $request->room_id);
        }
        if ($request->has('teacher_id') && $request->teacher_id) {
            $query->where('teacher_id', $request->teacher_id);
        }
        $teachers = User::where('user_type', 2)->get();
        $weeklySchedules = $query->get();
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

        return view('admin.schedule.all', compact('weeklySchedules', 'days', 'rooms', 'teachers'));
    }
}
