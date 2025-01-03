<?php

namespace App\Models;

use App\Models\Schedule;
use App\Models\Appointment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleDate extends Model
{
    protected $fillable = [
        'schedule_id',
        'date',
        'until',
        'start_time',
        'end_time',
        'rrule',
        'status',
    ];

    // protected function casts(): array
    // {
    //     return [
    //         'start_date' => 'date',
    //         'start_time' => 'time',
    //         'end_time' => 'time',
    //     ];
    // }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
