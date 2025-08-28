<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Audience extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'description'
    ];

    /**
     * Get the user that owns the audience.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subreddits in this audience.
     */
    public function subreddits(): HasMany
    {
        return $this->hasMany(AudienceSubreddit::class);
    }
}
