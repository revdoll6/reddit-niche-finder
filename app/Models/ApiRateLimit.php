<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiRateLimit extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'provider',
        'requests_per_minute',
        'concurrent_requests',
        'retry_failed_requests',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'requests_per_minute' => 'integer',
        'concurrent_requests' => 'integer',
        'retry_failed_requests' => 'boolean',
    ];

    /**
     * Get the user that owns the API rate limit.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 