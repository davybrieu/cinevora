<?php

namespace App\Http\Controllers;

use App\Http\Resources\TmdbResource;
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

        // Chercher dans WatchProgress si on a une progression
        $progress = 0;
        if ($profileId) {
            $progressQuery = WatchProgress::where('profile_id', $profileId)
                ->where('item_id', $id)
                ->where('item_type', $type);

            if ($type === 'tv' && $season !== null && $episode !== null) {
                $progressQuery = $progressQuery
                    ->where('season', $season)
                    ->where('episode', $episode);
            }

            $progressRow = $progressQuery->orderByDesc('updated_at')->first();
            if ($progressRow && $progressRow->progress > 0) {
                $progress = $progressRow->progress;
            }
        }

        if ($type === 'movie') {
            $iframeSrc = "https://player.videasy.net/movie/{$id}?overlay=true&progress={$progress}";
        } else if ($type === 'tv') {
            $iframeSrc = "https://player.videasy.net/tv/{$id}/{$season}/{$episode}?overlay=true&nextEpisode=false&episodeSelector=false&progress={$progress}";
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
