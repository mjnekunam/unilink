<?php

namespace App\Http\Controllers;

use DateTime;
use RRule\RSet;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Schedule;
use App\Models\Appointment;
use App\Models\ScheduleDate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests\ScheduleStoreRequest;

class ScheduleController extends Controller
{
    public function index(User $user, Request $request)
    {
        $role = Auth::user()->role;
        switch ($role) {
            case 'teacher':
                $props = [
                    'students' => fn() => $user->students(),
                    'events' => fn() => $this->mergeEvents($this->scheduleEvents(), $this->appointmentEvents($role)),
                    'schedules' => fn() => $this->schedules(), // schedules from DB for ScheduleForm when submitting a new form
                ];
                break;
            case 'student':
                $props = [
                    'teachers' => fn() => $user->teachers(),
                    'events' => fn() => $this->mergeEvents($this->scheduleEvents($request->id), $this->appointmentEvents($role)),
                ];
                break;
        }

        return Inertia::render(ucfirst($role) . '/Calendar', $props);
    }

    public function store(ScheduleStoreRequest $request): RedirectResponse
    {
        $input = $request->validated();
        $datesData = [];
        foreach ($input['dates'] as $date) {
            foreach ($date['times'] as $time) {
                $datesData[] = [
                    'date' => $input['frequency'] === 'none' ? $date['date'] : null,
                    'start_time' => $time['start'],
                    'end_time' => $time['end'],
                    'rrule' => $time['rrule'] ?? null,
                ];
            }
        }

        DB::transaction(function () use ($input, $datesData) {
            $schedule = Schedule::create([
                'user_id' => Auth::id(),
                'title' => $input['title'],
                'description' => $input['description'],
                'frequency' => $input['frequency']
            ]);

            $schedule->dates()->createMany($datesData);
        });
        return Redirect::route('calendar');
    }

    public function destroy(Request $request): RedirectResponse
    {
        ScheduleDate::whereIn('id', $request->id)->delete();
        $schedules = Schedule::with('dates')->where('user_id', Auth::id())->get();

        foreach ($schedules as $schedule) {
            if ($schedule->dates->isEmpty()) {
                $schedule->delete();
            }
        }
        return Redirect::route('calendar');
    }

    private function mergeEvents($firstEvent, $secondEvent)
    {
        return $firstEvent->merge($secondEvent);
    }
    private function scheduleEvents($teacherId = null)
    {
        # Student Dashboard
        if ($teacherId) {
            $schedules = Schedule::where('user_id', $teacherId)->with('dates')->get();
            $appointments = Appointment::where('student_id', Auth::id())->with('schedule_date')->get();
            $appointmentDates = $appointments->map(function ($appointment) {
                return $appointment->date . 'T' . $appointment->start_time . 'Z';
            })->toArray();
        }

        # Teacher Dashboard
        else {
            $schedules = Schedule::where('user_id', Auth::id())->with('dates')->get();
            $appointments = Appointment::where('teacher_id', Auth::id())->with('schedule_date')->get();
            $appointmentDates = $appointments->where('status', 'approved')->map(function ($appointment) {
                return $appointment->date . 'T' . $appointment->start_time . 'Z';
            })->toArray();
        }
        $appointmentDateIds = $appointments->pluck('schedule_date.id')->toArray();

        $scheduleEvents = $schedules->isEmpty() ? collect() : $schedules->flatMap(function (Schedule $schedule) use ($appointmentDateIds, $appointmentDates) {
            return $schedule->dates->map(function (ScheduleDate $date) use ($schedule, $appointmentDateIds, $appointmentDates) {
                if ($schedule->frequency === 'none' && !in_array($date->id, $appointmentDateIds)) {
                    $baseEvent = [
                        'id' => $date->id,
                        'title' => $schedule->title,
                        'start' => $date->date . 'T' . $date->start_time,
                        'end' => $date->date . 'T' . $date->end_time,
                        'extendedProps' => [
                            'status' => 'open',
                            'description' => $schedule->description,
                        ],
                    ];
                } else if ($schedule->frequency !== 'none') {
                    $start = new DateTime($date->start_time);
                    $end = new DateTime($date->end_time);
                    $duration = $end->diff($start)->format('%H:%I');
                    $baseEvent = [
                        'id' => $date->id,
                        'title' => $schedule->title,
                        'rrule' => $date->rrule,
                        'exdate' => $appointmentDates,
                        'duration' => $duration,
                        'extendedProps' => [
                            'status' => 'open',
                            'description' => $schedule->description,
                        ],
                    ];
                }

                return $baseEvent ?? collect();
            });
        });

        return $scheduleEvents;
    }

    private function appointmentEvents($role)
    {
        switch ($role) {
            case 'student':
                $appointments = Appointment::where("student_id", Auth::id())
                    ->with(['schedule_date:id,schedule_id'])
                    ->get();
                $otherRole = 'teacher';
                break;
            case 'teacher':
                $appointments = Appointment::where("teacher_id", Auth::id())
                    ->where('status', 'approved')
                    ->with(['schedule_date:id,schedule_id'])
                    ->get();
                $otherRole = 'student';
                break;
        }
        $appointmentEvents = $appointments->isEmpty() ? collect() : $appointments->map(function (Appointment $appointment) use ($otherRole) {
            return [
                'id' => $appointment->id,
                'title' => $appointment->schedule_date->schedule->title . ' ' . "({$appointment->{$otherRole}->name})",
                'start' => $appointment->date . 'T' . $appointment->start_time . 'Z',
                'end' => $appointment->date . 'T' . $appointment->end_time . 'Z',
                'backgroundColor' => $appointment->status === 'approved' ? 'green' : 'gold',
                'borderColor' => $appointment->status === 'approved' ? 'green' : 'gold',
                'extendedProps' => [
                    'status' => $appointment->status,
                    'name' => $appointment->{$otherRole}->name,
                    'avatar' => $appointment->{$otherRole}->avatar,
                    'description' => $appointment->schedule_date->schedule->description
                ],
            ];
        });

        return $appointmentEvents;
    }

    private function schedules()
    {
        $schedules = Schedule::where('user_id', Auth::id())->with('dates')->get();
        $result = $schedules
            ->flatMap(function (Schedule $schedule) {
                return collect($schedule->dates)->map(function ($date) use ($schedule) {
                    $rrule = $date['rrule'] ? (new RSet($date['rrule']))->getRRules()[0]->getRule() : null;
                    return [
                        'id' => $schedule['id'],
                        'title' => $schedule->title,
                        'frequency' => $schedule->frequency,
                        'custom' => $schedule->frequency === 'custom' ? [
                            'startDate' => $rrule['DTSTART']->format('Y-m-d'),
                            'endDate' => $rrule['UNTIL'] ? $rrule['UNTIL']->format('Y-m-d') : null,
                            'interval' => $rrule['INTERVAL']
                        ] : null,
                        'date' => $date['date'] ?? $rrule['BYDAY'],
                        'time' => [
                            'id' => $date['id'],
                            'start' => substr($date['start_time'], 0, 5),
                            'end' => substr($date['end_time'], 0, 5),
                            'rrule' => $date['rrule'] ?? null,
                        ],
                    ];
                });
            })
            ->groupBy('title')
            ->map(function ($group) {
                return [
                    'id' => $group->first()['id'],
                    'title' => $group->first()['title'],
                    'frequency' => $group->first()['frequency'],
                    'custom' => $group->first()['custom'],
                    'dates' => $group->groupBy('date')
                        ->map(function ($dates) {
                            return [
                                'date' => $dates->first()['date'],
                                'times' => $dates->pluck('time')->toArray(),
                            ];
                        })
                        ->values()
                        ->toArray(),
                ];
            })
            ->values();
        return $result;
    }
}
