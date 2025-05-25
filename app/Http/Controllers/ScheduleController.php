<?php

namespace App\Http\Controllers;

use App\Models\MainSchedule;
use App\Models\Room;
use Illuminate\Http\Request;
use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use App\Models\Reservation;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\WeeklySchedule;
use Illuminate\Support\Facades\Log;

class ScheduleController extends Controller
{


    //admin




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

    // NEW METHOD: Display a list of main schedules for the teacher
    public function mainScheduleList()
    {
        $user = Auth::user();
        $currentTime = now()->format('h:i A');

        $mainSchedules = MainSchedule::where('teacher_id', $user->id)
            ->orderBy('start_date')
            ->get();

        return view('teacher.schedule.main', compact('mainSchedules', 'currentTime', 'user'));
    }

    // NEW METHOD: Store a new main schedule
    public function storeMainSchedule(Request $request)
    {
        // Log::info([$request]);
        // $request->validate([
        //     'schedule_name' => 'required|string|max:255',
        //     'start_month' => 'required|date_format:Y-m-d', // Expects YYYY-MM-01 format from input[type="month"]
        //     'end_month' => 'required|date_format:Y-m-d|after_or_equal:start_month', // Expects YYYY-MM-01, must be >= start_month
        // ]);

        MainSchedule::create([
            'teacher_id' => Auth::id(),
            'schedule_name' => $request->schedule_name,
            'start_date' => $request->start_month,
            'end_date' => Carbon::parse($request->end_month)->endOfMonth()->toDateString(), // Ensure end_date is end of month
        ]);
        // Log::info([$result]);
        // Optional: Notify admin about new main schedule
        $admin = User::where('user_type', '1')->first();
        if ($admin) {
            Notification::create([
                'user_id' => $admin->id,
                'message' => "A new main schedule titled '{$request->schedule_name}' has been created by " . Auth::user()->first_name .
                    " for the period from " . Carbon::parse($request->start_month)->format('F Y') .
                    " to " . Carbon::parse($request->end_month)->format('F Y') . ".",
            ]);
        }


        return redirect()->back()->with('success', 'Main schedule created successfully!');
    }

    // NEW METHOD: Delete a main schedule and its associated weekly schedules
    public function deleteMainSchedule(MainSchedule $mainSchedule)
    {
        // Ensure the current teacher owns this main schedule
        if ($mainSchedule->teacher_id != Auth::id()) {
            return redirect()->back()->with('error', 'You can only delete your own main schedules.');
        }

        $scheduleName = $mainSchedule->schedule_name;
        $mainSchedule->delete(); // This will also cascade delete associated weekly schedules due to onDelete('cascade') in migration

        // Optional: Notify admin about deleted main schedule
        $admin = User::where('user_type', '1')->first();
        if ($admin) {
            Notification::create([
                'user_id' => $admin->id,
                'message' => "A main schedule titled '{$scheduleName}' has been deleted by " . Auth::user()->first_name . ".",
            ]);
        }

        return redirect()->back()->with('success', 'Main schedule deleted successfully!');
    }

    // NEW METHOD: Display weekly schedules for a specific main schedule
    public function weeklyScheduleForMainSchedule(MainSchedule $mainSchedule)
    {
        // Ensure the current teacher owns this main schedule
        if ($mainSchedule->teacher_id != Auth::id()) {
            return redirect()->back()->with('error', 'You do not have permission to view this schedule.');
        }

        $user = Auth::user();
        // Get rooms related to the teacher's subject for the dropdown in the modal
        $getRoom = Room::where('subject', $user->subject)->get();

        // Fetch weekly schedules specifically for this main schedule
        $weeklySchedules = WeeklySchedule::with(['room', 'teacher'])
            ->where('main_schedule_id', $mainSchedule->id)
            ->orderByRaw("FIELD(day, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')")
            ->orderBy('start_time')
            ->get();

        // Define days of the week for displaying the grid
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']; // Include weekend days if you allow them

        $currentTime = now()->format('h:i A');

        return view('teacher.schedule.weekly', compact('mainSchedule', 'getRoom', 'weeklySchedules', 'days', 'currentTime'));
    }

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

    // MODIFIED METHOD: Store a new weekly schedule, now requiring a MainSchedule context
    public function storeWeeklySchedule(Request $request, MainSchedule $mainSchedule)
    {
        $request->validate([
            'day' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday', // Ensure all allowed days are listed
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s|after:start_time',
            'room_id' => 'required|exists:rooms,id',
        ]);

        // Check for conflicts within the context of this specific main schedule, room, and day
        $conflict = WeeklySchedule::where('main_schedule_id', $mainSchedule->id)
            ->where('room_id', $request->room_id)
            ->where('day', $request->day)
            ->where(function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    // New schedule starts during existing schedule
                    $q->where('start_time', '<', $request->end_time)
                        ->where('end_time', '>', $request->start_time);
                });
            })->exists();

        if ($conflict) {
            return back()->with('error', 'There is a schedule conflict for this room, day, and time slot within this main schedule.');
        }

        WeeklySchedule::create([
            'teacher_id' => Auth::id(),
            'main_schedule_id' => $mainSchedule->id, // Associate with the main schedule
            'room_id' => $request->room_id,
            'day' => $request->day,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        $admin = User::where('user_type', '1')->first();
        if ($admin) {
            Notification::create([
                'user_id' => $admin->id,
                'message' => "A new weekly schedule has been created by " . Auth::user()->first_name .
                    " for '{$mainSchedule->schedule_name}' on {$request->day} from {$request->start_time} to {$request->end_time}.",
            ]);
        }

        return redirect()->back()->with('success', 'Weekly schedule created successfully!');
    }

    // MODIFIED METHOD: Delete a weekly schedule, now requiring MainSchedule and WeeklySchedule context
    public function deleteWeeklySchedule(MainSchedule $mainSchedule, WeeklySchedule $weeklySchedule)
    {
        // Ensure the weekly schedule belongs to the current teacher AND the specified main schedule
        if ($weeklySchedule->teacher_id != Auth::id() || $weeklySchedule->main_schedule_id != $mainSchedule->id) {
            return redirect()->back()->with('error', 'You can only delete your own schedules or schedules within the correct main schedule context.');
        }

        $day = $weeklySchedule->day;
        $startTime = $weeklySchedule->start_time;
        $endTime = $weeklySchedule->end_time;
        $mainScheduleName = $mainSchedule->schedule_name;

        $weeklySchedule->delete();

        $admin = User::where('user_type', '1')->first();
        if ($admin) {
            Notification::create([
                'user_id' => $admin->id,
                'message' => "A weekly schedule has been deleted by " . Auth::user()->first_name .
                    " for '{$mainScheduleName}' on {$day} from {$startTime} to {$endTime}.",
            ]);
        }

        return redirect()->back()->with('success', 'Weekly schedule deleted successfully!');
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
        // Get current room filter or use 'All' as default
        $currentRoom = $request->input('room_id', 'All');

        // Set first day of week to Sunday (0)
        Carbon::setWeekStartsAt(Carbon::SUNDAY);
        Carbon::setWeekEndsAt(Carbon::SATURDAY);

        $startOfMonth = Carbon::create($currentYear, $currentMonth, 1)->startOfMonth();
        $endOfMonth = Carbon::create($currentYear, $currentMonth, 1)->endOfMonth();

        // Get all weekly schedules for the teacher within the selected month and year based on MainSchedule
        $weeklySchedulesQuery = WeeklySchedule::with(['room'])
            ->where('weekly_schedules.teacher_id', $user->id) // FIX: Specify the table for teacher_id
            ->join('main_schedules', 'weekly_schedules.main_schedule_id', '=', 'main_schedules.id')
            ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                // Filter main schedules that overlap with the selected month/year
                $query->whereDate('main_schedules.start_date', '<=', $endOfMonth)
                    ->whereDate('main_schedules.end_date', '>=', $startOfMonth);
            })
            ->select('weekly_schedules.*'); // Select weekly_schedules columns to avoid ambiguity

        if ($currentRoom !== 'All') {
            $weeklySchedulesQuery->where('weekly_schedules.room_id', $currentRoom);
        }

        $weeklySchedules = $weeklySchedulesQuery->get()->groupBy('day');

        // Get all reservations for the current teacher within the selected month and year
        // Only fetch if the user is a Non-ICT teacher, as per your ReservationController logic
        $reservations = collect();
        if ($user && $user->user_type == 2 && $user->teacher_type == 'Non-ICT') {
            $reservationQuery = Reservation::with(['room'])
                ->where('teacher_name', $user->first_name . ' ' . $user->last_name) // Assuming teacher_name is stored as full name
                ->whereMonth('date', $currentMonth)
                ->whereYear('date', $currentYear);

            if ($currentRoom !== 'All') {
                $reservationQuery->where('room_id', $currentRoom);
            }

            $reservations = $reservationQuery->get()->groupBy('date'); // <--- CHANGE IS HERE: Group by the full date
        }

        // Prepare weeks of the month
        $weeks = [];


        // Start from Sunday of the week containing the 1st of the month
        $currentWeek = $startOfMonth->copy()->startOfWeek(Carbon::SUNDAY);

        while ($currentWeek <= $endOfMonth) {
            $week = [];
            $week['start'] = $currentWeek->copy();
            $week['end'] = $currentWeek->copy()->endOfWeek(Carbon::SATURDAY);

            // Prepare days for this week (Sunday to Saturday)
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

        // Get all rooms for the filter dropdown
        $rooms = Room::all();


        return view('teacher.schedule.calendar', compact(
            'user',
            'weeklySchedules',
            'reservations',
            'weeks',
            'currentMonth',
            'currentYear',
            'startOfMonth',
            'endOfMonth',
            'currentTime',
            'rooms', // Pass rooms to the view
            'currentRoom' // Pass selected room to the view
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
