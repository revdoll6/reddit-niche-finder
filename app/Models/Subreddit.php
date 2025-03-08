<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subreddit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'members',
        'engagement_rate',
        'sentiment_score',
        'last_crawled',
    ];

    protected $casts = [
        'last_crawled' => 'datetime',
        'engagement_rate' => 'float',
        'sentiment_score' => 'float',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_subreddit_tracking')
            ->withPivot('is_favorite')
            ->withTimestamps();
    }

    public function analytics()
    {
        return $this->hasMany(SubredditAnalytics::class);
    }

    public function competitorActivities()
    {
        return $this->hasMany(CompetitorActivity::class);
    }
} 