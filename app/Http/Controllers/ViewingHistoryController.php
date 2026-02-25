<?php

namespace App\Http\Controllers;

use App\Http\Resources\TmdbResource;
use App\Models\WatchProgress;
use App\Services\TmdbService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ViewingHistoryController extends Controller
{
    public function __construct(
        protected TmdbService $tmdb,
        protected TmdbResource $resource,
    ) {}

    public function index(Request $request): Response
    {
        $profileId = $request->session()->get('profile_id');
        abort_if(! $profileId, 403);

        $rows = WatchProgress::query()
            ->where('profile_id', $profileId)
            ->orderByDesc('updated_at')
            ->get();

        $stats = $this->computeStats($rows);
        $historyByDate = $this->buildHistoryGroupedByDate($rows);

        return Inertia::render('ViewingHistory', [
            'stats' => $stats,
            'historyByDate' => $historyByDate,
        ]);
    }

    public function destroyAll(Request $request): \Illuminate\Http\RedirectResponse
    {
        $profileId = $request->session()->get('profile_id');
        abort_if(! $profileId, 403);

        WatchProgress::where('profile_id', $profileId)->delete();

        return back();
    }

    public function destroy(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        $profileId = $request->session()->get('profile_id');
        abort_if(! $profileId, 403);

        $row = WatchProgress::where('id', $id)->where('profile_id', $profileId)->firstOrFail();
        $row->delete();

        return back();
    }

    protected function computeStats(\Illuminate\Support\Collection $rows): array
    {
        $moviesWatched = $rows->where('item_type', 'movie')->filter(fn ($r) => $r->progress_percentage >= 95)->count();
        $seriesStarted = $rows->where('item_type', 'tv')->pluck('item_id')->unique()->count();
        $episodesCount = $rows->where('item_type', 'tv')->count();
        $totalSeconds = $rows->sum('progress');

        return [
            'movies_watched' => $moviesWatched,
            'series_started' => $seriesStarted,
            'episodes_count' => $episodesCount,
            'total_seconds' => $totalSeconds,
            'total_time_formatted' => $this->formatDuration($totalSeconds),
        ];
    }

    protected function formatDuration(int $totalSeconds): string
    {
        if ($totalSeconds <= 0) {
            return __('viewing_history_zero_time');
        }
        $hours = intdiv($totalSeconds, 3600);
        $minutes = intdiv($totalSeconds % 3600, 60);
        $seconds = $totalSeconds % 60;

        $parts = [];
        if ($hours > 0) {
            $parts[] = $hours . ' ' . trans_choice('viewing_history_hour', $hours);
        }
        if ($minutes > 0) {
            $parts[] = $minutes . ' ' . trans_choice('viewing_history_minute', $minutes);
        }
        if ($seconds > 0 || empty($parts)) {
            $parts[] = $seconds . ' ' . trans_choice('viewing_history_second', $seconds);
        }

        return implode(' ' . __('viewing_history_and') . ' ', $parts);
    }

    protected function buildHistoryGroupedByDate(\Illuminate\Support\Collection $rows): array
    {
        $grouped = $rows->groupBy(fn ($r) => Carbon::parse($r->updated_at)->format('Y-m-d'));

        $result = [];
        foreach ($grouped as $date => $items) {
            $dateLabel = $this->formatDateLabel($date);
            $entries = [];
            foreach ($items as $row) {
                $entries[] = $this->buildHistoryEntry($row);
            }
            $result[] = [
                'date' => $date,
                'date_label' => $dateLabel,
                'entries' => $entries,
            ];
        }

        return $result;
    }

    protected function formatDateLabel(string $date): string
    {
        $carbon = Carbon::parse($date);
        $today = Carbon::today()->format('Y-m-d');

        if ($date === $today) {
            return __('viewing_history_today') . ' - ' . $carbon->translatedFormat('d F Y');
        }

        return $carbon->translatedFormat('d F Y');
    }

    protected function buildHistoryEntry(WatchProgress $row): array
    {
        $isMovie = $row->item_type === 'movie';
        $isCompleted = $row->progress_percentage >= 95;

        try {
            $data = $this->tmdb->getItemSummary($row->item_id, $row->item_type);
            $title = $data['title'] ?? $data['name'] ?? '';
            if (! $isMovie && $row->season > 0 && $row->episode > 0) {
                $title .= ' - S' . $row->season . 'E' . $row->episode;
            }
        } catch (\Throwable $e) {
            $title = $isMovie ? __('viewing_history_unknown_movie') : __('viewing_history_unknown_series');
        }

        $remainingSeconds = max(0, $row->duration - $row->progress);
        $remainingFormatted = $this->formatDuration($remainingSeconds);

        $detailRoute = $isMovie ? 'movie.show' : 'tv.show';
        $detailUrl = route($detailRoute, ['id' => $row->item_id]);

        $statusLabel = $isCompleted
            ? ($isMovie ? __('viewing_history_movie_finished') : __('viewing_history_episode_finished'))
            : __('viewing_history_remaining_to_watch', ['time' => $remainingFormatted]);

        return [
            'id' => $row->id,
            'item_id' => $row->item_id,
            'item_type' => $row->item_type,
            'season' => $row->season,
            'episode' => $row->episode,
            'title' => $title,
            'is_completed' => $isCompleted,
            'status_label' => $statusLabel,
            'detail_url' => $detailUrl,
            'updated_at' => $row->updated_at->toIso8601String(),
        ];
    }
}
