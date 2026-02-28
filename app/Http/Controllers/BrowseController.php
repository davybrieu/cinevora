<?php

namespace App\Http\Controllers;

use App\Http\Resources\TmdbResource;
use App\Models\ItemReaction;
use App\Models\WatchProgress;
use App\Services\TmdbService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\JsonResponse;

class BrowseController extends Controller
{
    public const SORT_OPTIONS = [
        'popularity_desc' => 'browse_sort_popularity',
        'release_date_desc' => 'browse_sort_date_desc',
        'release_date_asc' => 'browse_sort_date_asc',
        'vote_average_desc' => 'browse_sort_rating',
        'vote_count_desc' => 'browse_sort_vote_count',
    ];

    public function __construct(
        protected TmdbService $tmdb,
        protected TmdbResource $resource,
    ) {}

    public function index(Request $request): Response
    {
        $filters = $this->filtersFromRequest($request);
        $page = 1;

        $result = $this->fetchResults($filters, $page);

        $profileId = $request->session()->get('profile_id');
        $result['items'] = WatchProgress::mergeProgressIntoItems($result['items'], $profileId);
        $result['items'] = ItemReaction::mergeCountsIntoItems($result['items']);

        return Inertia::render('Browse', [
            'items' => $result['items'],
            'currentPage' => $result['currentPage'],
            'totalPages' => $result['totalPages'],
            'totalResults' => $result['totalResults'] ?? 0,
            'filters' => $filters,
            'providers' => $this->tmdb->getProvidersForType($filters['type'] ?? 'all'),
            'genres' => $this->tmdb->getGenresForBrowse(),
            'languages' => $this->tmdb->getLanguages(),
            'sortOptions' => self::SORT_OPTIONS,
        ]);
    }

    public function loadMore(Request $request): JsonResponse
    {
        $filters = $this->filtersFromRequest($request);
        $page = max(1, (int) $request->query('page', 1));

        $result = $this->fetchResults($filters, $page);

        $profileId = $request->session()->get('profile_id');
        $result['items'] = WatchProgress::mergeProgressIntoItems($result['items'], $profileId);
        $result['items'] = ItemReaction::mergeCountsIntoItems($result['items']);

        return response()->json([
            'items' => $result['items'],
            'currentPage' => $result['currentPage'],
            'totalPages' => $result['totalPages'],
            'totalResults' => $result['totalResults'] ?? 0,
        ]);
    }

    protected function filtersFromRequest(Request $request): array
    {
        $genres = $request->query('genres');
        if (is_string($genres)) {
            $genres = $genres !== '' ? array_map('intval', explode(',', $genres)) : null;
        }

        return [
            'query' => $request->query('query'),
            'type' => $request->query('type', 'all'),
            'provider' => $request->query('provider') ? (int) $request->query('provider') : null,
            'genres' => $genres,
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'language' => $request->query('language'),
            'vote_min' => $request->query('vote_min'),
            'vote_count_min' => $request->query('vote_count_min'),
            'sort' => $request->query('sort', 'popularity_desc'),
        ];
    }

    protected function fetchResults(array $filters, int $page): array
    {
        $query = trim((string) ($filters['query'] ?? ''));

        if ($query !== '') {
            return $this->fetchSearchResults($filters, $page);
        }

        return $this->fetchDiscoverResults($filters, $page);
    }

    protected function fetchSearchResults(array $filters, int $page): array
    {
        $type = $filters['type'] === 'all' ? 'multi' : $filters['type'];
        $data = $this->tmdb->searchBrowse($filters['query'], $type, $page);

        $results = collect($data['results'] ?? []);
        $selectedGenres = collect($filters['genres'] ?? [])
            ->map(fn($id) => (int) $id)
            ->filter()
            ->values();

        if ($selectedGenres->isNotEmpty()) {
            $results = $results->filter(function ($item) use ($selectedGenres) {
                $itemGenres = collect($item['genre_ids'] ?? [])->map(fn($id) => (int) $id);
                return $itemGenres->intersect($selectedGenres)->isNotEmpty();
            })->values();
        }

        if ($type === 'multi') {
            $items = $results
                ->filter(fn($item) => in_array($item['media_type'] ?? null, ['movie', 'tv']))
                ->take(20)
                ->map(fn($item) => $this->resource->formatItem($item, $item['media_type']))
                ->values()
                ->all();
        } else {
            $items = $results
                ->take(20)
                ->map(fn($item) => $this->resource->formatItem($item, $type))
                ->values()
                ->all();
        }

        return [
            'items' => $items,
            'currentPage' => $data['page'],
            'totalPages' => $data['total_pages'],
            'totalResults' => $data['total_results'] ?? 0,
        ];
    }

    protected function fetchDiscoverResults(array $filters, int $page): array
    {
        $type = $filters['type'] ?? 'all';

        if ($type === 'all') {
            return $this->fetchDiscoverMerged($filters, $page);
        }

        $data = $this->tmdb->discoverBrowse(array_merge($filters, ['type' => $type, 'page' => $page]));

        $items = collect($data['results'])
            ->map(fn($item) => $this->resource->formatItem($item, $type))
            ->values()
            ->all();

        return [
            'items' => $items,
            'currentPage' => $data['page'],
            'totalPages' => $data['total_pages'],
            'totalResults' => $data['total_results'] ?? 0,
        ];
    }


    protected function fetchDiscoverMerged(array $filters, int $page): array
    {
        $movieData = $this->tmdb->discoverBrowse(array_merge($filters, ['type' => 'movie', 'page' => $page]));
        $tvData = $this->tmdb->discoverBrowse(array_merge($filters, ['type' => 'tv', 'page' => $page]));

        $movies = collect($movieData['results'])->map(fn($item) => array_merge($this->resource->formatItem($item, 'movie'), ['_sort_date' => $item['release_date'] ?? '']));
        $tv = collect($tvData['results'])->map(fn($item) => array_merge($this->resource->formatItem($item, 'tv'), ['_sort_date' => $item['first_air_date'] ?? '']));

        $merged = $movies->concat($tv)
            ->sortByDesc('_sort_date')
            ->take(20)
            ->map(function ($item) {
                unset($item['_sort_date']);
                return $item;
            })
            ->values()
            ->all();

        $totalPages = max($movieData['total_pages'], $tvData['total_pages']);

        $totalResults = ($movieData['total_results'] ?? 0) + ($tvData['total_results'] ?? 0);

        return [
            'items' => $merged,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalResults' => min($totalResults, 10000),
        ];
    }
}
