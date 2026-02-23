<?php

namespace App\Http\Controllers;

use App\Http\Resources\TmdbResource;
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

        if ($type === 'movie') {
            $iframeSrc = "https://player.videasy.net/movie/{$id}?overlay=true";
        } else if ($type === 'tv') {
            $iframeSrc = "https://player.videasy.net/tv/{$id}/{$season}/{$episode}?overlay=true&nextEpisode=false&episodeSelector=false";
        }

        return Inertia::render('Watch', [
            'type' => $type,
            'item' => $item,
            'tmdb_id' => $id,
            'season' => $season,
            'episode' => $episode,
            'iframeSrc' => $iframeSrc,
        ]);
    }
}
