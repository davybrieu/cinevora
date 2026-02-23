<?php

namespace App\Http\Resources;

use App\Services\TmdbService;
use Illuminate\Support\Facades\App;
use Carbon\Carbon;

class TmdbResource
{
    public function __construct(
        protected TmdbService $tmdb,
    ) {}

    public static function imageUrl(?string $path, string $size = 'w500'): ?string
    {
        if (empty($path)) {
            return null;
        }

        return config('services.tmdb.image_base_url') . "{$size}{$path}";
    }

    public function formatItem(array $item, ?string $mediaType = null): array
    {
        $genreMap = $this->tmdb->getGenreMap();
        $runtime = $this->formatRuntime($item['runtime'] ?? ($item['episode_run_time'][0] ?? null));

        // Use formatDate for all date fields
        $releaseDateRaw = $item['release_date'] ?? $item['first_air_date'] ?? '';
        $releaseDate = $this->formatDate($releaseDateRaw);

        return [
            'id' => $item['id'],
            'title' => $item['title'] ?? $item['name'] ?? '',
            'overview' => $item['overview'] ?? '',
            'poster_path' => self::imageUrl($item['poster_path'] ?? null),
            'backdrop_path' => self::imageUrl($item['backdrop_path'] ?? null, 'original'),
            'vote_average' => round($item['vote_average'] ?? 0, 1),
            'release_date' => $releaseDate,
            'runtime' => $runtime,
            'number_of_seasons' => $item['number_of_seasons'] ?? null,
            'media_type' => $mediaType ?? $item['media_type'] ?? 'movie',
            'genres' => collect($item['genre_ids'] ?? [])
                ->map(function ($id) use ($genreMap) {
                    $id = (int) $id;
                    if (! isset($genreMap[$id])) {
                        return null;
                    }
                    return [
                        'id' => $id,
                        'name' => $genreMap[$id],
                    ];
                })
                ->filter()
                ->values()
                ->all(),
        ];
    }

    public function withDetails(array $items): array
    {
        return array_map(function (array $item) {
            $details = $this->tmdb->getItemSummary($item['id'], $item['media_type']);
            $item['logo_path'] = self::imageUrl($details['images']['logos'][0]['file_path'] ?? null);
            $item['runtime'] = $this->formatRuntime($details['runtime'] ?? ($details['episode_run_time'][0] ?? null));
            $item['number_of_seasons'] = $details['number_of_seasons'] ?? null;

            return $item;
        }, $items);
    }

    public function formatCredit(array $item): array
    {
        // Use formatDate for release_date
        $releaseDateRaw = $item['release_date'] ?? $item['first_air_date'] ?? '';
        $releaseDate = $this->formatDate($releaseDateRaw);

        return [
            'id' => $item['id'],
            'title' => $item['title'] ?? $item['name'] ?? '',
            'overview' => $item['overview'] ?? '',
            'poster_path' => self::imageUrl($item['poster_path'] ?? null),
            'backdrop_path' => self::imageUrl($item['backdrop_path'] ?? null, 'original'),
            'vote_average' => round($item['vote_average'] ?? 0, 1),
            'release_date' => $releaseDate,
            'media_type' => $item['media_type'] ?? 'movie',
            'character' => $item['character'] ?? '',
        ];
    }

    public function formatDetail(array $data, string $mediaType): array
    {
        $region = App::getLocale() === 'en' ? 'US' : 'FR';
        $providers = $data['watch/providers']['results'][$region] ?? [];
        $runtime = $this->formatRuntime($data['runtime'] ?? ($data['episode_run_time'][0] ?? null));

        // Use formatDate for all date fields
        $releaseDateRaw = $data['release_date'] ?? $data['first_air_date'] ?? '';
        $releaseDate = $this->formatDate($releaseDateRaw);

        $firstAirDate = $this->formatDate($data['first_air_date'] ?? null);
        $lastAirDate = $this->formatDate($data['last_air_date'] ?? null);

        $imdbId = $data['imdb_id'] ?? ($data['external_ids']['imdb_id'] ?? null);

        $result = [
            'id' => $data['id'],
            'imdb_id' => $imdbId ? (str_starts_with($imdbId, 'tt') ? $imdbId : 'tt' . $imdbId) : null,
            'media_type' => $mediaType,
            'title' => $data['title'] ?? $data['name'] ?? '',
            'original_title' => $data['original_title'] ?? $data['original_name'] ?? '',
            'tagline' => $data['tagline'] ?? '',
            'overview' => $data['overview'] ?? '',
            'poster_path' => self::imageUrl($data['poster_path'] ?? null),
            'backdrop_path' => self::imageUrl($data['backdrop_path'] ?? null, 'original'),
            'logo_path' => self::imageUrl($data['images']['logos'][0]['file_path'] ?? null),
            'vote_average' => round($data['vote_average'] ?? 0, 1),
            'vote_count' => $data['vote_count'] ?? 0,
            'release_date' => $releaseDate,
            'first_air_date' => $firstAirDate,
            'last_air_date' => $lastAirDate,
            'runtime' => $runtime,
            'status' => $this->translateStatus($data['status'] ?? ''),
            'genres' => collect($data['genres'] ?? [])->map(fn($g) => [
                'id' => (int) ($g['id'] ?? 0),
                'name' => $g['name'] ?? '',
            ])->filter(fn($g) => ! empty($g['id']) && $g['name'] !== '')->values()->all(),
            'number_of_seasons' => $data['number_of_seasons'] ?? null,
            'number_of_episodes' => $data['number_of_episodes'] ?? null,
            'budget' => $data['budget'] ?? null,
            'revenue' => $data['revenue'] ?? null,
            'original_language' => $data['original_language'] ?? null,
            'spoken_languages' => collect($data['spoken_languages'] ?? [])->pluck('english_name')->all(),
            'production_companies' => collect($data['production_companies'] ?? [])->map(fn($c) => [
                'id' => $c['id'],
                'name' => $c['name'] ?? '',
                'logo_path' => self::imageUrl($c['logo_path'] ?? null, 'w185'),
                'origin_country' => $c['origin_country'] ?? '',
            ])->all(),
            'production_countries' => collect($data['production_countries'] ?? [])->pluck('name')->all(),
            'cast' => collect($data['credits']['cast'] ?? [])
                ->map(fn($p) => [
                    'id' => $p['id'],
                    'name' => $p['name'],
                    'character' => $p['character'] ?? '',
                    'profile_path' => self::imageUrl($p['profile_path'] ?? null, 'w185'),
                ])
                ->all(),
            'crew' => collect($data['credits']['crew'] ?? [])
                ->filter(fn($p) => in_array($p['job'] ?? '', ['Director', 'Writer', 'Screenplay', 'Creator', 'Producer', 'Executive Producer', 'Original Music Composer']))
                ->unique('id')
                ->map(fn($p) => [
                    'id' => $p['id'],
                    'name' => $p['name'],
                    'job' => $p['job'] ?? '',
                    'profile_path' => self::imageUrl($p['profile_path'] ?? null, 'w185'),
                ])
                ->values()
                ->all(),
            'videos' => collect($data['videos']['results'] ?? [])
                ->filter(fn($v) => $v['site'] === 'YouTube')
                ->map(fn($v) => [
                    'key' => $v['key'],
                    'name' => $v['name'],
                    'type' => $v['type'] ?? '',
                ])
                ->values()
                ->all(),
            'backdrops' => collect($data['images']['backdrops'] ?? [])
                ->map(fn($img) => self::imageUrl($img['file_path'], 'w1280'))
                ->all(),
            'posters' => collect($data['images']['posters'] ?? [])
                ->map(fn($img) => self::imageUrl($img['file_path']))
                ->all(),
            'providers' => [
                'flatrate' => $this->formatProviders($providers['flatrate'] ?? []),
                'rent' => $this->formatProviders($providers['rent'] ?? []),
                'buy' => $this->formatProviders($providers['buy'] ?? []),
                'link' => $providers['link'] ?? null,
            ],
            'reviews' => collect($data['reviews']['results'] ?? [])
                ->map(fn($r) => [
                    'id' => $r['id'],
                    'author' => $r['author'] ?? '',
                    'author_username' => $r['author_details']['username'] ?? '',
                    'author_avatar' => $this->formatAvatarUrl($r['author_details']['avatar_path'] ?? null),
                    'author_rating' => $r['author_details']['rating'] ?? null,
                    'content' => $r['content'] ?? '',
                    'created_at' => $this->formatDate($r['created_at'] ?? null),
                ])
                ->all(),
            'similar' => collect($data['similar']['results'] ?? [])
                ->take(20)
                ->map(fn($item) => $this->formatItem($item, $mediaType))
                ->all(),
        ];

        if ($mediaType === 'tv') {
            $result['seasons'] = collect($data['seasons'] ?? [])
                ->filter(fn($s) => ($s['season_number'] ?? 0) >= 0)
                ->map(fn($s) => [
                    'id' => $s['id'] ?? null,
                    'season_number' => (int) ($s['season_number'] ?? 0),
                    'name' => $s['name'] ?? '',
                    'episode_count' => (int) ($s['episode_count'] ?? 0),
                    'poster_path' => self::imageUrl($s['poster_path'] ?? null),
                    'overview' => $s['overview'] ?? '',
                ])
                ->values()
                ->all();
        } else {
            $result['seasons'] = [];
        }

        return $result;
    }

    public function formatPerson(array $data): array
    {
        $credits = $data['combined_credits'] ?? [];

        $knownFor = collect($credits['cast'] ?? [])
            ->sortByDesc('popularity')
            ->map(fn($item) => $this->formatCredit($item))
            ->values()
            ->all();

        $castCredits = collect($credits['cast'] ?? [])
            ->sortByDesc(fn($item) => $item['release_date'] ?? $item['first_air_date'] ?? '0000')
            ->map(fn($item) => $this->formatCredit($item))
            ->values()
            ->all();

        $crewCredits = collect($credits['crew'] ?? [])
            ->sortByDesc(fn($item) => $item['release_date'] ?? $item['first_air_date'] ?? '0000')
            ->unique('id')
            ->map(fn($item) => [
                ...$this->formatCredit($item),
                'job' => $item['job'] ?? '',
            ])
            ->values()
            ->all();

        // Use formatDate for birthday and deathday
        $birthday = $this->formatDate($data['birthday'] ?? null);
        $deathday = $this->formatDate($data['deathday'] ?? null);

        return [
            'id' => $data['id'],
            'name' => $data['name'] ?? '',
            'biography' => $data['biography'] ?? '',
            'birthday' => $birthday,
            'deathday' => $deathday,
            'place_of_birth' => $data['place_of_birth'] ?? '',
            'known_for_department' => $data['known_for_department'] ?? '',
            'profile_path' => self::imageUrl($data['profile_path'] ?? null),
            'images' => collect($data['images']['profiles'] ?? [])
                ->map(fn($img) => self::imageUrl($img['file_path']))
                ->all(),
            'known_for' => $knownFor,
            'cast_credits' => $castCredits,
            'crew_credits' => $crewCredits,
        ];
    }

    protected function formatProviders(array $providers): array
    {
        return collect($providers)
            ->map(fn($p) => [
                'name' => $p['provider_name'],
                'logo' => self::imageUrl($p['logo_path'], 'original'),
            ])
            ->all();
    }

    protected function formatAvatarUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (str_starts_with($path, '/http')) {
            return ltrim($path, '/');
        }

        return self::imageUrl($path, 'w185');
    }

    private function formatRuntime(?int $runtime): ?string
    {
        if (! is_null($runtime)) {
            $hours = intdiv($runtime, 60);
            $minutes = $runtime % 60;
            return $hours > 0 ? $hours . 'h' . ($minutes > 0 ? ' ' . $minutes . 'min' : '') : $minutes . 'min';
        }

        return null;
    }

    private function formatDate(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        return ucwords(Carbon::parse($date)->translatedFormat('d M Y'));
    }

    private function translateStatus(string $status): string
    {
        if (empty($status)) {
            return '';
        }

        $key = 'status_' . str_replace(' ', '_', strtolower($status));

        $translated = __($key);

        return $translated !== $key ? $translated : $status;
    }

    /**
     * Format season episodes for API response.
     *
     * @param  array{episodes: array}  $seasonData
     * @return array<int, array{id: int, name: string, overview: string, still_path: string|null, runtime: string|null, episode_number: int, season_number: int, air_date: string|null}>
     */
    public function formatSeasonEpisodes(array $seasonData): array
    {
        $episodes = $seasonData['episodes'] ?? [];

        return collect($episodes)
            ->map(fn($ep) => [
                'id' => (int) ($ep['id'] ?? 0),
                'name' => $ep['name'] ?? '',
                'overview' => $ep['overview'] ?? '',
                'still_path' => self::imageUrl($ep['still_path'] ?? null, 'w300'),
                'runtime' => $this->formatRuntime($ep['runtime'] ?? null),
                'episode_number' => (int) ($ep['episode_number'] ?? 0),
                'season_number' => (int) ($ep['season_number'] ?? 0),
                'air_date' => $this->formatDate($ep['air_date'] ?? null),
            ])
            ->values()
            ->all();
    }
}
