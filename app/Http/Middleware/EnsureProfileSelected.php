<?php

namespace App\Http\Middleware;

use App\Models\Profile;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileSelected
{
    public function handle(Request $request, Closure $next): Response
    {
        $profileId = $request->session()->get('profile_id');

        if (! $profileId) {
            return redirect()->route('profiles.index');
        }

        $profile = Profile::where('id', $profileId)
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $profile) {
            $request->session()->forget('profile_id');

            return redirect()->route('profiles.index');
        }

        return $next($request);
    }
}
