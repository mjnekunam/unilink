<?php

namespace App\Models;

use App\Models\User;
use App\Models\ScheduleDate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    protected $fillable = [
        'teacher_id',
        'student_id',
        'date_id',
        'date',
        'start_time',
        'end_time',
        'status',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id')->where('role', 'teacher');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id')->where('role', 'student');
    }

    public function schedule_date(): BelongsTo
    {
        return $this->belongsTo(ScheduleDate::class, 'date_id');
    }
}
