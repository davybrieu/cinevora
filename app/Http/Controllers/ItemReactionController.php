<?php

namespace App\Http\Controllers;

use App\Models\ItemReaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ItemReactionController extends Controller
{
    public function set(Request $request): RedirectResponse
    {
        $request->validate([
            'item_id' => 'required|integer',
            'item_type' => 'required|in:movie,tv',
            'reaction' => 'required|in:like,dislike',
        ]);

        $profileId = $request->session()->get('profile_id');

        abort_if(!$profileId, 403);

        $existing = ItemReaction::where('profile_id', $profileId)
            ->where('item_id', $request->item_id)
            ->where('item_type', $request->item_type)
            ->first();

        if ($existing) {
            if ($existing->reaction === $request->reaction) {
                $existing->delete();
            } else {
                $existing->update(['reaction' => $request->reaction]);
            }
        } else {
            ItemReaction::create([
                'profile_id' => $profileId,
                'item_id' => $request->item_id,
                'item_type' => $request->item_type,
                'reaction' => $request->reaction,
            ]);
        }

        return back();
    }
}
