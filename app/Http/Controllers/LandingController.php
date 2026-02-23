<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class LandingController extends Controller
{
    public function index(Request $request): \Inertia\Response|\Illuminate\Http\RedirectResponse
    {
        if ($request->user()) {
            if ($request->session()->has('profile_id')) {
                return redirect()->route('home');
            }

            return redirect()->route('profiles.index');
        }

        return Inertia::render('Landing');
    }
}
