<?php

namespace App\Http\Controllers;

use App\Http\Resources\TmdbResource;
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

        return Inertia::render('Home', [
            'hero' => $hero,
            'categories' => $categories,
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
                return [
                    'title' => $category['name'] ?? '',
                    'slug' => $category['slug'] ?? '',
                    'items' => $data['results'] ?? [],
                ];
            })
            ->filter(fn($cat) => is_array($cat) && count($cat['items']) > 0)
            ->values()
            ->all();
    }
}
