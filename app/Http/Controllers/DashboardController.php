<?php

namespace App\Http\Controllers;

use DateTime;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CalendarRequest;
use Illuminate\Support\Facades\Redirect;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render(
            'Dashboard',
            [
                'students' => fn() => User::where('role', 'student')->pluck('name'),
            ]
        );
    }

    public function calendar()
    {
        return Inertia::render('Calendar', [
            'students' => fn() => User::where('role', 'student')->pluck('name'),
            'events' => fn() => $this->getEvents(),
            'schedules' => fn() => $this->getSchedules(),
        ]);
    }

    private function getEvents()
    {
        $schedules = Schedule::where('user_id', Auth::id())->get();
        return $schedules->flatMap(function (Schedule $schedule) {
            return collect($schedule->dates)->flatMap(function ($date) use ($schedule) {
                return collect($date['times'])->map(function ($time) use ($date, $schedule) {
                    $baseEvent = [
                        'title' => $schedule->title,
                        'start' => $date['date'] . 'T' . $time['start'],
                        'end' => $date['date'] . 'T' . $time['end'],
                    ];

                    if ($schedule->frequency !== 'none') {
                        $start = new DateTime($time['start']);
                        $end = new DateTime($time['end']);
                        $duration = $end->diff($start)->format('%H:%I');

                        $baseEvent = [
                            'title' => $schedule->title,
                            'rrule' => $time['rrule'],
                            'duration' => $duration,
                        ];
                    }

                    return $baseEvent;
                });
            });
        });
    }

    private function getSchedules()
    {
        return Schedule::where('user_id', Auth::id())->get()->map(function (Schedule $schedule) {
            $result = [
                'title' => $schedule->title,
                'frequency' => $schedule->frequency,
                'dates' => $schedule->dates,
            ];
            if ($schedule->frequency === 'custom') {
                $result['custom'] = $schedule->custom;
            }
            return $result;
        });
    }

    public function store(CalendarRequest $request)
    {
        $input = $request->validated();
        $schedule = [
            'user_id' => Auth::id(),
            'title' => $input['title'],
            'dates' => $input['dates'],
            'frequency' => $input['frequency'],
        ];
        if ($input['frequency'] === 'custom') {
            $schedule['custom'] = [
                'startDate' => $input['custom']['startDate'],
                'endDate' => $input['custom']['endDate'],
                'interval' => $input['custom']['interval'],
            ];
        }
        Schedule::create($schedule);
        return Redirect::route('calendar');
    }
}
