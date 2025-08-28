<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class RedditDebugService
{
    /**
     * Log Reddit API response data
     *
     * @param string $endpoint
     * @param array $response
     * @return void
     */
    public static function logApiResponse($endpoint, $response)
    {
        $logData = [
            'timestamp' => now()->toIso8601String(),
            'endpoint' => $endpoint,
            'response' => $response
        ];

        // Log to file
        $logPath = storage_path('logs/reddit_api_debug.json');
        $existingLogs = file_exists($logPath) ? json_decode(file_get_contents($logPath), true) : [];
        $existingLogs[] = $logData;
        file_put_contents($logPath, json_encode($existingLogs, JSON_PRETTY_PRINT));

        // Also log to Laravel log
        Log::info('Reddit API Debug', $logData);
    }

    /**
     * Get debug data for a subreddit
     *
     * @param array $subredditData
     * @return array
     */
    public static function getSubredditDebugInfo($subredditData)
    {
        return [
            'basic_info' => [
                'name' => $subredditData['display_name'] ?? 'N/A',
                'title' => $subredditData['title'] ?? 'N/A',
                'description' => $subredditData['public_description'] ?? 'N/A',
                'created_utc' => $subredditData['created_utc'] ?? 'N/A',
            ],
            'metrics' => [
                'subscribers' => $subredditData['subscribers'] ?? 0,
                'active_users' => $subredditData['active_user_count'] ?? 0,
            ],
            'settings' => [
                'over18' => $subredditData['over18'] ?? false,
                'subreddit_type' => $subredditData['subreddit_type'] ?? 'N/A',
                'submission_type' => $subredditData['submission_type'] ?? 'N/A',
            ],
            'raw_data' => $subredditData
        ];
    }
} 