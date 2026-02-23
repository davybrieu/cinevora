<?php

namespace App\Http\Controllers;

use App\Models\WatchProgress;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WatchProgressController extends Controller
{
    public function store(Request $request): Response
    {
        $profileId = $request->session()->get('profile_id');
        abort_if(!$profileId, 403);

        $validated = $request->validate([
            'item_id' => 'required|integer',
            'item_type' => 'required|in:movie,tv',
            'watched_progress' => 'required|integer',
            'watched_duration' => 'required|integer',
            'season' => 'required|integer|min:0',
            'episode' => 'required|integer|min:0',
        ]);

        WatchProgress::updateOrCreate(
            [
                'profile_id' => $profileId,
                'item_id' => $validated['item_id'],
                'item_type' => $validated['item_type'],
                'season' => $validated['season'],
                'episode' => $validated['episode'],
            ],
            [
                'progress' => $validated['watched_progress'],
                'duration' => $validated['watched_duration'],
            ]
        );

        return response()->noContent(204);
    }
}
