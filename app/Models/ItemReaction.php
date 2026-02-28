<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemReaction extends Model
{
    protected $fillable = [
        'profile_id',
        'item_id',
        'item_type',
        'reaction',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Merge like_count and dislike_count into each item.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    public static function mergeCountsIntoItems(array $items): array
    {
        if (empty($items)) {
            return $items;
        }

        $pairs = collect($items)
            ->map(fn($i) => ['item_id' => (int) ($i['id'] ?? 0), 'item_type' => $i['media_type'] ?? 'movie'])
            ->filter(fn($p) => $p['item_id'] > 0)
            ->unique(fn($p) => $p['item_type'] . '-' . $p['item_id'])
            ->values()
            ->all();

        if (empty($pairs)) {
            return array_map(fn($item) => array_merge($item, ['like_count' => 0, 'dislike_count' => 0]), $items);
        }

        $query = static::query()
            ->selectRaw('item_id, item_type, reaction, count(*) as cnt')
            ->groupBy('item_id', 'item_type', 'reaction');

        $query->where(function ($q) use ($pairs) {
            foreach ($pairs as $p) {
                $q->orWhere(function ($q2) use ($p) {
                    $q2->where('item_id', $p['item_id'])->where('item_type', $p['item_type']);
                });
            }
        });

        $rows = $query->get();

        $map = [];
        foreach ($rows as $row) {
            $key = $row->item_type . '-' . $row->item_id;
            $map[$key] ??= ['like_count' => 0, 'dislike_count' => 0];
            $map[$key][$row->reaction . '_count'] = (int) $row->cnt;
        }

        return array_map(function ($item) use ($map) {
            $key = ($item['media_type'] ?? 'movie') . '-' . ($item['id'] ?? 0);
            $item['like_count'] = $map[$key]['like_count'] ?? 0;
            $item['dislike_count'] = $map[$key]['dislike_count'] ?? 0;
            return $item;
        }, $items);
    }
}
