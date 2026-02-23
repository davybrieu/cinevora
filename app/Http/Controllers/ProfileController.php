<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public const AVATARS = [
        'avatar-1.svg',
        'avatar-2.svg',
        'avatar-3.svg',
        'avatar-4.svg',
        'avatar-5.svg',
        'avatar-6.svg',
    ];

    public function index(Request $request): Response
    {
        return Inertia::render('Profiles/Select', [
            'profiles' => $request->user()->profiles->map(fn(Profile $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'avatar' => $p->avatar,
                'avatar_url' => $p->avatar_url,
            ]),
            'avatars' => collect(self::AVATARS)->map(fn(string $a) => [
                'name' => $a,
                'url' => asset("images/avatars/{$a}"),
            ]),
        ]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        abort_if($user->profiles()->count() >= 3, 403, 'Maximum 3 profiles.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'avatar' => ['required', 'string', 'in:' . implode(',', self::AVATARS)],
        ]);

        $user->profiles()->create($validated);

        return back();
    }

    public function select(Request $request, Profile $profile): \Illuminate\Http\RedirectResponse
    {
        abort_unless($profile->user_id === $request->user()->id, 403);

        $request->session()->put('profile_id', $profile->id);

        return redirect()->route('home');
    }

    public function deselect(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->session()->forget('profile_id');

        return redirect()->route('profiles.index');
    }

    public function destroy(Request $request, Profile $profile): \Illuminate\Http\RedirectResponse
    {
        abort_unless($profile->user_id === $request->user()->id, 403);

        if ($request->session()->get('profile_id') === $profile->id) {
            $request->session()->forget('profile_id');
        }

        $profile->delete();

        return back();
    }
}
