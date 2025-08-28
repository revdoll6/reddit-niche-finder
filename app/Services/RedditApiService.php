<?php

namespace App\Services;

use App\Models\ApiCredential;
use App\Models\ApiRateLimit;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\ClientException;

class RedditApiService
{
    /**
     * The HTTP client instance.
     *
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * The authenticated user.
     *
     * @var \App\Models\User|null
     */
    protected $user;

    /**
     * The API credentials.
     *
     * @var \App\Models\ApiCredential|null
     */
    protected $credentials;

    /**
     * The API rate limits.
     *
     * @var \App\Models\ApiRateLimit|null
     */
    protected $rateLimits;

    /**
     * The access token.
     *
     * @var string|null
     */
    protected $accessToken;

    /**
     * The token expiration time.
     *
     * @var int|null
     */
    protected $tokenExpires;

    /**
     * The request timeout in seconds.
     *
     * @var int
     */
    protected $timeout = 30; // Default timeout in seconds

    /**
     * Tracks API requests for rate limiting
     * 
     * @var array
     */
    protected $requestLog = [];
    
    /**
     * Maximum requests allowed per minute (Reddit's limit)
     * 
     * @var int
     */
    protected $maxRequestsPerMinute = 60;
    
    /**
     * Whether to enforce rate limiting
     * 
     * @var bool
     */
    protected $enforceRateLimit = true;

    /**
     * Create a new Reddit API service instance.
     *
     * @param \App\Models\User|null $user
     * @return void
     */
    public function __construct(User $user = null)
    {
        $this->client = new Client([
            'base_uri' => 'https://oauth.reddit.com/',
            'timeout' => 10.0,
        ]);

        // Set the user property
        $this->user = $user;

        if ($user) {
            $this->credentials = $user->redditCredentials;
            $this->rateLimits = $user->redditRateLimits;
        }

        // Load request log from cache
        $this->loadRequestLog();
    }

    /**
     * Set the API credentials.
     *
     * @param \App\Models\ApiCredential $credentials
     * @return $this
     */
    public function setCredentials(ApiCredential $credentials)
    {
        $this->credentials = $credentials;
        return $this;
    }

    /**
     * Set the API rate limits.
     *
     * @param \App\Models\ApiRateLimit $rateLimits
     * @return $this
     */
    public function setRateLimits(ApiRateLimit $rateLimits)
    {
        $this->maxRequestsPerMinute = $rateLimits->requests_per_minute;
        $this->enforceRateLimit = true;
        return $this;
    }

    /**
     * Set request timeout in seconds
     *
     * @param int $seconds
     * @return void
     */
    public function setTimeout($seconds)
    {
        $this->timeout = max(1, min($seconds, 60)); // Limit between 1 and 60 seconds
    }

    /**
     * Get current timeout setting
     *
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Test the Reddit API connection.
     *
     * @return bool
     * @throws \Exception
     */
    public function testConnection()
    {
        // Check rate limits before testing connection
        if (!$this->isWithinRateLimit()) {
            $status = $this->getRateLimitStatus();
            $resetIn = $status['reset_in_seconds'];
            \Log::warning("Rate limit exceeded during connection test. Reset in {$resetIn} seconds.");
            throw new \Exception("Reddit API rate limit exceeded. Please try again in {$resetIn} seconds.");
        }
        
        // Log this request for rate limiting
        $this->logRequest();
        
        try {
            \Log::info('Testing Reddit API connection with credentials: ' . json_encode([
                'client_id' => $this->credentials->client_id,
                'username' => $this->credentials->username,
                'user_agent' => $this->credentials->user_agent
            ]));

            // Initialize curl for token request (similar to standalone script)
            $ch = curl_init();

            // Set curl options for token request
            curl_setopt_array($ch, [
                CURLOPT_URL => 'https://www.reddit.com/api/v1/access_token',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
                CURLOPT_SSL_VERIFYPEER => false,  // Disable SSL verification for testing
                CURLOPT_HTTPHEADER => [
                    'User-Agent: ' . $this->credentials->user_agent
                ],
                CURLOPT_USERPWD => $this->credentials->client_id . ':' . $this->credentials->client_secret
            ]);

            // Execute token request
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            // Check for curl errors
            if ($response === false) {
                $curlError = curl_error($ch);
                $curlErrno = curl_errno($ch);
                curl_close($ch);
                \Log::error("cURL Error ($curlErrno): $curlError");
                throw new \Exception("cURL Error ($curlErrno): $curlError");
            }
            
            \Log::info("Token Response (HTTP $httpCode): " . $response);
            
            // Parse response
            $data = json_decode($response, true);
            
            if (isset($data['access_token'])) {
                \Log::info("Access token received!");
                $accessToken = $data['access_token'];
                
                // Test API endpoint
                \Log::info("Testing API endpoint...");
                
                // Reset curl options for API request
                curl_setopt_array($ch, [
                    CURLOPT_URL => 'https://oauth.reddit.com/api/v1/me',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => false,
                    CURLOPT_SSL_VERIFYPEER => false,  // Disable SSL verification for testing
                    CURLOPT_HTTPHEADER => [
                        'Authorization: Bearer ' . $accessToken,
                        'User-Agent: ' . $this->credentials->user_agent
                    ]
                ]);
                
                // Execute API request
                $apiResponse = curl_exec($ch);
                $apiHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                
                // Check for curl errors in the second request
                if ($apiResponse === false) {
                    $curlError = curl_error($ch);
                    $curlErrno = curl_errno($ch);
                    curl_close($ch);
                    \Log::error("cURL Error ($curlErrno): $curlError");
                    throw new \Exception("cURL Error ($curlErrno): $curlError");
                }
                
                \Log::info("API Response (HTTP $apiHttpCode): " . $apiResponse);
                
                // Clean up
                curl_close($ch);
                
                if ($apiHttpCode === 200) {
                    \Log::info("API test successful!");
                    if ($this->credentials->exists) {
                        $this->credentials->update([
                            'is_connected' => true,
                            'last_connected_at' => now(),
                        ]);
                    }
                    return true;
                } else {
                    \Log::error("API test failed with HTTP code: $apiHttpCode");
                    throw new \Exception("API test failed with HTTP code: $apiHttpCode");
                }
            } else {
                // Clean up
                curl_close($ch);
                
                \Log::error('Failed to get access token!');
                if (isset($data['error'])) {
                    $errorMsg = "Error: " . $data['error'];
                    if (isset($data['error_description'])) {
                        $errorMsg .= " - " . $data['error_description'];
                    }
                    \Log::error($errorMsg);
                    throw new \Exception($errorMsg);
                } else {
                    throw new \Exception('Failed to get access token. No error information available.');
                }
            }
        } catch (\Exception $e) {
            \Log::error('Reddit API connection test failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Authenticate with the Reddit API.
     *
     * @return void
     * @throws \Exception
     */
    public function authenticate()
    {
        // Check rate limits before authentication
        if (!$this->isWithinRateLimit()) {
            $status = $this->getRateLimitStatus();
            $resetIn = $status['reset_in_seconds'];
            \Log::warning("Rate limit exceeded during authentication. Reset in {$resetIn} seconds.");
            throw new \Exception("Reddit API rate limit exceeded. Please try again in {$resetIn} seconds.");
        }
        
        // Log this request for rate limiting
        $this->logRequest();
        
        if (!$this->credentials) {
            throw new \Exception('API credentials not set');
        }

        // Check if we have a valid cached token
        $cacheKey = 'reddit_token_' . $this->credentials->user_id;
        if (Cache::has($cacheKey)) {
            $tokenData = Cache::get($cacheKey);
            $this->accessToken = $tokenData['token'];
            $this->tokenExpires = $tokenData['expires'];
            
            // If token is still valid, return it
            if ($this->tokenExpires > time()) {
                return;
            }
        }

        try {
            $authClient = new Client([
                'base_uri' => 'https://www.reddit.com/',
                'timeout' => 10.0,
                'verify' => false, // Disable SSL verification temporarily
                'headers' => [
                    'User-Agent' => $this->credentials->user_agent
                ]
            ]);

            // Use client_credentials flow
            $response = $authClient->post('api/v1/access_token', [
                'auth' => [
                    $this->credentials->client_id,
                    $this->credentials->client_secret
                ],
                'form_params' => [
                    'grant_type' => 'client_credentials'
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            
            if (isset($data['access_token'])) {
                $this->accessToken = $data['access_token'];
                $this->tokenExpires = time() + $data['expires_in'];
                
                // Cache the token
                Cache::put($cacheKey, [
                    'token' => $this->accessToken,
                    'expires' => $this->tokenExpires,
                ], $data['expires_in']);
                
                // Update the client with the new token
                $this->client = new Client([
                    'base_uri' => 'https://oauth.reddit.com/',
                    'timeout' => 10.0,
                    'verify' => false, // Disable SSL verification temporarily
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->accessToken,
                        'User-Agent' => $this->credentials->user_agent
                    ]
                ]);
                
                return;
            }
            
            throw new \Exception('Failed to get access token: ' . json_encode($data));
        } catch (GuzzleException $e) {
            Log::error('Reddit API authentication failed: ' . $e->getMessage());
            throw new \Exception('Reddit API authentication failed: ' . $e->getMessage());
        }
    }

    /**
     * Make a GET request to the Reddit API.
     *
     * @param string $endpoint
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function get($endpoint, array $params = [])
    {
        return $this->executeWithRateLimit(function() use ($endpoint, $params) {
        return $this->request('GET', $endpoint, $params);
        });
    }

    /**
     * Make a POST request to the Reddit API.
     *
     * @param string $endpoint
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function post($endpoint, array $params = [])
    {
        return $this->executeWithRateLimit(function() use ($endpoint, $params) {
        return $this->request('POST', $endpoint, $params);
        });
    }

    /**
     * Make a request to the Reddit API.
     *
     * @param string $method
     * @param string $endpoint
     * @param array $params
     * @return array
     * @throws \Exception
     */
    protected function request($method, $endpoint, array $params = [])
    {
        if (!$this->accessToken) {
            $this->authenticate();
        }

        // Rate limiting is now handled by executeWithRateLimit
        
        // Ensure endpoint starts with a forward slash
        $endpoint = '/' . ltrim($endpoint, '/');
        
        // Construct base URL
        $url = 'https://oauth.reddit.com' . $endpoint;
        
        // For GET requests, append parameters to URL
        if ($method === 'GET' && !empty($params)) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($params);
        }
        
        $headers = [
            'Authorization: Bearer ' . $this->accessToken,
            'User-Agent: ' . $this->credentials->user_agent
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if (!empty($params)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            }
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $errno = curl_errno($ch);

        curl_close($ch);

        if ($response === false) {
            if ($errno === CURLE_OPERATION_TIMEOUTED) {
                throw new \Exception('Request timed out after ' . $this->timeout . ' seconds');
            }
            throw new \Exception('cURL Error (' . $errno . '): ' . $error);
        }

        $data = json_decode($response, true);

        if ($httpCode === 401) {
            // Token might be expired, try to refresh and retry once
            $this->authenticate();
            return $this->request($method, $endpoint, $params);
        }

        if ($httpCode !== 200) {
            $message = isset($data['message']) ? $data['message'] : 'Unknown error';
            throw new \Exception('Reddit API Error: ' . $message . ' (HTTP ' . $httpCode . ')');
        }

        return $data;
    }

    /**
     * Get information about a subreddit.
     *
     * @param string $subredditName
     * @return array
     * @throws \Exception
     */
    public function getSubredditInfo($subredditName)
    {
        return $this->get("r/{$subredditName}/about");
    }

    /**
     * Search for subreddits.
     *
     * @param string $query
     * @param int $limit
     * @return array
     * @throws \Exception
     */
    public function searchSubreddits($query, $limit = 25)
    {
        return $this->executeWithRateLimit(function() use ($query, $limit) {
            if (!$this->accessToken) {
                $this->authenticate();
            }

            try {
                // Log the search attempt
                \Log::info("Searching subreddits for query: {$query}");

                // Make a single search request with expanded data
                $url = 'https://oauth.reddit.com/subreddits/search';
                $params = http_build_query([
            'q' => $query,
            'limit' => $limit,
                    'sort' => 'relevance',
                    'include_over_18' => 'false',
                    'raw_json' => 1
                ]);

                $headers = [
                    'Authorization: Bearer ' . $this->accessToken,
                    'User-Agent: ' . $this->credentials->user_agent
                ];

                $ch = curl_init($url . '?' . $params);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 10, // Reduced timeout for initial search
                    CURLOPT_HTTPHEADER => $headers,
                    CURLOPT_SSL_VERIFYPEER => false, // Disable SSL verification for development
                    CURLOPT_SSL_VERIFYHOST => 0, // Disable SSL host verification for development
                ]);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error = curl_error($ch);
                curl_close($ch);

                if ($response === false) {
                    throw new \Exception('cURL Error: ' . $error);
                }

                $searchData = json_decode($response, true);
                
                if ($httpCode !== 200) {
                    $message = isset($searchData['message']) ? $searchData['message'] : 'Unknown error';
                    throw new \Exception('Reddit API Error: ' . $message . ' (HTTP ' . $httpCode . ')');
                }

                // Log successful response
                \Log::info("Search successful, processing " . count($searchData['data']['children'] ?? []) . " results");

                // Process results without making additional API calls
                if (isset($searchData['data']['children'])) {
                    foreach ($searchData['data']['children'] as &$subreddit) {
                        if (!isset($subreddit['data'])) continue;

                        // Calculate metrics from available data
                        $activeUsers = $subreddit['data']['active_user_count'] ?? 0;
                        $totalSubscribers = $subreddit['data']['subscribers'] ?? 1;
                        $postsPerDay = $this->estimatePostsPerDay($subreddit['data']);
                        $growthRate = $this->estimateGrowthRate($subreddit['data']);
                        $engagementRate = round(($activeUsers / $totalSubscribers) * 100, 2);
                        
                        // Calculate additional engagement metrics
                        $engagementPerPost = $this->calculateEngagementPerPost($activeUsers, $postsPerDay);
                        $activePostEngagement = $this->calculateActivePostEngagement($activeUsers, $postsPerDay);
                        $keywordEngagement = $this->calculateKeywordEngagement($query, $subreddit['data']);
                        
                        // Calculate opportunity score (simple algorithm)
                        $opportunityScore = $this->calculateOpportunityScore($totalSubscribers, $engagementRate, $growthRate, $postsPerDay);
                        
                        // Estimate content types based on subreddit name and description
                        $contentTypes = $this->estimateContentTypes($subreddit['data']);
                        
                        // Extract topics from title and description
                        $topics = $this->extractTopics($subreddit['data']);
                        
                        // Estimate moderation level
                        $moderationLevel = $this->estimateModerationLevel($subreddit['data']);
                        
                        // Create calculated metrics object
                        $calculatedMetrics = [
                            'posts_per_day' => $postsPerDay,
                            'engagement_rate' => $engagementRate,
                            'growth_rate' => $growthRate,
                            'active_users' => $activeUsers,
                            'opportunity_score' => $opportunityScore,
                            'content_types' => $contentTypes,
                            'topics' => $topics,
                            'moderation_level' => $moderationLevel,
                            'engagement_per_post' => $engagementPerPost,
                            'active_post_engagement' => $activePostEngagement,
                            'keyword_engagement' => $keywordEngagement
                        ];
                        
                        // Log metrics for a few subreddits for debugging
                        if ($subreddit['data']['display_name'] && rand(1, 10) <= 3) {
                            \Log::debug("DEBUG - Calculated metrics for r/{$subreddit['data']['display_name']}:", [
                                'posts_per_day' => $postsPerDay,
                                'engagement_rate' => $engagementRate,
                                'growth_rate' => $growthRate,
                                'opportunity_score' => $opportunityScore,
                                'active_post_engagement' => $activePostEngagement,
                                'keyword_engagement' => $keywordEngagement
                            ]);
                        }
                        
                        // Add metrics directly to the subreddit data for easier access
                        $subreddit['data']['posts_per_day'] = $postsPerDay;
                        $subreddit['data']['engagement_rate'] = $engagementRate;
                        $subreddit['data']['growth_rate'] = $growthRate;
                        $subreddit['data']['opportunity_score'] = $opportunityScore;
                        $subreddit['data']['active_post_engagement'] = $activePostEngagement;
                        $subreddit['data']['keyword_engagement'] = $keywordEngagement;
                        
                        // Also store in calculated_metrics for compatibility
                        $subreddit['data']['calculated_metrics'] = $calculatedMetrics;
                    }
                }

                return $searchData;

            } catch (\Exception $e) {
                \Log::error("Reddit API search failed: {$query} - " . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Estimate posts per day based on available metrics
     */
    private function estimatePostsPerDay($subredditData)
    {
        $subscribers = $subredditData['subscribers'] ?? 0;
        $activeUsers = $subredditData['active_user_count'] ?? 0;
        
        // Simple estimation based on active users and subscriber count
        $baseRate = min($activeUsers * 0.1, 100); // Assume 10% of active users post
        $subscriberFactor = $subscribers > 0 ? log10($subscribers) : 1;
        
        return round(max($baseRate * ($subscriberFactor / 5), 1), 1);
    }

    /**
     * Estimate growth rate based on available metrics
     */
    private function estimateGrowthRate($subredditData)
    {
        $ageInDays = (time() - ($subredditData['created_utc'] ?? time())) / 86400;
        $subscribers = $subredditData['subscribers'] ?? 0;
        
        if ($ageInDays < 1 || $subscribers < 1) return 0;
        
        // Estimate daily growth based on age and current size
        $estimatedDailyGrowth = $subscribers / max($ageInDays, 1);
        return round(($estimatedDailyGrowth / $subscribers) * 100, 2);
    }

    /**
     * Get posts from a subreddit.
     *
     * @param string $subredditName
     * @param string $sort
     * @param int $limit
     * @return array
     * @throws \Exception
     */
    public function getSubredditPosts($subredditName, $sort = 'hot', $limit = 25)
    {
        return $this->executeWithRateLimit(function() use ($subredditName, $sort, $limit) {
        return $this->get("r/{$subredditName}/{$sort}", [
            'limit' => $limit,
        ]);
        });
    }

    /**
     * Enable or disable rate limit enforcement
     *
     * @param bool $enforce
     * @return $this
     */
    public function enforceRateLimit($enforce = true)
    {
        $this->enforceRateLimit = $enforce;
        return $this;
    }
    
    /**
     * Check if we're within rate limits
     *
     * @return bool
     */
    protected function isWithinRateLimit()
    {
        if (!$this->enforceRateLimit) {
            return true;
        }
        
        // Clean up old requests (older than 1 minute)
        $oneMinuteAgo = time() - 60;
        $this->requestLog = array_filter($this->requestLog, function($timestamp) use ($oneMinuteAgo) {
            return $timestamp >= $oneMinuteAgo;
        });
        
        // Check if we're within limits
        return count($this->requestLog) < $this->maxRequestsPerMinute;
    }
    
    /**
     * Load request log from cache
     *
     * @return void
     */
    protected function loadRequestLog()
    {
        try {
            // Get user-specific or global request log
            $cacheKey = $this->getRateLimitCacheKey();
            $cachedLog = \Cache::get($cacheKey, []);
            
            // Ensure we have a valid array
            $this->requestLog = is_array($cachedLog) ? $cachedLog : [];
            
            // Clean up old requests (older than 1 minute)
            $oneMinuteAgo = time() - 60;
            $this->requestLog = array_filter($this->requestLog, function($timestamp) use ($oneMinuteAgo) {
                return $timestamp >= $oneMinuteAgo;
            });
        } catch (\Exception $e) {
            // If there's any error, just start with an empty log
            \Log::warning("Error loading request log from cache: " . $e->getMessage());
            $this->requestLog = [];
        }
    }
    
    /**
     * Save request log to cache
     *
     * @return void
     */
    protected function saveRequestLog()
    {
        try {
            $cacheKey = $this->getRateLimitCacheKey();
            \Cache::put($cacheKey, $this->requestLog, now()->addMinutes(2));
        } catch (\Exception $e) {
            // If there's any error saving to cache, just log it
            \Log::warning("Error saving request log to cache: " . $e->getMessage());
        }
    }
    
    /**
     * Get cache key for rate limit data
     *
     * @return string
     */
    protected function getRateLimitCacheKey()
    {
        // If we have a user, use user-specific cache key
        if ($this->user && isset($this->user->id)) {
            return 'reddit_api_rate_limit_user_' . $this->user->id;
        }
        
        // Otherwise use a global cache key
        return 'reddit_api_rate_limit_global';
    }
    
    /**
     * Log a new API request
     *
     * @return void
     */
    protected function logRequest()
    {
        $this->requestLog[] = time();
        $this->saveRequestLog();
    }
    
    /**
     * Get current rate limit status
     *
     * @return array
     */
    public function getRateLimitStatus()
    {
        // Clean up old requests
        $oneMinuteAgo = time() - 60;
        $this->requestLog = array_filter($this->requestLog, function($timestamp) use ($oneMinuteAgo) {
            return $timestamp >= $oneMinuteAgo;
        });
        
        return [
            'requests_in_last_minute' => count($this->requestLog),
            'max_requests_per_minute' => $this->maxRequestsPerMinute,
            'remaining' => $this->maxRequestsPerMinute - count($this->requestLog),
            'reset_in_seconds' => $this->requestLog ? 60 - (time() - min($this->requestLog)) : 0
        ];
    }

    /**
     * Execute a function with rate limit awareness
     *
     * @param callable $callback The function to execute
     * @param int $maxRetries Maximum number of retries
     * @param int $initialBackoff Initial backoff time in seconds
     * @return mixed The result of the callback
     * @throws \Exception
     */
    public function executeWithRateLimit(callable $callback, $maxRetries = 3, $initialBackoff = 2)
    {
        $retries = 0;
        $backoff = $initialBackoff;
        
        while (true) {
            try {
                // Check if we're within rate limits
                if (!$this->isWithinRateLimit()) {
                    $status = $this->getRateLimitStatus();
                    $resetIn = $status['reset_in_seconds'];
                    
                    if ($retries < $maxRetries) {
                        // Wait for rate limit to reset
                        $sleepTime = min($resetIn + 1, $backoff);
                        \Log::warning("Rate limit reached. Waiting {$sleepTime} seconds before retry. Attempt " . ($retries + 1) . " of {$maxRetries}");
                        sleep($sleepTime);
                        
                        // Increase backoff for next attempt
                        $backoff *= 2;
                        $retries++;
                        continue;
                    } else {
                        throw new \Exception("Reddit API rate limit exceeded. Please try again in {$resetIn} seconds.");
                    }
                }
                
                // Log this request
                $this->logRequest();
                
                // Execute the callback
                return $callback();
                
            } catch (\Exception $e) {
                // If it's not a rate limit error, or we've exhausted retries, rethrow
                if (strpos($e->getMessage(), 'rate limit') === false || $retries >= $maxRetries) {
                    throw $e;
                }
                
                // Increase backoff for next attempt
                $backoff *= 2;
                $retries++;
                
                \Log::warning("Rate limit error: {$e->getMessage()}. Retrying in {$backoff} seconds. Attempt {$retries} of {$maxRetries}");
                sleep($backoff);
            }
        }
    }

    /**
     * Calculate opportunity score based on various metrics
     */
    private function calculateOpportunityScore($subscribers, $engagementRate, $growthRate, $postsPerDay)
    {
        // Normalize subscriber count (log scale)
        $subscriberScore = min(100, 20 * log10(max($subscribers, 100)));
        
        // Normalize engagement rate (0-100)
        $engagementScore = min(100, $engagementRate * 10);
        
        // Normalize growth rate
        $growthScore = min(100, $growthRate);
        
        // Normalize activity level
        $activityScore = min(100, $postsPerDay * 5);
        
        // Calculate weighted score
        $score = (
            ($subscriberScore * 0.2) +
            ($engagementScore * 0.3) +
            ($growthScore * 0.3) +
            ($activityScore * 0.2)
        );
        
        return round($score);
    }
    
    /**
     * Estimate content types based on subreddit data
     */
    private function estimateContentTypes($subredditData)
    {
        $contentTypes = [];
        $name = strtolower($subredditData['display_name'] ?? '');
        $title = strtolower($subredditData['title'] ?? '');
        $description = strtolower($subredditData['public_description'] ?? '');
        
        // Check for image-related keywords
        if (
            strpos($name, 'pic') !== false || 
            strpos($name, 'image') !== false || 
            strpos($name, 'photo') !== false ||
            strpos($title, 'picture') !== false ||
            strpos($description, 'image') !== false ||
            strpos($description, 'photo') !== false
        ) {
            $contentTypes[] = 'images';
        }
        
        // Check for video-related keywords
        if (
            strpos($name, 'video') !== false || 
            strpos($name, 'tube') !== false || 
            strpos($name, 'film') !== false ||
            strpos($title, 'video') !== false ||
            strpos($description, 'video') !== false ||
            strpos($description, 'youtube') !== false
        ) {
            $contentTypes[] = 'videos';
        }
        
        // Check for text-related keywords
        if (
            strpos($name, 'text') !== false || 
            strpos($name, 'story') !== false || 
            strpos($name, 'write') !== false ||
            strpos($title, 'discussion') !== false ||
            strpos($description, 'discussion') !== false ||
            strpos($description, 'text') !== false
        ) {
            $contentTypes[] = 'text';
        }
        
        // Check for link-related keywords
        if (
            strpos($name, 'link') !== false || 
            strpos($name, 'news') !== false || 
            strpos($title, 'link') !== false ||
            strpos($description, 'link') !== false ||
            strpos($description, 'article') !== false
        ) {
            $contentTypes[] = 'links';
        }
        
        // Default to text if nothing else is detected
        if (empty($contentTypes)) {
            $contentTypes[] = 'text';
        }
        
        return $contentTypes;
    }
    
    /**
     * Extract topics from subreddit data
     */
    private function extractTopics($subredditData)
    {
        $topics = [];
        $name = $subredditData['display_name'] ?? '';
        $title = $subredditData['title'] ?? '';
        $description = $subredditData['public_description'] ?? '';
        
        // Combine all text
        $text = $name . ' ' . $title . ' ' . $description;
        
        // Remove common words and punctuation
        $text = strtolower($text);
        $text = preg_replace('/[^\w\s]/', ' ', $text);
        
        // Split into words
        $words = preg_split('/\s+/', $text);
        
        // Filter out common words and short words
        $stopWords = ['a', 'an', 'the', 'and', 'or', 'but', 'is', 'are', 'in', 'on', 'at', 'to', 'for', 'with', 'by', 'about', 'like', 'through', 'over', 'before', 'between', 'after', 'since', 'without', 'under', 'within', 'along', 'following', 'across', 'behind', 'beyond', 'plus', 'except', 'but', 'up', 'out', 'around', 'down', 'off', 'above', 'near', 'of', 'this', 'that', 'these', 'those', 'it', 'they', 'we', 'you', 'i', 'he', 'she', 'him', 'her', 'them', 'their', 'our', 'your', 'my', 'his', 'its', 'us', 'me'];
        
        $filteredWords = array_filter($words, function($word) use ($stopWords) {
            return strlen($word) > 3 && !in_array($word, $stopWords);
        });
        
        // Count word frequencies
        $wordCounts = array_count_values($filteredWords);
        
        // Sort by frequency
        arsort($wordCounts);
        
        // Take top 5 words as topics
        $topics = array_slice(array_keys($wordCounts), 0, 5);
        
        return $topics;
    }
    
    /**
     * Estimate moderation level based on subreddit data
     */
    private function estimateModerationLevel($subredditData)
    {
        $subscribers = $subredditData['subscribers'] ?? 0;
        $description = $subredditData['public_description'] ?? '';
        
        // Check for strict moderation keywords
        $strictKeywords = ['rule', 'strict', 'remove', 'ban', 'moderate', 'guideline', 'policy'];
        $strictCount = 0;
        
        foreach ($strictKeywords as $keyword) {
            if (stripos($description, $keyword) !== false) {
                $strictCount++;
            }
        }
        
        // Larger subreddits tend to have stricter moderation
        if ($subscribers > 1000000 || $strictCount >= 3) {
            return 'strict';
        } else if ($subscribers > 100000 || $strictCount >= 1) {
            return 'moderate';
        } else {
            return 'relaxed';
        }
    }

    /**
     * Calculate engagement per post based on active users and posts per day
     */
    private function calculateEngagementPerPost($activeUsers, $postsPerDay)
    {
        // If there are no posts, return 0 to avoid division by zero
        if ($postsPerDay <= 0) {
            return 0;
        }
        
        // Estimate engagement per post based on active users
        // This is a simplified model - in reality, we'd need actual post data
        $estimatedTotalEngagement = $activeUsers * 0.2; // Assume 20% of active users engage with content
        $engagementPerPost = $estimatedTotalEngagement / $postsPerDay;
        
        return round($engagementPerPost, 2);
    }

    /**
     * Calculate active post engagement based on active users and posts per day
     */
    private function calculateActivePostEngagement($activeUsers, $postsPerDay)
    {
        // If there are no posts, return 0 to avoid division by zero
        if ($postsPerDay <= 0) {
            return 0;
        }
        
        // Estimate active posts (posts that get engagement)
        // Typically not all posts get engagement, so we estimate 70% of posts are "active"
        $activePosts = $postsPerDay * 0.7;
        
        // Estimate engagement per active post
        $estimatedTotalEngagement = $activeUsers * 0.2; // Assume 20% of active users engage with content
        $activePostEngagement = $activePosts > 0 ? $estimatedTotalEngagement / $activePosts : 0;
        
        return round($activePostEngagement, 2);
    }

    /**
     * Calculate keyword engagement based on query and subreddit data
     */
    private function calculateKeywordEngagement($query, $subredditData)
    {
        // Get the normalized query
        $normalizedQuery = strtolower(trim($query));
        
        // Get subreddit text content
        $name = strtolower($subredditData['display_name'] ?? '');
        $title = strtolower($subredditData['title'] ?? '');
        $description = strtolower($subredditData['public_description'] ?? '');
        
        // Combine all text
        $text = $name . ' ' . $title . ' ' . $description;
        
        // Calculate relevance score based on keyword presence
        $relevanceScore = 0;
        
        // Check if query terms appear in the subreddit text
        $queryTerms = explode(' ', $normalizedQuery);
        foreach ($queryTerms as $term) {
            if (strlen($term) > 2 && strpos($text, $term) !== false) {
                $relevanceScore += 1;
            }
        }
        
        // Normalize the score (0-10 scale)
        $normalizedScore = min(10, $relevanceScore);
        
        // Calculate keyword engagement based on relevance and active users
        $activeUsers = $subredditData['active_user_count'] ?? 0;
        $keywordEngagement = $normalizedScore * ($activeUsers * 0.01);
        
        return round($keywordEngagement, 2);
    }
} 