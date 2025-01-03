<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{

    public function index(User $user)
    {
        $role = Auth::user()->role;

        if ($role === 'teacher') {
            return Inertia::render(
                "Teacher/Dashboard",
                [
                    'students' => fn() => $user->students(),
                ]
            );
        } else if ($role === 'student') {
            return Inertia::render('Student/Dashboard');
        }
    }
}
