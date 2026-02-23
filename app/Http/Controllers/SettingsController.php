<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    private function getUserData(): array
    {
        $user = Auth::user();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at,
        ];
    }

    private function getProfileData(Request $request): ?array
    {
        $profileId = $request->session()->get('profile_id');
        if (!$profileId) {
            return null;
        }

        $profile = $request->user()->profiles()->find($profileId);
        if (!$profile) {
            return null;
        }

        return [
            'id' => $profile->id,
            'name' => $profile->name,
            'avatar' => $profile->avatar,
        ];
    }

    private function getAvatars(): array
    {
        $avatarsPath = public_path('images/avatars');
        if (!is_dir($avatarsPath)) {
            return [];
        }

        return collect(scandir($avatarsPath))
            ->filter(fn($file) => !in_array($file, ['.', '..']) && preg_match('/\.(png|jpg|jpeg|webp|svg)$/i', $file))
            ->values()
            ->toArray();
    }

    public function profile(Request $request): Response
    {
        return Inertia::render('Settings/Profile', [
            'profile' => $this->getProfileData($request),
            'avatars' => $this->getAvatars(),
        ]);
    }

    public function account(): Response
    {
        return Inertia::render('Settings/Account', [
            'user' => $this->getUserData(),
        ]);
    }

    public function password(): Response
    {
        return Inertia::render('Settings/Password');
    }

    public function deleteAccount(): Response
    {
        return Inertia::render('Settings/DeleteAccount');
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $profileId = $request->session()->get('profile_id');
        $profile = $request->user()->profiles()->findOrFail($profileId);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'avatar' => ['required', 'string', 'max:255'],
        ]);

        $profile->update($validated);

        return back()->with('success', __('settings.profile_updated'));
    }

    public function updateAccount(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return back()->with('success', __('settings.account_updated'));
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', __('settings.password_updated'));
    }

    public function destroyAccount(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->profiles()->delete();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing');
    }
}
