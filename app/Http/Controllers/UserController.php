<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function home()
    {
        return Inertia::render('Home', [
            'img' => asset('storage/img/landing.webp'),
        ]);
    }
}
