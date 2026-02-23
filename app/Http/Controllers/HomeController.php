<?php

namespace App\Http\Controllers;

use App\Http\Resources\TmdbResource;
use App\Models\WatchProgress;
use App\Services\TmdbService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __construct(
        protected TmdbService $tmdb,
        protected TmdbResource $resource,
    ) {}

    public function index(Request $request): Response
    {
        $providerId = $request->query('provider') ? (int) $request->query('provider') : null;

        $hero = $this->getHeroItems($providerId);

        $categories = $this->getCategories($providerId);

        $profileId = $request->session()->get('profile_id');
        $keepWatching = $this->getKeepWatchingItems($profileId);

        return Inertia::render('Home', [
            'hero' => $hero,
            'categories' => $categories,
            'keepWatching' => $keepWatching,
            'currentProvider' => $providerId,
            'providers' => $this->tmdb->getPreferedProviders(),
        ]);
    }

    public function switchLocale(Request $request, string $locale): \Illuminate\Http\RedirectResponse
    {
        if (in_array($locale, ['fr', 'en'])) {
            $request->session()->put('locale', $locale);
        }

        return redirect()->back();
    }

    protected function getHeroItems(?int $providerId): array
    {
        if ($providerId) {
            $data = $this->tmdb->getPopularByProvider($providerId, 'movie');
            $items = collect($data['results'] ?? [])
                ->take(5)
                ->map(fn($item) => $this->resource->formatItem($item, 'movie'))
                ->values()
                ->all();
        } else {
            $data = $this->tmdb->getTrending();
            $items = collect($data['results'] ?? [])
                ->filter(fn($item) => !empty($item['backdrop_path']))
                ->take(5)
                ->map(fn($item) => $this->resource->formatItem($item))
                ->values()
                ->all();
        }

        return $this->resource->withDetails($items);
    }

    /**
     * Fixed: Check for array keys existence to avoid undefined 'type' errors
     */
    protected function getCategories(?int $providerId): array
    {
        $categories = $this->tmdb->getCategories();
        return collect($categories)
            ->map(function ($category) use ($providerId) {
                // fallback to check for array keys
                $type = $category['type'] ?? null;
                $id = $category['id'] ?? $category['genre'] ?? null; // fallback: try 'genre' as well
                if (!$type || !$id) {
                    return null;
                }
                $data = $this->tmdb->getByGenre($type, $id, $providerId);
                $rawItems = $data['results'] ?? [];
                $items = collect($rawItems)->map(fn($item) => array_merge($item, ['media_type' => $type]))->all();
                return [
                    'title' => $category['name'] ?? '',
                    'slug' => $category['slug'] ?? '',
                    'items' => $items,
                ];
            })
            ->filter(fn($cat) => is_array($cat) && count($cat['items']) > 0)
            ->values()
            ->all();
    }

    /**
     * Get "Keep Watching" items: latest from watch_progress per title, ordered by updated_at.
     */
    protected function getKeepWatchingItems(?int $profileId): array
    {
        if (! $profileId) {
            return [];
        }

        $rows = WatchProgress::where('profile_id', $profileId)
            ->orderByDesc('updated_at')
            ->get()
            ->unique(fn(WatchProgress $r) => $r->item_type . '-' . $r->item_id)
            ->values();

        $items = [];
        foreach ($rows->take(20) as $row) {
            $data = $this->tmdb->getItemSummary($row->item_id, $row->item_type);
            if (empty($data['id'])) {
                continue;
            }
            $item = $this->resource->formatItem($data, $row->item_type);
            $item['watch_progress_percentage'] = round($row->progress_percentage, 1);
            $item['resume_season'] = $row->season;
            $item['resume_episode'] = $row->episode;
            $items[] = $item;
        }

        return $items;
    }
}
