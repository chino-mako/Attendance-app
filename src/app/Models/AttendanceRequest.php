<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'request_date',
        'clock_in',
        'clock_out',
        'note',
        'status',
        'approved_by',
        'approved_at',
    ];

        public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function requestBreakTimes()
    {
        return $this->hasMany(RequestBreakTime::class, 'attendance_id', 'attendance_id');
    }

}
