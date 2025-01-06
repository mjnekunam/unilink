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
        $props = [];
        if ($role === 'teacher') {
            $props =
                [
                    'students' => fn() => $user->students(),
                ];
        }
        return Inertia::render(ucfirst($role) . '/Dashboard', $props);
    }
}
