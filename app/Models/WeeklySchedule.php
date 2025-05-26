<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklySchedule extends Model
{
    use HasFactory;

    protected $fillable = ['teacher_id', 'room_id', 'day', 'start_time', 'end_time', 'main_schedule_id'];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function mainSchedule()
    {
        return $this->belongsTo(MainSchedule::class, 'main_schedule_id');
    }
}
