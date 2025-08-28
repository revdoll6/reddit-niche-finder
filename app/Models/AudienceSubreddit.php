<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AudienceSubreddit extends Model
{
    protected $fillable = [
        'audience_id',
        'subreddit_name',
        'subreddit_data'
    ];

    protected $casts = [
        'subreddit_data' => 'array'
    ];

    /**
     * Get the audience that owns the subreddit.
     */
    public function audience(): BelongsTo
    {
        return $this->belongsTo(Audience::class);
    }
}
