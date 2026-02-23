<?php

namespace App\Services;

use App\Http\Resources\TmdbResource;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TmdbService
{
    protected string $baseUrl;
    protected string $apiKey;

    protected array $localeMap = [
        'fr' => 'fr-FR',
        'en' => 'en-US',
    ];

    public function __construct()
    {
        $this->baseUrl = config('services.tmdb.base_url');
        $this->apiKey = config('services.tmdb.api_key');
    }

    protected function getTmdbLanguage(): string
    {
        return $this->localeMap[App::getLocale()] ?? 'fr-FR';
    }

    protected function getWatchRegion(): string
    {
        return App::getLocale() === 'en' ? 'US' : 'FR';
    }

    protected function get(string $endpoint, array $params = []): array
    {
        $params['api_key'] = $this->apiKey;
        $params['language'] = $this->getTmdbLanguage();

        $cacheKey = 'tmdb_' . md5($endpoint . json_encode($params));

        return Cache::remember($cacheKey, now()->addHours(3), function () use ($endpoint, $params) {
            $response = Http::timeout(10)->get("{$this->baseUrl}{$endpoint}", $params);

            if ($response->failed()) {
                return ['results' => []];
            }

            return $response->json() ?? ['results' => []];
        });
    }

    public function getGenreMap(): array
    {
        $cacheKey = 'tmdb_genre_map_' . App::getLocale();

        return Cache::remember($cacheKey, now()->addDays(7), function () {
            $movieGenres = $this->get('/genre/movie/list');
            $tvGenres = $this->get('/genre/tv/list');

            $map = [];
            foreach (array_merge($movieGenres['genres'] ?? [], $tvGenres['genres'] ?? []) as $genre) {
                $map[$genre['id']] = $genre['name'];
            }

            return $map;
        });
    }

    public function getTrending(string $type = 'all', string $window = 'week', int $page = 1): array
    {
        return $this->get("/trending/{$type}/{$window}", ['page' => $page]);
    }

    public function getByGenre(string $type, int $genreId, ?int $providerId = null, int $page = 1): array
    {
        $params = [
            'with_genres' => $genreId,
            'sort_by' => 'popularity.desc',
            'include_adult' => false,
            'page' => $page,
        ];

        if ($providerId) {
            $params['with_watch_providers'] = $providerId;
            $params['watch_region'] = $this->getWatchRegion();
        }

        return $this->get("/discover/{$type}", $params);
    }

    public function getPopularByProvider(int $providerId, string $type = 'movie'): array
    {
        $params = [
            'with_watch_providers' => $providerId,
            'watch_region' => $this->getWatchRegion(),
            'sort_by' => 'popularity.desc',
            'include_adult' => false,
        ];

        return $this->get("/discover/{$type}", $params);
    }

    /**
     * Discover movies or TV with full filters. Returns ['results' => [], 'total_pages' => int].
     *
     * @param  array{type: 'movie'|'tv', page: int, provider?: int, genres?: int[], date_from?: string, date_to?: string, language?: string, vote_min?: float, vote_count_min?: int, sort?: string}  $filters
     */
    public function discoverBrowse(array $filters): array
    {
        $type = $filters['type'] ?? 'movie';
        $page = max(1, (int) ($filters['page'] ?? 1));

        $params = [
            'include_adult' => false,
            'page' => $page,
            'sort_by' => $this->mapSortBy($filters['sort'] ?? 'primary_release_date.desc', $type),
        ];

        if (! empty($filters['provider'])) {
            $params['with_watch_providers'] = (int) $filters['provider'];
            $params['watch_region'] = $this->getWatchRegion();
        }

        if (! empty($filters['genres'])) {
            $ids = is_array($filters['genres']) ? $filters['genres'] : explode(',', $filters['genres']);
            $params['with_genres'] = implode(',', array_map('intval', $ids));
        }

        if (! empty($filters['date_from'])) {
            $key = $type === 'movie' ? 'primary_release_date.gte' : 'first_air_date.gte';
            $params[$key] = $filters['date_from'];
        }
        if (! empty($filters['date_to'])) {
            $key = $type === 'movie' ? 'primary_release_date.lte' : 'first_air_date.lte';
            $params[$key] = $filters['date_to'];
        }

        if (! empty($filters['language'])) {
            $params['with_original_language'] = $filters['language'];
        }

        if (isset($filters['vote_min']) && $filters['vote_min'] !== '' && $filters['vote_min'] !== null) {
            $params['vote_average.gte'] = (float) $filters['vote_min'];
        }
        if (isset($filters['vote_count_min']) && $filters['vote_count_min'] !== '' && $filters['vote_count_min'] !== null) {
            $params['vote_count.gte'] = (int) $filters['vote_count_min'];
        }

        $data = $this->get("/discover/{$type}", $params);

        return [
            'results' => $data['results'] ?? [],
            'total_pages' => min($data['total_pages'] ?? 1, 500),
            'total_results' => min($data['total_results'] ?? 0, 500 * 20),
            'page' => $page,
        ];
    }

    protected function mapSortBy(string $sort, string $type): string
    {
        $map = [
            'release_date_desc' => $type === 'movie' ? 'primary_release_date.desc' : 'first_air_date.desc',
            'release_date_asc' => $type === 'movie' ? 'primary_release_date.asc' : 'first_air_date.asc',
            'popularity_desc' => 'popularity.desc',
            'vote_average_desc' => 'vote_average.desc',
            'vote_count_desc' => 'vote_count.desc',
        ];

        return $map[$sort] ?? ($type === 'movie' ? 'primary_release_date.desc' : 'first_air_date.desc');
    }

    /**
     * Search multi (title/person) with optional type filter. Returns ['results' => [], 'total_pages' => int].
     */
    public function searchBrowse(string $query, string $type = 'multi', int $page = 1): array
    {
        $data = $this->search($query, $type, $page);

        return [
            'results' => $data['results'] ?? [],
            'total_pages' => min($data['total_pages'] ?? 1, 500),
            'total_results' => min($data['total_results'] ?? 0, 500 * 20),
            'page' => $page,
        ];
    }

    /**
     * @return array<int, string> id => name
     */
    public function getGenresForBrowse(): array
    {
        $map = $this->getGenreMap();
        $movieIds = [28, 12, 16, 35, 80, 99, 18, 10751, 14, 36, 27, 10402, 9648, 10749, 878, 53, 10770, 10752, 37];
        $tvIds = [10759, 16, 35, 80, 99, 18, 10751, 10762, 9648, 10763, 10764, 10765, 10766, 10767, 10768, 36, 37];

        $out = [];
        foreach (array_unique(array_merge($movieIds, $tvIds)) as $id) {
            if (isset($map[$id])) {
                $out[$id] = $map[$id];
            }
        }
        asort($out);

        return $out;
    }

    public function getItemSummary(int $id, string $mediaType): array
    {
        return $this->get("/{$mediaType}/{$id}", [
            'append_to_response' => 'images',
            'include_image_language' => $this->getTmdbLanguage() . ',null',
        ]);
    }

    public function getDetails(string $type, int $id): array
    {
        return $this->get("/{$type}/{$id}", [
            'append_to_response' => 'credits,videos,images,similar,watch/providers,external_ids',
            'include_image_language' => $this->getTmdbLanguage() . ',null',
        ]);
    }

    public function getEpisodeDetails(int $tvId, int $season, int $episode): array
    {
        return $this->get("/tv/{$tvId}/season/{$season}/episode/{$episode}", [
            'append_to_response' => 'credits,images',
        ]);
    }

    public function getReviews(int $id, string $mediaType): array
    {
        $params = ['api_key' => $this->apiKey];
        $cacheKey = 'tmdb_' . md5("/{$mediaType}/{$id}/reviews" . json_encode($params));

        return Cache::remember($cacheKey, now()->addHours(3), function () use ($id, $mediaType, $params) {
            $response = Http::timeout(10)->get("{$this->baseUrl}/{$mediaType}/{$id}/reviews", $params);

            if ($response->failed()) {
                return ['results' => []];
            }

            return $response->json() ?? ['results' => []];
        });
    }

    public function getPersonDetails(int $id): array
    {
        return $this->get("/person/{$id}", [
            'append_to_response' => 'combined_credits,images',
        ]);
    }

    public function search(string $query, string $type = 'multi', int $page = 1): array
    {
        return $this->get("/search/{$type}", [
            'query' => $query,
            'page' => $page,
            'include_adult' => false,
        ]);
    }

    public function getLanguages(): array
    {
        $data = $this->get('/configuration/languages') ?? [];
        $languages = array_map(function ($language) {
            $name = !empty($language['name']) ? $language['name'] : $language['english_name'];
            return [
                'code' => $language['iso_639_1'] ?? '',
                'name' => $name,
            ];
        }, $data);

        // Trier par le champ 'name' en ordre alphabétique
        usort($languages, function ($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });

        return $languages;
    }


    public function getMovieProviders(): array
    {
        return $this->get('/watch/providers/movie')['results'] ?? [];
    }

    public function getTvProviders(): array
    {
        return $this->get('/watch/providers/tv')['results'] ?? [];
    }

    public function getProviders(): array
    {
        $movieProviders = $this->getMovieProviders();
        $tvProviders = $this->getTvProviders();
        $allProviders = array_merge($movieProviders, $tvProviders);

        $providers = array_reduce($allProviders, function ($carry, $provider) {
            $id = $provider['provider_id'];
            if (!isset($carry[$id])) {
                $carry[$id] = $this->formatWatchProvider($provider);
            }
            return $carry;
        }, []);

        // Sort providers alphabetically by 'name'
        usort($providers, function ($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });

        return array_values($providers);
    }

    /**
     * Return watch providers list for browse filter: movie only, tv only, or merged.
     *
     * @param  'movie'|'tv'|'all'  $type
     * @return array<int, array{id: int, name: string, logo: string|null}>
     */
    public function getProvidersForType(string $type): array
    {
        if ($type === 'movie') {
            $raw = $this->getMovieProviders();
        } elseif ($type === 'tv') {
            $raw = $this->getTvProviders();
        } else {
            return $this->getProviders();
        }

        $providers = array_map(fn($p) => $this->formatWatchProvider($p), $raw);
        usort($providers, fn($a, $b) => strcasecmp($a['name'], $b['name']));

        return array_values($providers);
    }

    /**
     * @param  array{provider_id: int, provider_name: string, logo_path?: string|null}  $provider
     * @return array{id: int, name: string, logo: string|null}
     */
    private function formatWatchProvider(array $provider): array
    {
        return [
            'id'   => $provider['provider_id'],
            'name' => $provider['provider_name'],
            'logo' => TmdbResource::imageUrl($provider['logo_path'] ?? null, 'original'),
        ];
    }

    public function getPreferedProviders(): array
    {
        $preferedProvidersIds = [8, 337, 119, 350, 381, 384, 15, 283, 173];
        $preferedProvidersIds = array_flip($preferedProvidersIds);
        return array_values(array_filter(
            $this->getProviders(),
            fn($provider) => isset($preferedProvidersIds[$provider['id']])
        ));
    }

    public function getMovieCategories(): array
    {
        return $this->get('/genre/movie/list') ?? [];
    }

    public function getTvCategories(): array
    {
        return $this->get('/genre/movie/list') ?? [];
    }

    public function getCategories(): array
    {
        $movieCategories = $this->getMovieCategories();
        $tvCategories = $this->getTvCategories();
        $allCategories = array_merge($movieCategories, $tvCategories);

        // Sort providers alphabetically by 'name'
        usort($allCategories, function ($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });

        return array_values($allCategories);
    }
}
