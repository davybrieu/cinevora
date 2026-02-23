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
        return $this->progress / $this->duration * 100;
    }
}
