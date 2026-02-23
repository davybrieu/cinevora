<?php

namespace App\Http\Controllers;

use App\Http\Resources\TmdbResource;
use App\Models\WatchProgress;
use App\Models\Watchlist;
use App\Services\TmdbService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WatchlistController extends Controller
{
    public function __construct(
        protected TmdbService $tmdb,
        protected TmdbResource $resource,
    ) {}

    public function index(Request $request): Response
    {
        $profileId = $request->session()->get('profile_id');

        abort_if(!$profileId, 403);

        $watchlistItems = Watchlist::where('profile_id', $profileId)
            ->orderBy('created_at', 'desc')
            ->get();

        $items = $watchlistItems->map(function (Watchlist $watchlist) {
            $data = $this->tmdb->getItemSummary($watchlist->item_id, $watchlist->item_type);

            if (empty($data['id'])) {
                return null;
            }

            $item = $this->resource->formatItem($data, $watchlist->item_type);
            $item['watchlist_id'] = $watchlist->id;

            return $item;
        })->filter()->values()->all();

        $items = WatchProgress::mergeProgressIntoItems($items, $profileId);

        return Inertia::render('Watchlist', [
            'items' => $items,
        ]);
    }

    public function toggle(Request $request): RedirectResponse
    {
        $request->validate([
            'item_id' => 'required|integer',
            'item_type' => 'required|in:movie,tv',
        ]);

        $profileId = $request->session()->get('profile_id');

        abort_if(!$profileId, 403);

        $watchlist = Watchlist::where('profile_id', $profileId)
            ->where('item_id', $request->item_id)
            ->where('item_type', $request->item_type)
            ->first();

        if ($watchlist) {
            $watchlist->delete();
        } else {
            Watchlist::create([
                'profile_id' => $profileId,
                'item_id' => $request->item_id,
                'item_type' => $request->item_type,
            ]);
        }

        return back();
    }
}
