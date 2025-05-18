<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use App\Models\Room;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = ['teacher_id', 'date', 'start_time', 'end_time', 'room_id', 'status', 'remarks'];



    // Relationship with User (Teacher)
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    // Relationship with Room
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    
    static public function getSingle($id) 
    {
        return self::find($id);
    }


    
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }


    public function getProfilePictureUrl()
{
    if(!empty($this->profile_pic) && file_exists('upload/profile/'.$this->profile_pic))
    {
        return url('upload/profile/'.$this->profile_pic);
    }
    else
    {
        return "";
    
    }

}




public static function updateCompletedSchedules()
{
    $now = Carbon::now('Asia/Manila');
    $currentDate = $now->toDateString(); // Extract only the date (YYYY-MM-DD)
    $currentTime = $now->toTimeString(); // Extract only the time (HH:MM:SS)

    // Update schedules that should be marked as "completed"
    $completedSchedules = static::where('status', 'upcoming')
        ->where(function ($query) use ($currentDate, $currentTime) {
            $query->whereDate('date', '<', $currentDate) // Past dates
                  ->orWhere(function ($q) use ($currentDate, $currentTime) {
                      $q->whereDate('date', '=', $currentDate) // Today's date
                        ->whereRaw("TIME(end_time) < ?", [$currentTime]); // If end time is in the past
                  });
        })
        ->get();

    foreach ($completedSchedules as $schedule) {
        $schedule->update(['status' => 'completed']);

        // Notify users about room availability
        static::notifyRoomAvailability($schedule->room_id, $schedule->date, $schedule->start_time, $schedule->end_time);
    }

    // Update schedules that should be marked as "ongoing"
    static::where('status', 'upcoming')
        ->whereDate('date', '=', $currentDate) // Check only today's schedules
        ->whereRaw("TIME(start_time) = ?", [$currentTime]) // If start time is exactly now
        ->update(['status' => 'ongoing']);
}





// Fixed function: Query User model instead of Auth::user()

public static function notifyRoomAvailability($roomId, $date, $start_time, $end_time)
{
    // Fetch the room details
    $room = Room::find($roomId);
    $roomName = $room ? $room->room_name : 'Unknown Room'; // Use a fallback if room is not found

    // Convert start and end times to 12-hour format
    $formattedStartTime = date('h:i A', strtotime($start_time));
    $formattedEndTime = date('h:i A', strtotime($end_time));

    // Find all teachers who might need this room
    $interestedUsers = User::where('user_type', '2')->get();

    foreach ($interestedUsers as $user) {
        Notification::create([
            'user_id' => $user->id,
            'message' => "The room **{$roomName}** is now available on {$date} from {$formattedStartTime} to {$formattedEndTime}.",
        ]);
    }
}




}
