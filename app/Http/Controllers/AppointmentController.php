<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Appointment;
use App\Models\ScheduleDate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Vite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests\AppointmentStoreRequest;
use App\Http\Requests\AppointmentUpdateRequest;

class AppointmentController extends Controller
{
    public function index()
    {
        $role = Auth::user()->role;
        $img = Vite::asset('public/storage/img/lost.svg');
        return Inertia::render(
            ucfirst($role) . "/Appointment",
            [
                'appointments' => fn() => $this->appointments($role),
                'img' => $img,
            ]
        );
    }

    public function update(AppointmentUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        DB::transaction(function () use ($validated) {
            $appointments = Appointment::whereIn('id', $validated['id'])->get();
            Appointment::whereIn('id', $validated['id'])->update(['status' => 'approved']);
            foreach ($appointments as $appointment) {
                Appointment::where('date', $appointment->date)
                    ->where('start_time', $appointment->start_time)
                    ->where('end_time', $appointment->end_time)
                    ->where('status', 'pending')
                    ->delete();
            }
        });

        return Redirect::route('appointment');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Appointment::whereIn('id', $request->id)->delete();
        //TODO: send notification to student when teacher deletes an appointment
        return Redirect::route('appointment');
    }

    public function store(Request $request)
    {
        $date = ScheduleDate::findOrFail($request->dateId);
        $teacherId = $date->schedule->user_id;
        Appointment::create([
            'student_id' => Auth::id(),
            'teacher_id' => $teacherId,
            'date_id' => $request->dateId,
            'date' => $request->date,
            'start_time' => $request->startTime,
            'end_time' => $request->endTime,
        ]);
    }

    private function appointments($userRole)
    {
        if ($userRole === 'student') {
            $appointments = Appointment::where("{$userRole}_id", Auth::id())
                ->with(["{$userRole}:id,name,avatar", 'schedule_date:id,rrule'])
                ->get();
        } else if ($userRole === 'teacher') {
            $appointments = Appointment::where("{$userRole}_id", Auth::id())->where('status', 'pending')
                ->with(["{$userRole}:id,name,avatar", 'schedule_date:id,rrule'])
                ->get();
        }
        $otherRole = $userRole === 'teacher' ? 'student' : 'teacher';
        $result = $appointments->map(function ($appointment) use ($otherRole) {
            return [
                'id' => $appointment->id,
                'date' => $appointment->date,
                'startTime' => substr($appointment->start_time, 0, 5),
                'endTime' => substr($appointment->end_time, 0, 5),
                'status' => $appointment->status,
                $otherRole => [
                    'name' => $appointment->{$otherRole}->name,
                    'avatar' => $appointment->{$otherRole}->avatar,
                ]
            ];
        });
        return $result;
    }
}
