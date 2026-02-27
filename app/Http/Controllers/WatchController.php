<?php

namespace App\Http\Controllers;

use App\Http\Resources\TmdbResource;
use App\Models\User;
use App\Models\WatchProgress;
use App\Services\TmdbService;
use App\Services\TorrentioService;
use Inertia\Inertia;
use Inertia\Response;

class WatchController extends Controller
{
    public function __construct(
        protected TmdbService $tmdb,
        protected TmdbResource $resource,
        protected TorrentioService $torrentio,
    ) {}

    public function movie(int $id): Response
    {
        return $this->renderWatch('movie', $id, null, null);
    }

    public function tv(int $id, int $season, int $episode): Response
    {
        return $this->renderWatch('tv', $id, $season, $episode);
    }

    private function renderWatch(string $type, int $id, $season = null, $episode = null): Response
    {
        $data = $this->tmdb->getDetails($type, $id);

        abort_if(empty($data['id']), 404);

        $profileId = request()->session()->get('profile_id');
        $item = $this->resource->formatDetail($data, $type, $profileId);

        $streams = $this->getStreams($item['imdb_id'], $type, $season, $episode, request()->user());

        $progress = $this->getProgress($id, $type, $season, $episode);

        return Inertia::render('Watch', [
            'type' => $type,
            'item' => $item,
            'tmdb_id' => $id,
            'season' => $season,
            'episode' => $episode,
            'streams' => $streams,
            'progress' => $progress,
        ]);
    }

    private function getStreams(string $imdbId, string $movieType, ?int $season = null, ?int $episode = null, ?User $user = null): array
    {
        if ($movieType === 'tv') {
            $payload = $this->torrentio->getSeriesStreams($imdbId, $season, $episode, $user);
        } elseif ($movieType === "movie") {
            $payload = $this->torrentio->getMovieStreams($imdbId, $user);
        } else {
            return [];
        }

        $streams = $payload['streams'] ?? [];
        if (!is_array($streams)) {
            return [];
        }

        return collect($streams)
            ->map(fn($stream) => [
                'infoHash' => strtolower((string) ($stream['infoHash'] ?? '')),
                'title' => (string) ($stream['title'] ?? ''),
                'name' => (string) ($stream['name'] ?? ''),
                'fileIdx' => isset($stream['fileIdx']) ? (int) $stream['fileIdx'] : null,
                'filename' => $stream['filename'] ?? null,
                'url' => $stream['url'] ?? null,
            ])
            ->values()
            ->all();
    }

    private function getProgress(int $itemId, string $itemType, ?int $season = null, ?int $episode = null): array
    {
        $profileId = request()->session()->get('profile_id');
        if (!$profileId) {
            return ['watched_progress' => 0, 'watched_duration' => 0];
        }

        $query = WatchProgress::query()
            ->where('profile_id', $profileId)
            ->where('item_id', $itemId)
            ->where('item_type', $itemType);

        if ($itemType === 'tv') {
            $query
                ->where('season', $season ?? 0)
                ->where('episode', $episode ?? 0);
        } else {
            $query
                ->where('season', 0)
                ->where('episode', 0);
        }

        $progress = $query->first();
        if (!$progress) {
            return ['watched_progress' => 0, 'watched_duration' => 0];
        }

        return [
            'watched_progress' => (int) $progress->progress,
            'watched_duration' => (int) $progress->duration,
        ];
    }
}
