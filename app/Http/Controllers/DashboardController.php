<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Room;
use App\Models\Schedule;
use Carbon\Carbon;

class DashboardController extends Controller
{
    // Apply time filters based on request parameters
    protected function applyTimeFilter($query, Request $request)
    {
        $filter = $request->get('filter', 'all');
        
        if ($filter === 'week') {
            $startOfWeek = now()->startOfWeek();
            $endOfWeek = now()->endOfWeek();
            $query->whereBetween('date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')]);
        } elseif ($filter === 'month') {
            $startOfMonth = now()->startOfMonth();
            $endOfMonth = now()->endOfMonth();
            $query->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')]);
        }
        
        return $query;
    }

    // Helper method to calculate status based on date and time
    protected function calculateStatus($date, $startTime, $endTime, $currentStatus)
    {
        // If already declined, keep it that way
        if ($currentStatus === 'declined') {
            return 'declined';
        }
        
        $now = Carbon::now();
        $scheduleStart = Carbon::parse($date . ' ' . $startTime);
        $scheduleEnd = Carbon::parse($date . ' ' . $endTime);
        
        if ($now < $scheduleStart) {
            return 'upcoming';
        } elseif ($now >= $scheduleStart && $now <= $scheduleEnd) {
            return 'ongoing';
        } else {
            return 'completed';
        }
    }
    
    // Helper method to apply the calculated status to all schedules
    protected function applyCalculatedStatus($schedules)
    {
        foreach ($schedules as $schedule) {
            $schedule->status = $this->calculateStatus(
                $schedule->date, 
                $schedule->start_time, 
                $schedule->end_time, 
                $schedule->status
            );
        }
        return $schedules;
    }

    // All schedules list (main dashboard)
    public function dashboard(Request $request)
    {
        $data['header_title'] = 'Dashboard';
    
        if (Auth::user()->user_type == 1) {
            // Build query with filters
            $query = Schedule::with(['teacher', 'room']);
            $this->applyTimeFilter($query, $request);
            
            // Apply status filter if provided
            $status = $request->input('status', 'all');
            if ($status !== 'all') {
                $query->where('status', $status);
            }
            
            // Paginate results
            $perPage = $request->get('per_page', 10);
            $schedules = $query->paginate($perPage);
            
            // Update and save status for each schedule
            foreach ($schedules as $schedule) {
                if ($schedule->status !== 'declined') {
                    $newStatus = $this->calculateStatus(
                        $schedule->date, 
                        $schedule->start_time, 
                        $schedule->end_time, 
                        $schedule->status
                    );
                    
                    // Update the status if it has changed
                    if ($schedule->status !== $newStatus) {
                        Schedule::where('id', $schedule->id)->update(['status' => $newStatus]);
                        $schedule->status = $newStatus;
                    }
                }
            }
            
            return view('admin.schedules.AllList', compact('schedules'));
        
        } elseif (Auth::user()->user_type == 2) {
            $data['schedules'] = $this->getFilteredSchedules($request, true);
            return view('teacher.dashboard', $data);
        
        } elseif (Auth::user()->user_type == 3) {
            return view('inspector.dashboard', $data);
        }
    }

    private function getFilteredSchedules(Request $request, $forTeacher = false)
    {
        $query = Schedule::with(['room', 'teacher']);
        
        // Apply teacher filter if needed
        if ($forTeacher) {
            $query->where('teacher_id', Auth::id());
        }
        
        // Apply time filter
        $filter = $request->input('filter', 'all');
        
        switch ($filter) {
            case 'week':
                $startOfWeek = Carbon::now()->startOfWeek();
                $endOfWeek = Carbon::now()->endOfWeek();
                $query->whereBetween('date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')]);
                break;
                
            case 'month':
                $startOfMonth = Carbon::now()->startOfMonth();
                $endOfMonth = Carbon::now()->endOfMonth();
                $query->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')]);
                break;
                
            case 'all':
            default:
                // No additional filter needed
                break;
        }
        
        // Apply status filter
        $status = $request->input('status', 'all');
        
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        
        // Get the per_page parameter from the request, default to 10 if not set
        $perPage = $request->input('per_page', 10);
        
        // Make sure per_page is one of the allowed values
        if (!in_array($perPage, [5, 10, 15])) {
            $perPage = 10; // Default if invalid value provided
        }
        
        // Get the schedules
        $schedules = $query->orderBy('date', 'desc')
                    ->orderBy('start_time', 'desc')
                    ->paginate($perPage);
        
        // Update the status of each schedule in the database
        foreach ($schedules as $schedule) {
            // Only update if it's not already declined
            if ($schedule->status !== 'declined') {
                $newStatus = $this->calculateStatus(
                    $schedule->date, 
                    $schedule->start_time, 
                    $schedule->end_time, 
                    $schedule->status
                );
                
                // Update the status if it has changed
                if ($schedule->status !== $newStatus) {
                    Schedule::where('id', $schedule->id)->update(['status' => $newStatus]);
                    $schedule->status = $newStatus; // Update the model instance too
                }
            }
        }
        
        return $schedules;
    }

    // Upcoming schedules - optimized query
    public function upcoming(Request $request)
    {
        $data['header_title'] = 'Dashboard';
        
        if (Auth::user()->user_type != 1) {
            return redirect()->back();
        }
        
        $now = Carbon::now();
        $nowFormatted = $now->format('Y-m-d H:i:s');
        
        // Query for upcoming schedules directly in database
        $query = Schedule::with(['teacher', 'room'])
            ->where('status', '!=', 'declined')
            ->whereRaw("CONCAT(date, ' ', start_time) > ?", [$nowFormatted]);
            
        // Apply time filters
        $this->applyTimeFilter($query, $request);
        
        // Get paginated results
        $perPage = $request->get('per_page', 10);
        $schedules = $query->paginate($perPage);
        
        // Apply calculated status just to be sure display is consistent
        $this->applyCalculatedStatus($schedules);
        
        return view('admin.schedules.upcoming', compact('schedules'));
    }

    // Ongoing schedules - optimized query
    public function ongoing(Request $request)
    {
        $data['header_title'] = 'Dashboard';
        
        if (Auth::user()->user_type != 1) {
            return redirect()->back();
        }
        
        $now = Carbon::now();
        $nowFormatted = $now->format('Y-m-d H:i:s');
        
        // Query for ongoing schedules directly in database
        $query = Schedule::with(['teacher', 'room'])
            ->where('status', '!=', 'declined')
            ->whereRaw("CONCAT(date, ' ', start_time) <= ?", [$nowFormatted])
            ->whereRaw("CONCAT(date, ' ', end_time) >= ?", [$nowFormatted]);
            
        // Apply time filters
        $this->applyTimeFilter($query, $request);
        
        // Get paginated results
        $perPage = $request->get('per_page', 10);
        $schedules = $query->paginate($perPage);
        
        // Apply calculated status just to be sure display is consistent
        $this->applyCalculatedStatus($schedules);
        
        return view('admin.schedules.ongoing', compact('schedules'));
    }

    // Completed schedules - optimized query
    public function completed(Request $request)
    {
        $data['header_title'] = 'Dashboard';
        
        if (Auth::user()->user_type != 1) {
            return redirect()->back();
        }
        
        $now = Carbon::now();
        $nowFormatted = $now->format('Y-m-d H:i:s');
        
        // Query for completed schedules directly in database
        $query = Schedule::with(['teacher', 'room'])
            ->where('status', '!=', 'declined')
            ->whereRaw("CONCAT(date, ' ', end_time) < ?", [$nowFormatted]);
            
        // Apply time filters
        $this->applyTimeFilter($query, $request);
        
        // Get paginated results
        $perPage = $request->get('per_page', 10);
        $schedules = $query->paginate($perPage);
        
        // Apply calculated status just to be sure display is consistent
        $this->applyCalculatedStatus($schedules);
        
        return view('admin.schedules.completed', compact('schedules'));
    }

    // Declined schedules (these keep their status from the database)
    public function declined(Request $request)
    {
        $data['header_title'] = 'Dashboard';
    
        (Auth::user()->user_type == 1);
        
        // Get only declined schedules
        $query = Schedule::with(['teacher', 'room'])
            ->where('status', 'declined');
            
        // Apply time filters
        $this->applyTimeFilter($query, $request);
        
        // Get paginated results
        $perPage = $request->get('per_page', 10);
        $schedules = $query->paginate($perPage);
        
        return view('admin.schedules.declined', compact('schedules'));
    }
}
