<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $table = "rooms";



    static public function getRoom()
    {
        $return = Room::select('rooms.*', 'users.first_name as created_by_name')
            ->join('users', 'users.id', 'rooms.created_by')
            ->where('rooms.is_delete', '=', 0)
            ->orderBy('rooms.id', 'desc')
            ->paginate(5);

        return $return;
    }

    public function weeklySchedules()
    {
        return $this->hasMany(WeeklySchedule::class);
    }

    static public function getSingle($id)
    {
        return self::find($id);
    }
}
