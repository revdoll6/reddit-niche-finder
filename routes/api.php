<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ApiSettingsController;
use App\Http\Controllers\RedditController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AudienceController;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Debug route to check authentication
Route::get('/check-auth', function (Request $request) {
    return response()->json([
        'authenticated' => auth()->check(),
        'user' => auth()->check() ? auth()->user() : null,
    ]);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // API Settings
    Route::prefix('settings')->group(function () {
        Route::get('/api', [ApiSettingsController::class, 'index']);
        Route::post('/api', [ApiSettingsController::class, 'store']);
        Route::post('/generate-key', [ApiSettingsController::class, 'generateKey']);
        Route::get('/key/{id}', [ApiSettingsController::class, 'getKey']);
        Route::delete('/revoke-key/{id}', [ApiSettingsController::class, 'revokeKey']);
    });
    
    // Test connection endpoint - moved inside auth middleware
    Route::post('/settings/test-connection', [ApiSettingsController::class, 'testConnection']);
    
    // Reddit API
    Route::prefix('reddit')->group(function () {
        // Subreddit Discovery
        Route::get('/subreddits/search', [RedditController::class, 'searchSubreddits']);
        Route::post('/subreddits/search/store', [RedditController::class, 'storeSearchInSession']);
        Route::get('/subreddits/search/retrieve', [RedditController::class, 'getSearchFromSession']);
        Route::get('/subreddits/{name}', [RedditController::class, 'getSubredditInfo']);
        Route::get('/subreddits/{name}/posts', [RedditController::class, 'getSubredditPosts']);
        Route::get('/subreddits/{name}/analyze', [RedditController::class, 'analyzeSubreddit']);
        
        // Tracking
        Route::post('/subreddits/{name}/track', [RedditController::class, 'trackSubreddit']);
        Route::delete('/subreddits/{name}/untrack', [RedditController::class, 'untrackSubreddit']);
        Route::get('/tracked-subreddits', [RedditController::class, 'getTrackedSubreddits']);
        
        // Rate Limiting
        Route::get('/rate-limit', [RedditController::class, 'getRateLimitStatus']);
    });

    // Profile
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/profile/update-name', [ProfileController::class, 'updateName']);
        Route::post('/profile/update', [ProfileController::class, 'updateProfile']);
        Route::post('/profile/update-password', [ProfileController::class, 'updatePassword']);
    });

    // Audiences
    Route::prefix('audiences')->group(function () {
        Route::get('/', [AudienceController::class, 'index']);
        Route::post('/', [AudienceController::class, 'store']);
        Route::get('/{audience}', [AudienceController::class, 'show']);
        Route::get('/{audience}/fetch-status', [AudienceController::class, 'fetchStatus']);
        Route::get('/{audience}/posts', [AudienceController::class, 'getPosts']);
        Route::delete('/{audience}', [AudienceController::class, 'destroy']);
    });
});

// Add search logging endpoint
Route::post('/log/search', function (Request $request) {
    try {
        $logEntry = $request->all();
        Log::debug('Search log received input', [
            'input_keys' => array_keys($logEntry),
            'content_type' => $request->header('Content-Type'),
            'content_length' => $request->header('Content-Length')
        ]);
        
        // Enhanced validation with detailed errors
        $errors = [];
        
        if (!isset($logEntry['query']) || empty($logEntry['query'])) {
            $errors[] = 'Missing or empty query field';
        } elseif (!is_string($logEntry['query'])) {
            $errors[] = 'Query must be a string, got: ' . gettype($logEntry['query']);
        }
        
        if (!isset($logEntry['results'])) {
            $errors[] = 'Missing results field';
        } elseif (!is_array($logEntry['results'])) {
            $errors[] = 'Results must be an array, got: ' . gettype($logEntry['results']);
        } elseif (empty($logEntry['results'])) {
            $errors[] = 'Results array is empty';
        }
        
        if (!empty($errors)) {
            Log::warning('Search log validation failed', ['errors' => $errors]);
            return response()->json([
                'status' => 'error',
                'message' => 'Missing required data for search logging',
                'details' => $errors
            ], 400);
        }
        
        // Add user info if authenticated
        $user = $request->user();
        $logEntry['user_id'] = $user ? $user->id : null;
        
        // Save log to database or file
        Log::channel('search')->info('Search Log', $logEntry);
        
        return response()->json(['status' => 'success']);
    } catch (Exception $e) {
        Log::error('Search logging error: ' . $e->getMessage(), [
            'exception' => $e,
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to log search data: ' . $e->getMessage()
        ], 500);
    }
});

// Add debug endpoint for troubleshooting search logging issues
Route::post('/debug/log-search', function (Request $request) {
    $input = $request->all();
    
    // Log the entire input for debugging
    Log::debug('Debug log-search received input', [
        'input' => $input,
        'content_type' => $request->header('Content-Type'),
        'content_length' => $request->header('Content-Length'),
        'accept' => $request->header('Accept'),
        'user_agent' => $request->header('User-Agent')
    ]);
    
    // Check for required fields
    $hasQuery = isset($input['query']);
    $hasResults = isset($input['results']);
    $resultsIsArray = $hasResults && is_array($input['results']);
    $resultsCount = $resultsIsArray ? count($input['results']) : 0;
    
    return response()->json([
        'status' => 'debug',
        'received_data' => [
            'has_query' => $hasQuery,
            'query_type' => $hasQuery ? gettype($input['query']) : null,
            'query_value' => $hasQuery ? $input['query'] : null,
            'has_results' => $hasResults,
            'results_is_array' => $resultsIsArray,
            'results_count' => $resultsCount,
            'payload_size' => strlen(json_encode($input)) . ' bytes',
            'timestamp_present' => isset($input['timestamp']),
            'total_top_level_keys' => count(array_keys($input)),
            'top_level_keys' => array_keys($input),
        ],
        'raw_first_result' => ($resultsCount > 0) ? $input['results'][0] : null,
    ]);
});

// Add opportunity metrics logging endpoint
Route::post('/log/opportunity-metrics', function (Request $request) {
    try {
        $logEntry = $request->all();
        
        // Log the input for debugging
        Log::debug('Opportunity metrics log received input', [
            'input_keys' => array_keys($logEntry),
            'subreddit' => $logEntry['subreddit_name'] ?? 'unknown'
        ]);
        
        // Validate minimum required fields
        if (!isset($logEntry['subreddit_name'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Missing required field: subreddit_name'
            ], 400);
        }
        
        // Ensure all expected fields exist with defaults
        $logEntry = array_merge([
            'normalized_score' => 0,
            'raw_score' => 0,
            'engagement_per_post' => 0,
            'keyword_engagement' => 0,
            'posts_per_day' => 0,
            'growth_rate' => 0,
            'subscriber_count' => 0,
            'timestamp' => now()->toIso8601String(),
            'normalized_metrics' => [
                'engagement' => 0,
                'keyword' => 0,
                'content' => 0,
                'base' => 0,
                'activity' => 0,
                'growth' => 0,
                'size' => 0
            ]
        ], $logEntry);
        
        // Ensure normalized_metrics is an array
        if (!isset($logEntry['normalized_metrics']) || !is_array($logEntry['normalized_metrics'])) {
            $logEntry['normalized_metrics'] = [
                'engagement' => 0,
                'keyword' => 0,
                'content' => 0,
                'base' => 0,
                'activity' => 0,
                'growth' => 0,
                'size' => 0
            ];
        }
        
        // Make sure all normalized metrics exist
        $logEntry['normalized_metrics'] = array_merge([
            'engagement' => 0,
            'keyword' => 0,
            'content' => 0,
            'base' => 0,
            'activity' => 0,
            'growth' => 0,
            'size' => 0
        ], $logEntry['normalized_metrics']);
        
        // Format the log entry
        $formattedLog = sprintf(
            "\n[%s] Opportunity Score Metrics for r/%s\n" .
            "----------------------------------------\n" .
            "Final Score: %.2f (Raw Score: %.2f)\n\n" .
            "Normalized Metrics (0-100):\n" .
            "- Engagement: %.2f\n" .
            "- Keyword Engagement: %.2f\n" .
            "- Content Relevancy: %.2f\n" .
            "- Base Relevancy: %.2f\n" .
            "- Activity (Posts/Day): %.2f\n" .
            "- Growth Rate: %.2f\n" .
            "- Size Impact: %.2f\n\n" .
            "Raw Metrics:\n" .
            "- Engagement Per Post: %.2f\n" .
            "- Keyword Engagement: %.2f\n" .
            "- Posts Per Day: %.2f\n" .
            "- Growth Rate: %.2f\n" .
            "- Subscriber Count: %d\n" .
            "----------------------------------------\n",
            $logEntry['timestamp'],
            $logEntry['subreddit_name'],
            $logEntry['normalized_score'],
            $logEntry['raw_score'],
            $logEntry['normalized_metrics']['engagement'],
            $logEntry['normalized_metrics']['keyword'],
            $logEntry['normalized_metrics']['content'],
            $logEntry['normalized_metrics']['base'],
            $logEntry['normalized_metrics']['activity'],
            $logEntry['normalized_metrics']['growth'],
            $logEntry['normalized_metrics']['size'],
            $logEntry['engagement_per_post'],
            $logEntry['keyword_engagement'],
            $logEntry['posts_per_day'],
            $logEntry['growth_rate'],
            $logEntry['subscriber_count']
        );
        
        // Save to log file
        $logFile = storage_path('logs/opportunity_metrics.log');
        
        // Ensure the storage directory exists
        if (!file_exists(dirname($logFile))) {
            mkdir(dirname($logFile), 0777, true);
        }
        
        // Append to log file
        file_put_contents($logFile, $formattedLog, FILE_APPEND);
        
        // Also log to the opportunity metrics channel
        Log::channel('opportunity')->info('Opportunity Metrics', $logEntry);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Opportunity metrics logged successfully'
        ]);
        
    } catch (Exception $e) {
        Log::error('Opportunity metrics logging error: ' . $e->getMessage(), [
            'exception' => $e,
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to log opportunity metrics: ' . $e->getMessage()
        ], 500);
    }
});

// Helper function to format all posts data
function formatAllPostsData($posts) {
    if (empty($posts) || !is_array($posts)) {
        return "No posts available for analysis\n";
    }
    
    $output = "";
    foreach ($posts as $index => $post) {
        if (!is_array($post)) {
            $output .= "Invalid post at index $index\n";
            continue;
        }
        
        $output .= sprintf(
            "Post #%d:\n" .
            "Title: %s\n" .
            "Created: %s\n" .
            "Score: %d upvotes\n" .
            "Comments: %d\n" .
            "Has Engagement: %s\n" .
            "----------------------------------------\n",
            $index + 1,
            $post['title'] ?? 'N/A',
            isset($post['created_utc']) && is_numeric($post['created_utc']) ? date('Y-m-d H:i:s', $post['created_utc']) : 'N/A',
            intval($post['score'] ?? 0),
            intval($post['num_comments'] ?? 0),
            isset($post['has_engagement']) && $post['has_engagement'] ? 'Yes' : 'No'
        );
    }
    
    return $output;
}

// Helper function to format detailed post analysis
function formatDetailedPostAnalysis($posts, $postScores) {
    if (empty($posts) || !is_array($posts)) {
        return "No posts available for analysis\n";
    }
    
    $output = "";
    foreach ($posts as $index => $post) {
        if (!is_array($post)) {
            $output .= "Invalid post at index $index\n";
            continue;
        }
        
        $score = isset($postScores[$index]) && is_array($postScores[$index]) ? $postScores[$index] : [];
        
        $output .= sprintf(
            "Post #%d:\n" .
            "Title: %s\n" .
            "Content: %s\n" .
            "Created: %s\n" .
            "Score: %d upvotes\n" .
            "Comments: %d\n" .
            "Relevancy Score: %.2f%%\n" .
            "Term Matches:\n%s\n" .
            "----------------------------------------\n\n",
            $index + 1,
            $post['title'] ?? 'N/A',
            $post['selftext'] ?? '[No text content]',
            isset($post['created_utc']) && is_numeric($post['created_utc']) ? date('Y-m-d H:i:s', $post['created_utc']) : 'N/A',
            intval($post['score'] ?? 0),
            intval($post['num_comments'] ?? 0),
            floatval($score['score'] ?? 0),
            formatTermMatchesDetailed(isset($score['term_matches']) && is_array($score['term_matches']) ? $score['term_matches'] : [])
        );
    }
    
    return $output;
}

// Helper function to format match details
function formatMatchDetails($matches) {
    if (empty($matches) || !is_array($matches)) {
        return "No matches found";
    }
    
    $output = [];
    foreach ($matches as $match) {
        if (!is_array($match)) {
            continue;
        }
        $output[] = sprintf("'%s': %d matches", 
            isset($match['term']) ? $match['term'] : 'unknown', 
            isset($match['matches']) && is_numeric($match['matches']) ? $match['matches'] : 0
        );
    }
    
    return empty($output) ? "No valid matches found" : implode(", ", $output);
}

// Helper function to format term matches in detail
function formatTermMatchesDetailed($matches) {
    if (empty($matches) || !is_array($matches)) {
        return "  No term matches found\n";
    }
    
    $output = "";
    foreach ($matches as $term => $count) {
        $output .= sprintf("  - '%s': %d occurrences\n", $term, intval($count));
    }
    
    return empty($output) ? "  No term matches found\n" : $output;
} 