<?php

namespace App\Http\Controllers;

use App\Http\Resources\TmdbResource;
use App\Services\TmdbService;
use Inertia\Inertia;
use Inertia\Response;

class DetailController extends Controller
{
    public function __construct(
        protected TmdbService $tmdb,
        protected TmdbResource $resource,
    ) {}

    public function movie(int $id): Response
    {
        return $this->show('movie', $id);
    }

    public function tv(int $id): Response
    {
        return $this->show('tv', $id);
    }

    protected function show(string $type, int $id): Response
    {
        $data = $this->tmdb->getDetails($type, $id);

        abort_if(empty($data['id']), 404);

        $data['reviews'] = $this->tmdb->getReviews($id, $type);

        $profileId = request()->session()->get('profile_id');

        return Inertia::render('Detail', [
            'item' => $this->resource->formatDetail($data, $type, $profileId),
        ]);
    }

    /**
     * Return episodes for a TV season (JSON for frontend fetch).
     */
    public function seasonEpisodes(int $id, int $season): \Illuminate\Http\JsonResponse
    {
        $data = $this->tmdb->getSeasonDetails($id, $season);
        $episodes = $this->resource->formatSeasonEpisodes($data);

        return response()->json(['episodes' => $episodes]);
    }
}
