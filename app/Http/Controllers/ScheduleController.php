<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use App\Models\Reservation;
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


    public function adminschedulelist(Request $request)
    {
        $getRoom = Room::all();

        // Get current month and year or use requested values
        $currentMonth = $request->input('month', date('m'));
        $currentYear = $request->input('year', date('Y'));

        // Get all reservations for the selected month
        $reservations = Reservation::with(['room'])
            ->active() // Only show non-cancelled reservations
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->groupBy(function ($item) {
                return \Carbon\Carbon::parse($item->date)->format('Y-m-d');
            });

        return view('admin.schedule.list', compact('getRoom', 'reservations', 'currentMonth', 'currentYear'));
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

        $currentTime = now()->format('h:i A');

        return view('teacher.schedule.weekly', compact('getRoom', 'weeklySchedules', 'days', 'currentTime'));
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
                $query->where(function ($q) use ($request) {
                    // New schedule starts during existing schedule
                    $q->where('start_time', '<', $request->end_time)
                        ->where('end_time', '>', $request->start_time);
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

        $admin = User::where('user_type', '1')->first(); // Assuming '1' is the admin user type

        Notification::create([
            'user_id' => $admin->id,
            'message' => "A new weekly schedule has been created by " . Auth::user()->first_name .
                " for {$request->day} from {$request->start_time} to {$request->end_time}.",
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

        $admin = User::where('user_type', '1')->first(); // Assuming '1' is the admin user type

        Notification::create([
            'user_id' => $admin->id,
            'message' => "A weekly schedule has been deleted by " . Auth::user()->first_name .
                " for {$schedule->day} from {$schedule->start_time} to {$schedule->end_time}.",
        ]);

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

    public function todaySchedule()
    {
        $user = Auth::user();
        $today = now()->format('Y-m-d');
        $now = now();

        // Get today's fixed weekly schedules
        $dayOfWeek = strtolower($now->englishDayOfWeek);
        $weeklySchedules = WeeklySchedule::with('room')
            ->where('teacher_id', $user->id)
            ->where('day', $dayOfWeek)
            ->orderBy('start_time')
            ->get();

        // Get today's one-time schedules
        $oneTimeSchedules = Schedule::with('room')
            ->where('teacher_id', $user->id)
            ->where('date', $today)
            ->orderBy('start_time')
            ->get();

        // Combine and process all schedules
        $allSchedules = $weeklySchedules->merge($oneTimeSchedules)
            ->map(function ($schedule) use ($now) {
                return $this->addScheduleStatus($schedule, $now);
            })
            ->sortBy('start_time');

        // Count schedules by status
        $statusCounts = [
            'pending' => $allSchedules->where('status', 'pending')->count(),
            'ongoing' => $allSchedules->where('status', 'ongoing')->count(),
            'completed' => $allSchedules->where('status', 'completed')->count(),
            'total' => $allSchedules->count()
        ];

        return view('teacher.schedule.today', [
            'schedules' => $allSchedules,
            'currentTime' => $now->format('h:i A'),
            'statusCounts' => $statusCounts
        ]);
    }

    private function addScheduleStatus($schedule, $now)
    {
        $start = Carbon::parse($schedule->date . ' ' . $schedule->start_time);
        $end = Carbon::parse($schedule->date . ' ' . $schedule->end_time);

        if ($now < $start) {
            $schedule->status = 'pending';
            $schedule->status_badge = 'bg-yellow-100 text-yellow-800';
        } elseif ($now >= $start && $now <= $end) {
            $schedule->status = 'ongoing';
            $schedule->status_badge = 'bg-blue-100 text-blue-800';
        } else {
            $schedule->status = 'completed';
            $schedule->status_badge = 'bg-green-100 text-green-800';
        }

        return $schedule;
    }

    public function teacherScheduleCalendar(Request $request)
    {
        $user = Auth::user();
        $now = now();
        // Get current month and year or use requested values
        $currentMonth = $request->input('month', date('m'));
        $currentYear = $request->input('year', date('Y'));

        // Set first day of week to Sunday (0)
        Carbon::setWeekStartsAt(Carbon::SUNDAY);
        Carbon::setWeekEndsAt(Carbon::SATURDAY);

        // Get all weekly schedules for the teacher
        $weeklySchedules = WeeklySchedule::with(['room'])
            ->where('teacher_id', $user->id)
            ->get()
            ->groupBy('day');

        // Prepare weeks of the month
        $weeks = [];
        $startOfMonth = Carbon::create($currentYear, $currentMonth, 1)->startOfMonth();
        $endOfMonth = Carbon::create($currentYear, $currentMonth, 1)->endOfMonth();

        // Start from Sunday of the week containing the 1st of the month
        $currentWeek = $startOfMonth->copy()->startOfWeek(Carbon::SUNDAY);

        while ($currentWeek <= $endOfMonth) {
            $week = [];
            $week['start'] = $currentWeek->copy();
            $week['end'] = $currentWeek->copy()->endOfWeek(Carbon::SATURDAY);

            // Prepare days for this week (Sunday to Saturday)
            $week['days'] = [];
            for ($i = 0; $i < 7; $i++) {
                $dayDate = $currentWeek->copy()->addDays($i);
                $dayName = strtolower($dayDate->format('l'));

                $week['days'][] = [
                    'date' => $dayDate->format('Y-m-d'),
                    'day_name' => $dayName,
                    'is_today' => $dayDate->isToday(),
                    'in_month' => $dayDate->month == $currentMonth,
                    'day_number' => $dayDate->day,
                ];
            }

            $weeks[] = $week;
            $currentWeek->addWeek();
        }
        $currentTime = $now->format('h:i A');

        return view('teacher.schedule.calendar', compact(
            'user',
            'weeklySchedules',
            'weeks',
            'currentMonth',
            'currentYear',
            'startOfMonth',
            'endOfMonth',
            'currentTime'
        ));
    }
    public function adminCalendar(Request $request)
    {
        // Get current month and year or use requested values
        $currentMonth = $request->input('month', date('m'));
        $currentYear = $request->input('year', date('Y'));

        // Set first day of week to Sunday (0)
        Carbon::setWeekStartsAt(Carbon::SUNDAY);
        Carbon::setWeekEndsAt(Carbon::SATURDAY);

        // Get all weekly schedules
        $weeklySchedules = WeeklySchedule::with(['room', 'teacher'])
            ->orderByRaw("FIELD(day, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday')")
            ->orderBy('start_time')
            ->get()
            ->groupBy('day');

        // Get all reservations for the selected month
        $reservations = Reservation::with(['room'])
            ->active()
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->groupBy(function ($item) {
                return \Carbon\Carbon::parse($item->date)->format('Y-m-d');
            });

        // Prepare weeks of the month
        $weeks = [];
        $startOfMonth = Carbon::create($currentYear, $currentMonth, 1)->startOfMonth();
        $endOfMonth = Carbon::create($currentYear, $currentMonth, 1)->endOfMonth();

        // Start from Sunday of the week containing the 1st of the month
        $currentWeek = $startOfMonth->copy()->startOfWeek(Carbon::SUNDAY);

        while ($currentWeek <= $endOfMonth) {
            $week = [];
            $week['start'] = $currentWeek->copy();
            $week['end'] = $currentWeek->copy()->endOfWeek(Carbon::SATURDAY);

            // Prepare days for this week (Sunday to Saturday)
            $week['days'] = [];
            for ($i = 0; $i < 7; $i++) {
                $dayDate = $currentWeek->copy()->addDays($i);
                $dayName = strtolower($dayDate->format('l'));

                $week['days'][] = [
                    'date' => $dayDate->format('Y-m-d'),
                    'day_name' => $dayName,
                    'is_today' => $dayDate->isToday(),
                    'in_month' => $dayDate->month == $currentMonth,
                    'day_number' => $dayDate->day,
                ];
            }

            $weeks[] = $week;
            $currentWeek->addWeek();
        }

        return view('admin.schedule.calendar', compact(
            'weeklySchedules',
            'reservations',
            'weeks',
            'currentMonth',
            'currentYear',
            'startOfMonth',
            'endOfMonth'
        ));
    }


    public function dayDetails(Request $request)
    {
        $date = $request->input('date');
        $dayName = strtolower(Carbon::parse($date)->format('l'));

        // Get weekly schedules for this day of week
        $schedules = WeeklySchedule::with(['room', 'teacher'])
            ->where('day', $dayName)
            ->orderBy('start_time')
            ->get();

        // Get reservations for this specific date
        $reservations = Reservation::with(['room'])
            ->active()
            ->where('date', $date)
            ->orderBy('start_time')
            ->get();

        return view('admin.schedule.day-details', compact('date', 'schedules', 'reservations'));
    }
}
