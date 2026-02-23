<?php

namespace App\Http\Middleware;

use App\Models\Profile;
use App\Models\Watchlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
                'profile' => $this->getActiveProfile($request),
            ],
            'locale' => App::getLocale(),
            'availableLocales' => [
                ['code' => 'fr', 'name' => 'Français', 'flag' => 'https://flagcdn.com/fr.svg'],
                ['code' => 'en', 'name' => 'English', 'flag' => 'https://flagcdn.com/gb.svg'],
            ],
            'tmdb' => [
                'image_base_url' => config('services.tmdb.image_base_url'),
            ],
            'translations' => $this->getTranslations(),
            'flash' => [
                'success' => fn() => $request->session()->get('success'),
                'error' => fn() => $request->session()->get('error'),
                'info' => fn() => $request->session()->get('info'),
                'warning' => fn() => $request->session()->get('warning'),
            ],
        ];
    }

    protected function getActiveProfile(Request $request): ?array
    {
        $user = $request->user();
        $profileId = $request->session()->get('profile_id');

        if (! $user || ! $profileId) {
            return null;
        }

        $profile = Profile::where('id', $profileId)
            ->where('user_id', $user->id)
            ->first();

        if (! $profile) {
            return null;
        }

        return [
            'id' => $profile->id,
            'name' => $profile->name,
            'avatar' => $profile->avatar,
            'avatar_url' => $profile->avatar_url,
            'watchlist_ids' => $profile->watchlists->map(fn($w) => ['type' => $w->item_type, 'id' => $w->item_id])->toArray(),
        ];
    }

    protected function getTranslations(): array
    {
        $locale = App::getLocale();
        $path = lang_path("{$locale}.json");

        if (File::exists($path)) {
            return json_decode(File::get($path), true) ?? [];
        }

        return [];
    }
}
