<?php

namespace App\Http\Controllers;

use App\Models\Subreddit;
use App\Services\RedditApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class RedditController extends Controller
{
    /**
     * The Reddit API service instance.
     *
     * @var \App\Services\RedditApiService
     */
    protected $redditService;

    /**
     * Create a new controller instance.
     *
     * @param  \App\Services\RedditApiService  $redditService
     * @return void
     */
    public function __construct(RedditApiService $redditService)
    {
        $this->redditService = $redditService;
        
        // Set the authenticated user if available
        if (Auth::check()) {
            $user = Auth::user();
            $this->redditService = new RedditApiService($user);
        }
    }

    /**
     * Search for subreddits.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchSubreddits(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:255'
        ]);

        // Get the query parameter
        $query = $request->input('query');

        // Perform the actual search
        try {
            // Get the authenticated user
            $user = Auth::user();
            
            if (!$user || !$user->redditCredentials) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Reddit API credentials not set'
                ], 400);
            }

            // Clean and validate the search query
            $query = trim($request->input('query'));
            if (strlen($query) < 2) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Search query must be at least 2 characters long'
                ], 400);
            }

            // Generate session key for this search
            $sessionKey = 'search_' . md5($query);
            
            // Check if we have cached results in the session
            $sessionData = $request->session()->get($sessionKey);
            if ($sessionData) {
                $timestamp = $sessionData['timestamp'];
                $currentTime = time();
                $hourInSeconds = 60 * 60;
                
                // If the results are less than an hour old, return them
                if (is_numeric($timestamp) && ($currentTime - $timestamp) <= $hourInSeconds) {
                    \Log::info("Returning session results for query: {$query}");
                    return response()->json([
                        'status' => 'success',
                        'source' => 'session',
                        'data' => $sessionData
                    ]);
                } else {
                    // Results are stale, remove from session
                    $request->session()->forget($sessionKey);
                }
            }

            // Set timeout for this request
            $this->redditService->setTimeout(10); // Reduced timeout
            
            // Cache key based on search parameters
            $cacheKey = 'subreddit_search_' . md5($query . $request->input('limit', 25));
            
            // Try to get results from cache first
            if (Cache::has($cacheKey)) {
                \Log::info("Returning cached results for query: {$query}");
                $cachedResults = Cache::get($cacheKey);
                
                // Store in session as well
                $sessionData = [
                    'query' => $query,
                    'results' => $cachedResults,
                    'total_results' => count($cachedResults),
                    'timestamp' => time()
                ];
                $request->session()->put($sessionKey, $sessionData);
                
                return response()->json([
                    'status' => 'success',
                    'source' => 'cache',
                    'data' => $sessionData
                ]);
            }

            \Log::info("Starting new search for query: {$query}");
            
            // Authenticate and perform search
            $this->redditService->authenticate();
            $searchData = $this->redditService->searchSubreddits($query, $request->input('limit', 25));
            
            if (empty($searchData['data']['children'])) {
                return response()->json([
                    'status' => 'success',
                    'source' => 'api',
                    'data' => [
                        'query' => $query,
                        'results' => [],
                        'total_results' => 0,
                        'timestamp' => time()
                    ]
                ]);
            }

            // Process results
            $processedResults = $this->processSearchResults($searchData['data']['children']);
            
            // Cache successful results
            Cache::put($cacheKey, $processedResults, now()->addMinutes(15));
            
            // Store in session
            $sessionData = [
                'query' => $query,
                'results' => $processedResults,
                'total_results' => count($processedResults),
                'timestamp' => time()
            ];
            $request->session()->put($sessionKey, $sessionData);

            \Log::info("Search completed successfully for query: {$query}");

            return response()->json([
                'status' => 'success',
                'source' => 'api',
                'data' => $sessionData
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Search error for query '{$request->input('query')}': " . $e->getMessage());
            
            $message = $e->getMessage();
            $statusCode = 500;
            
            // Handle specific error cases
            if (strpos($message, 'timed out') !== false) {
                $message = 'Search request timed out. Please try again.';
                $statusCode = 504;
            } else if (strpos($message, 'API credentials') !== false) {
                $message = 'Reddit API credentials are not properly configured.';
                $statusCode = 400;
            } else if (strpos($message, '429 Too Many Requests') !== false) {
                $message = 'Rate limit exceeded. Please try again in a few minutes.';
                $statusCode = 429;
            }
            
            return response()->json([
                'status' => 'error',
                'message' => $message,
                'error' => true
            ], $statusCode);
        }
    }

    /**
     * Process search results
     */
    private function processSearchResults($results)
    {
        $processed = [];
        $seen = [];

        foreach ($results as $result) {
            if (!isset($result['data']['display_name'])) continue;

            $name = $result['data']['display_name'];
            if (isset($seen[$name])) continue;

            $seen[$name] = true;
            
            // Extract only necessary data
            $data = $result['data'];
            
            // Check if calculated_metrics exists, if not create default values
            $calculatedMetrics = $data['calculated_metrics'] ?? [];
            
            // Calculate metrics if they don't exist
            $subscribers = $data['subscribers'] ?? 0;
            $activeUsers = $data['active_user_count'] ?? 0;
            $createdUtc = $data['created_utc'] ?? 0;
            
            // If metrics are missing, calculate basic estimates
            if (empty($calculatedMetrics['posts_per_day'])) {
                $postsPerDay = $this->estimatePostsPerDay($subscribers, $activeUsers);
                $calculatedMetrics['posts_per_day'] = $postsPerDay;
            }
            
            if (empty($calculatedMetrics['engagement_rate']) && $subscribers > 0) {
                $engagementRate = round(($activeUsers / $subscribers) * 100, 2);
                $calculatedMetrics['engagement_rate'] = $engagementRate;
            }
            
            if (empty($calculatedMetrics['growth_rate']) && $createdUtc > 0) {
                $growthRate = $this->estimateGrowthRate($subscribers, $createdUtc);
                $calculatedMetrics['growth_rate'] = $growthRate;
            }
            
            if (empty($calculatedMetrics['opportunity_score'])) {
                $engagementRate = $calculatedMetrics['engagement_rate'] ?? 0;
                $growthRate = $calculatedMetrics['growth_rate'] ?? 0;
                $postsPerDay = $calculatedMetrics['posts_per_day'] ?? 0;
                
                $opportunityScore = $this->calculateOpportunityScore($subscribers, $engagementRate, $growthRate, $postsPerDay);
                $calculatedMetrics['opportunity_score'] = $opportunityScore;
            }
            
            if (empty($calculatedMetrics['active_post_engagement'])) {
                $postsPerDay = $calculatedMetrics['posts_per_day'] ?? 0;
                if ($postsPerDay > 0) {
                    $activePostEngagement = $activeUsers * 0.2 / $postsPerDay;
                    $calculatedMetrics['active_post_engagement'] = round($activePostEngagement, 2);
                }
            }
            
            if (empty($calculatedMetrics['keyword_engagement'])) {
                // This depends on the search query, which we don't have access to here
                // So we'll need to set a baseline
                $calculatedMetrics['keyword_engagement'] = round($activeUsers * 0.05, 2);
            }
            
            // Log the calculated metrics for the first few results (for debugging)
            if (count($processed) < 2) {
                \Log::debug("Subreddit search metrics for r/{$name}:", [
                    'posts_per_day' => $calculatedMetrics['posts_per_day'] ?? 'not set',
                    'engagement_rate' => $calculatedMetrics['engagement_rate'] ?? 'not set',
                    'growth_rate' => $calculatedMetrics['growth_rate'] ?? 'not set',
                    'opportunity_score' => $calculatedMetrics['opportunity_score'] ?? 'not set',
                    'active_post_engagement' => $calculatedMetrics['active_post_engagement'] ?? 'not set',
                    'keyword_engagement' => $calculatedMetrics['keyword_engagement'] ?? 'not set',
                ]);
            }
            
            $processedResult = [
                'id' => $data['id'] ?? '',
                'name' => $data['name'] ?? '',
                'display_name' => $name,
                'title' => $data['title'] ?? '',
                'public_description' => $data['public_description'] ?? '',
                'subscribers' => $subscribers,
                'active_user_count' => $activeUsers,
                'created_utc' => $createdUtc,
                
                // Add metrics both directly and in calculated_metrics
                'posts_per_day' => $calculatedMetrics['posts_per_day'] ?? 0,
                'engagement_rate' => $calculatedMetrics['engagement_rate'] ?? 0,
                'growth_rate' => $calculatedMetrics['growth_rate'] ?? 0,
                'opportunity_score' => $calculatedMetrics['opportunity_score'] ?? 0,
                'content_types' => $calculatedMetrics['content_types'] ?? [],
                'topics' => $calculatedMetrics['topics'] ?? [],
                'moderation_level' => $calculatedMetrics['moderation_level'] ?? 'unknown',
                'engagement_per_post' => $calculatedMetrics['engagement_per_post'] ?? 0,
                'active_post_engagement' => $calculatedMetrics['active_post_engagement'] ?? 0,
                'keyword_engagement' => $calculatedMetrics['keyword_engagement'] ?? 0,
                
                // Store the calculated_metrics object directly as well
                'calculated_metrics' => $calculatedMetrics
            ];
            
            $processed[] = $processedResult;
        }

        return $processed;
    }
    
    /**
     * Estimate posts per day based on subscribers and active users
     */
    private function estimatePostsPerDay($subscribers, $activeUsers)
    {
        // Simple estimation based on active users and subscriber count
        $baseRate = min($activeUsers * 0.1, 100); // Assume 10% of active users post
        $subscriberFactor = $subscribers > 0 ? log10($subscribers) : 1;
        
        return round(max($baseRate * ($subscriberFactor / 5), 1), 1);
    }
    
    /**
     * Estimate growth rate based on subscribers and creation date
     */
    private function estimateGrowthRate($subscribers, $createdUtc)
    {
        $ageInDays = (time() - $createdUtc) / 86400;
        
        if ($ageInDays < 1 || $subscribers < 1) return 0;
        
        // Estimate daily growth based on age and current size
        $estimatedDailyGrowth = $subscribers / max($ageInDays, 1);
        return round(($estimatedDailyGrowth / $subscribers) * 100, 2);
    }
    
    /**
     * Calculate opportunity score from metrics
     */
    private function calculateOpportunityScore($subscribers, $engagementRate, $growthRate, $postsPerDay)
    {
        // Simple weighted algorithm for opportunity score
        $subscriberScore = min(log10($subscribers) * 10, 100);
        $engagementScore = min($engagementRate * 2, 100);
        $growthScore = min($growthRate, 100);
        $activityScore = min($postsPerDay * 5, 100);
        
        // Weighted average of scores
        $opportunityScore = ($subscriberScore * 0.2) + 
                            ($engagementScore * 0.3) + 
                            ($growthScore * 0.3) + 
                            ($activityScore * 0.2);
                            
        return round($opportunityScore, 1);
    }

    /**
     * Get information about a subreddit.
     *
     * @param  string  $name
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubredditInfo($name)
    {
        // Cache the results for 1 hour
        $cacheKey = 'subreddit_info_' . $name;
        
        $info = Cache::remember($cacheKey, 3600, function () use ($name) {
            try {
                return $this->redditService->getSubredditInfo($name);
            } catch (\Exception $e) {
                return ['error' => $e->getMessage()];
            }
        });
        
        if (isset($info['error'])) {
            return response()->json(['error' => $info['error']], 500);
        }
        
        return response()->json($info);
    }

    /**
     * Get posts from a subreddit.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $name
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubredditPosts(Request $request, $name)
    {
        $validator = Validator::make($request->all(), [
            'sort' => 'string|in:hot,new,top,rising',
            'limit' => 'integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $sort = $request->input('sort', 'hot');
        $limit = $request->input('limit', 25);
        
        // Cache the results for 15 minutes
        $cacheKey = 'subreddit_posts_' . $name . '_' . $sort . '_' . $limit;
        
        $posts = Cache::remember($cacheKey, 900, function () use ($name, $sort, $limit) {
            try {
                return $this->redditService->getSubredditPosts($name, $sort, $limit);
            } catch (\Exception $e) {
                return ['error' => $e->getMessage()];
            }
        });
        
        if (isset($posts['error'])) {
            return response()->json(['error' => $posts['error']], 500);
        }
        
        return response()->json($posts);
    }

    /**
     * Track a subreddit for the authenticated user.
     *
     * @param  string  $name
     * @return \Illuminate\Http\JsonResponse
     */
    public function trackSubreddit($name)
    {
        $user = Auth::user();
        
        // Get subreddit info from Reddit API
        try {
            $subredditInfo = $this->redditService->getSubredditInfo($name);
            
            if (!isset($subredditInfo['data'])) {
                return response()->json(['error' => 'Subreddit not found'], 404);
            }
            
            // Find or create the subreddit in our database
            $subreddit = Subreddit::firstOrCreate(
                ['name' => $name],
                [
                    'display_name' => $subredditInfo['data']['display_name'],
                    'title' => $subredditInfo['data']['title'],
                    'description' => $subredditInfo['data']['public_description'],
                    'subscribers' => $subredditInfo['data']['subscribers'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            
            // Check if the user is already tracking this subreddit
            if ($user->trackedSubreddits()->where('subreddit_id', $subreddit->id)->exists()) {
                return response()->json(['message' => 'You are already tracking this subreddit']);
            }
            
            // Add the subreddit to the user's tracked subreddits
            $user->trackedSubreddits()->attach($subreddit->id);
            
            return response()->json([
                'message' => 'Subreddit tracked successfully',
                'subreddit' => $subreddit,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Untrack a subreddit for the authenticated user.
     *
     * @param  string  $name
     * @return \Illuminate\Http\JsonResponse
     */
    public function untrackSubreddit($name)
    {
        $user = Auth::user();
        
        // Find the subreddit in our database
        $subreddit = Subreddit::where('name', $name)->first();
        
        if (!$subreddit) {
            return response()->json(['error' => 'Subreddit not found'], 404);
        }
        
        // Check if the user is tracking this subreddit
        if (!$user->trackedSubreddits()->where('subreddit_id', $subreddit->id)->exists()) {
            return response()->json(['message' => 'You are not tracking this subreddit']);
        }
        
        // Remove the subreddit from the user's tracked subreddits
        $user->trackedSubreddits()->detach($subreddit->id);
        
        return response()->json([
            'message' => 'Subreddit untracked successfully',
        ]);
    }

    /**
     * Get all tracked subreddits for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTrackedSubreddits()
    {
        $user = auth()->user();
        $trackedSubreddits = $user->trackedSubreddits()->get();
        
        return response()->json($trackedSubreddits);
    }

    /**
     * Analyze a subreddit for metrics and opportunities.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $subredditName
     * @return \Illuminate\Http\JsonResponse
     */
    public function analyzeSubreddit(Request $request, $subredditName)
    {
        try {
            // Get the user associated with the API key
            $user = Auth::user();
            
            if (!$user) {
                return response()->json(['message' => 'Invalid API key'], 401);
            }
            
            // Get the user's Reddit credentials
            $redditCredentials = $user->redditCredentials;
            
            if (!$redditCredentials || !$redditCredentials->is_connected) {
                return response()->json(['message' => 'Reddit API not configured'], 400);
            }
            
            // Initialize Reddit service
            $redditService = new RedditApiService();
            $redditService->setCredentials($redditCredentials);
            
            // Get subreddit information
            $subredditInfo = $redditService->getSubredditInfo($subredditName);
            
            // Get recent posts
            $posts = $redditService->getSubredditPosts($subredditName, 'hot', 100);
            
            // Perform analysis (example)
            $analysis = [
                'subreddit_stats' => [
                    'name' => $subredditInfo['data']['display_name'],
                    'subscribers' => $subredditInfo['data']['subscribers'],
                    'active_users' => $subredditInfo['data']['active_user_count'],
                    'description' => $subredditInfo['data']['public_description'],
                ],
                'content_analysis' => [
                    'total_posts' => count($posts['data']['children']),
                    'average_score' => array_reduce($posts['data']['children'], function($carry, $post) {
                        return $carry + $post['data']['score'];
                    }, 0) / count($posts['data']['children']),
                    'post_types' => [
                        'text' => count(array_filter($posts['data']['children'], function($post) {
                            return $post['data']['is_self'];
                        })),
                        'links' => count(array_filter($posts['data']['children'], function($post) {
                            return !$post['data']['is_self'];
                        })),
                    ],
                ],
                'engagement_metrics' => [
                    'average_comments' => array_reduce($posts['data']['children'], function($carry, $post) {
                        return $carry + $post['data']['num_comments'];
                    }, 0) / count($posts['data']['children']),
                    'upvote_ratio' => array_reduce($posts['data']['children'], function($carry, $post) {
                        return $carry + $post['data']['upvote_ratio'];
                    }, 0) / count($posts['data']['children']),
                ],
                'best_posting_times' => $this->analyzeBestPostingTimes($posts['data']['children']),
            ];
            
            return response()->json([
                'success' => true,
                'data' => $analysis
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Analysis failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Analyze best posting times based on successful posts
     *
     * @param array $posts
     * @return array
     */
    private function analyzeBestPostingTimes($posts)
    {
        $hourlyScores = array_fill(0, 24, ['score' => 0, 'count' => 0]);
        
        foreach ($posts as $post) {
            $hour = date('G', $post['data']['created_utc']);
            $hourlyScores[$hour]['score'] += $post['data']['score'];
            $hourlyScores[$hour]['count']++;
        }
        
        // Calculate average score per hour
        $bestHours = [];
        foreach ($hourlyScores as $hour => $data) {
            if ($data['count'] > 0) {
                $bestHours[$hour] = $data['score'] / $data['count'];
            }
        }
        
        // Sort by average score
        arsort($bestHours);
        
        // Return top 5 hours
        return array_slice($bestHours, 0, 5, true);
    }

    /**
     * Get the current rate limit status.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRateLimitStatus()
    {
        try {
            $status = $this->redditService->getRateLimitStatus();
            
            return response()->json([
                'success' => true,
                'rate_limit' => $status
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to get rate limit status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get rate limit status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store search results in the session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeSearchInSession(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|max:255',
            'results' => 'required|array',
            'total_results' => 'required|integer',
            'timestamp' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Generate a session key based on the query
        $sessionKey = 'search_' . md5($request->input('query'));

        // Store search data in session
        $request->session()->put($sessionKey, [
            'query' => $request->input('query'),
            'results' => $request->input('results'),
            'total_results' => $request->input('total_results'),
            'timestamp' => $request->input('timestamp')
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Search results stored in session'
        ]);
    }

    /**
     * Retrieve search results from the session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSearchFromSession(Request $request)
    {
        // Validate query parameter
        if (!$request->has('query')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Query parameter is required'
            ], 422);
        }

        try {
            // Generate a session key based on the query
            $sessionKey = 'search_' . md5($request->query('query'));

            // Get data from session
            $searchData = $request->session()->get($sessionKey);

        if (!$searchData) {
            return response()->json([
                'status' => 'error',
                    'message' => 'No search results found in session'
                ], 404);
            }

            // Validate the required structure of the session data
            if (!isset($searchData['query']) || !isset($searchData['results']) || !isset($searchData['timestamp'])) {
                \Log::warning("Invalid search data structure in session for query: {$request->query('query')}");
                $request->session()->forget($sessionKey);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid search data structure in session'
            ], 404);
        }

            // Check if results are still fresh (less than 1 hour old)
            $timestamp = $searchData['timestamp'];
            $currentTime = time();
            $hourInSeconds = 60 * 60;

            if (!is_numeric($timestamp) || ($currentTime - $timestamp) > $hourInSeconds) {
                // Results are stale, remove from session
                \Log::info("Removing stale search results for query: {$request->query('query')}");
                $request->session()->forget($sessionKey);
                
            return response()->json([
                'status' => 'error',
                    'message' => 'Search results have expired'
            ], 404);
        }

            // Ensure results are a valid array
            if (!is_array($searchData['results'])) {
                \Log::warning("Invalid results array in session for query: {$request->query('query')}");
                $searchData['results'] = [];
            }

            // Return the session data
        return response()->json([
            'status' => 'success',
                'source' => 'session',
            'data' => $searchData
        ]);
        } catch (\Exception $e) {
            \Log::error("Error retrieving search from session: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve search data from session'
            ], 500);
        }
    }
} 