<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WatchProgress extends Model
{
    protected $table = 'watch_progress';

    protected $fillable = [
        'profile_id',
        'item_id',
        'item_type',
        'progress',
        'duration',
        'season',
        'episode',
    ];

    protected $casts = [
        'progress' => 'integer',
        'duration' => 'integer',
        'season' => 'integer',
        'episode' => 'integer',
    ];

    protected $appends = [
        'progress_percentage',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function getProgressPercentageAttribute(): float
    {
        if (!$this->duration || $this->duration <= 0) {
            return 0.0;
        }
        return min(100, (float) ($this->progress / $this->duration * 100));
    }

    /**
     * Get watch progress percentage for a list of items (for current profile).
     * Returns map: ["movie-123" => 45.2, "tv-456" => 80.0]
     * For TV we use the max progress across all season/episode for that item.
     *
     * @param  int|null  $profileId
     * @param  array<int, array{id: int, media_type: string}>  $items
     * @return array<string, float>
     */
    public static function getProgressMapForItems(?int $profileId, array $items): array
    {
        if (!$profileId || empty($items)) {
            return [];
        }

        $itemIds = collect($items)
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($itemIds === []) {
            return [];
        }

        $rows = self::query()
            ->where('profile_id', $profileId)
            ->whereIn('item_id', $itemIds)
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $key = $row->item_type . '-' . $row->item_id;
            $pct = $row->progress_percentage;
            if (! isset($map[$key]) || $pct > $map[$key]) {
                $map[$key] = $pct;
            }
        }

        return $map;
    }

    /**
     * Merge watch_progress_percentage into each item (in place).
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    public static function mergeProgressIntoItems(array $items, ?int $profileId): array
    {
        $map = self::getProgressMapForItems($profileId, $items);
        return array_map(function ($item) use ($map) {
            $key = ($item['media_type'] ?? 'movie') . '-' . ($item['id'] ?? 0);
            $item['watch_progress_percentage'] = isset($map[$key]) ? round($map[$key], 1) : null;
            return $item;
        }, $items);
    }
}
