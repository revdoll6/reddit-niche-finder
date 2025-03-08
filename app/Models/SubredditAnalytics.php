<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubredditAnalytics extends Model
{
    use HasFactory;

    protected $fillable = [
        'subreddit_id',
        'date',
        'post_count',
        'comment_count',
        'average_upvotes',
        'average_comments',
        'engagement_rate',
        'sentiment_score',
    ];

    protected $casts = [
        'date' => 'date',
        'engagement_rate' => 'float',
        'sentiment_score' => 'float',
    ];

    public function subreddit()
    {
        return $this->belongsTo(Subreddit::class);
    }
} 