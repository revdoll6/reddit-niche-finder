<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetitorActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'subreddit_id',
        'competitor_name',
        'post_title',
        'post_content',
        'post_url',
        'upvotes',
        'comment_count',
        'sentiment_score',
        'posted_at',
    ];

    protected $casts = [
        'posted_at' => 'datetime',
        'sentiment_score' => 'float',
    ];

    public function subreddit()
    {
        return $this->belongsTo(Subreddit::class);
    }
} 