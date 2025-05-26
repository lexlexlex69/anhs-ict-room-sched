<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'schedule_name',
        'start_date',
        'end_date',
    ];

    // Cast dates to Carbon instances
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    // A main schedule can have many weekly schedules
    public function weeklySchedules()
    {
        return $this->hasMany(WeeklySchedule::class, 'main_schedule_id');
    }
}
