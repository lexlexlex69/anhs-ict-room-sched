<?php

// app/Models/Reservation.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'room_id',
        'date',
        'start_time',
        'end_time',
        'teacher_name',
        'subject',
        'status'
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public static function generateReferenceNumber()
    {
        return 'RES-' . strtoupper(Str::random(8));
    }
}
