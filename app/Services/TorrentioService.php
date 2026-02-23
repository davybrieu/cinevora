<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TorrentioService
{
    protected string $baseUrl = 'https://torrentio.strem.fun/stream';

    /**
     * Fetch streams for a movie by IMDB ID.
     *
     * @return array{streams: array, default_index: int}
     */
    public function getMovieStreams(string $imdbId): array
    {
        $url = "{$this->baseUrl}/movie/{$imdbId}.json";

        return $this->fetchStreams($url);
    }

    /**
     * Fetch streams for a TV episode by IMDB ID, season and episode.
     *
     * @return array{streams: array, default_index: int}
     */
    public function getSeriesStreams(string $imdbId, int $season, int $episode): array
    {
        $url = "{$this->baseUrl}/series/{$imdbId}:{$season}:{$episode}.json";

        return $this->fetchStreams($url);
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
            ->map(fn ($s) => [
                'name' => str_replace("\n", ' · ', trim($s['name'] ?? '')),
                'title' => trim($s['title'] ?? ''),
                'infoHash' => $s['infoHash'] ?? null,
                'fileIdx' => (int) ($s['fileIdx'] ?? 0),
                'filename' => ($s['behaviorHints'] ?? [])['filename'] ?? null,
            ])
            ->filter(fn ($s) => ! empty($s['infoHash']))
            ->values()
            ->all();

        return [
            'streams' => $streams,
            'default_index' => 0,
        ];
    }
}
