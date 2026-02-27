<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;

class TorrentioService
{
    public static string $defaultBaseUrl = 'https://torrentio.strem.fun/stream';
    public static array $defaultProviders = [
        'yts' => 'YTS',
        'eztv' => 'EZTV',
        'rarbg' => 'RARBG',
        '1337x' => '1337X',
        'thepiratebay' => 'The Pirate Bay',
        'kickasstorrents' => 'Kickass Torrents',
        'torrentgalaxy' => 'Torrent Galaxy',
        'magnetdl' => 'MagnetDL',
        'horriblesubs' => 'HorribleSubs',
        'nyaasi' => 'NyaaSi',
        'tokyotosho' => 'Tokyo Tosho',
        'anidex' => 'Anidex',
        'torrent9' => 'Torrent9'
    ];
    public static array $defaultLanguages = [
        'english' => 'English',
        'french' => 'French',
        'spanish' => 'Spanish',
        'italian' => 'Italian',
        'german' => 'German',
        'japanese' => 'Japanese',
        'portuguese' => 'Portuguese',
        'russian' => 'Russian',
        'turkish' => 'Turkish',
        'chinese' => 'Chinese',
        'korean' => 'Korean',
        'arabic' => 'Arabic',
        'hindi' => 'Hindi',
        'bengali' => 'Bengali',
    ];

    /**
     * Fetch streams for a movie by IMDB ID.
     *
     * @return array{streams: array, default_index: int}
     */
    public function getMovieStreams(string $imdbId, ?User $user = null): array
    {
        $url = "{$this->buildBaseUrl($user)}/movie/{$imdbId}.json";
        return $this->fetchStreams($url);
    }

    /**
     * Fetch streams for a TV episode by IMDB ID, season and episode.
     *
     * @return array{streams: array, default_index: int}
     */
    public function getSeriesStreams(string $imdbId, int $season, int $episode, ?User $user = null): array
    {
        $url = "{$this->buildBaseUrl($user)}/series/{$imdbId}:{$season}:{$episode}.json";

        return $this->fetchStreams($url);
    }

    protected function buildBaseUrl(?User $user = null): string
    {
        $providers = $user?->torrentio_providers ?? [];
        $language = $user?->torrentio_language ?? [];
        $realDebridKey = trim((string) ($user?->torrentio_realdebrid_key ?? ''));

        $segments = [];

        if ($providers) {
            $segments[] = 'providers=' . implode(',', $providers);
        }

        if ($language) {
            $segments[] = 'language=' . implode(',', $language);
        }

        if ($realDebridKey !== '') {
            $segments[] = 'realdebrid=' . rawurlencode($realDebridKey);
        }

        return 'https://torrentio.strem.fun/' . implode('|', $segments) . '/stream';
    }

    /**
     * @return array{streams: array, default_index: int}
     */
    protected function fetchStreams(string $url): array
    {
        $response = Http::timeout(15)->get($url);

        if ($response->failed()) {
            return ['streams' => [], 'default_index' => 0];
        }

        $data = $response->json();
        $rawStreams = $data['streams'] ?? [];

        $streams = collect($rawStreams)
            ->map(function ($s) {
                $url = (string) ($s['url'] ?? '');
                $behaviorHints = is_array($s['behaviorHints'] ?? null) ? $s['behaviorHints'] : [];
                $filename = $behaviorHints['filename'] ?? null;
                $infoHash = $s['infoHash'] ?? null;

                if (!$infoHash && !empty($behaviorHints['bingeGroup']) && str_contains($behaviorHints['bingeGroup'], '|')) {
                    $parts = explode('|', (string) $behaviorHints['bingeGroup']);
                    foreach ($parts as $part) {
                        $part = strtolower(trim((string) $part));
                        if (preg_match('/^[a-f0-9]{40}$/', $part)) {
                            $infoHash = $part;
                            break;
                        }
                    }
                }

                if (!$infoHash && $url !== '' && preg_match('/\/([a-f0-9]{40})\//i', $url, $match)) {
                    $infoHash = strtolower($match[1]);
                }

                if (!$filename && $url !== '') {
                    $path = parse_url($url, PHP_URL_PATH);
                    $basename = $path ? basename($path) : null;
                    $filename = $basename ? urldecode($basename) : null;
                }

                return [
                    'name' => str_replace("\n", ' · ', trim($s['name'] ?? '')),
                    'title' => trim($s['title'] ?? ''),
                    'infoHash' => $infoHash,
                    'fileIdx' => isset($s['fileIdx']) ? (int) $s['fileIdx'] : null,
                    'filename' => $filename,
                    'url' => $url !== '' ? $url : null,
                ];
            })
            ->filter(fn($s) => ! empty($s['infoHash']) || ! empty($s['url']))
            ->values()
            ->all();

        return [
            'streams' => $streams,
            'default_index' => 0,
        ];
    }
}
