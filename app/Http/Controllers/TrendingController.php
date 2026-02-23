<?php

namespace App\Http\Controllers;

use App\Http\Resources\TmdbResource;
use App\Models\WatchProgress;
use App\Services\TmdbService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TrendingController extends Controller
{
    public function __construct(
        protected TmdbService $tmdb,
        protected TmdbResource $resource,
    ) {}

    public function index(Request $request): Response
    {
        $timeWindow = $request->query('time', 'day');
        $mediaType = $request->query('type', 'all');

        $data = $this->tmdb->getTrending($mediaType, $timeWindow);

        $items = collect($data['results'] ?? [])
            ->map(fn($item) => $this->resource->formatItem($item))
            ->values()
            ->all();

        $profileId = $request->session()->get('profile_id');
        $items = WatchProgress::mergeProgressIntoItems($items, $profileId);

        return Inertia::render('Trending', [
            'items' => $items,
            'totalResults' => $data['total_results'] ?? 0,
            'currentPage' => $data['page'] ?? 1,
            'totalPages' => $data['total_pages'] ?? 1,
            'filters' => [
                'time' => $timeWindow,
                'type' => $mediaType,
            ],
        ]);
    }

    public function loadMore(Request $request): JsonResponse
    {
        $timeWindow = $request->query('time', 'day');
        $mediaType = $request->query('type', 'all');
        $page = (int) $request->query('page', 1);

        $data = $this->tmdb->getTrending($mediaType, $timeWindow, $page);

        $items = collect($data['results'] ?? [])
            ->map(fn($item) => $this->resource->formatItem($item))
            ->values()
            ->all();

        $profileId = $request->session()->get('profile_id');
        $items = WatchProgress::mergeProgressIntoItems($items, $profileId);

        return response()->json([
            'items' => $items,
            'currentPage' => $data['page'] ?? 1,
            'totalPages' => $data['total_pages'] ?? 1,
        ]);
    }
}
