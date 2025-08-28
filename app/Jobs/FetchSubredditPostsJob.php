<?php

namespace App\Jobs;

use App\Services\RedditApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class FetchSubredditPostsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $audienceId;
    protected $subredditName;
    
    // Set a reasonable timeout
    public $timeout = 300; // 5 minutes
    
    // Set retry parameters
    public $tries = 3;
    public $backoff = [30, 60, 120]; // retry after 30s, 60s, then 120s

    /**
     * Create a new job instance.
     */
    public function __construct($audienceId, $subredditName)
    {
        $this->audienceId = $audienceId;
        $this->subredditName = $subredditName;
    }

    /**
     * Execute the job.
     * 
     * Note: We keep the RedditApiService parameter for compatibility with Laravel's dependency injection
     * but we don't actually use it.
     */
    public function handle(RedditApiService $redditService): void
    {
        try {
            // Update status to in_progress
            DB::table('audience_subreddit_posts')
                ->where('audience_id', $this->audienceId)
                ->where('subreddit_name', $this->subredditName)
                ->update(['fetch_status' => 'in_progress']);
            
            Log::info("Starting to fetch posts for r/{$this->subredditName} in audience {$this->audienceId}");
            
            // IMPORTANT: withHeaders() must be called BEFORE post() to avoid method chaining errors
            // Get access token exactly as in Explorer.vue
            $tokenResponse = Http::withoutVerifying()->withHeaders([
                'Authorization' => 'Basic ' . base64_encode('kkb3ZVEGF5hwlrtNqjjq5A:jqnUTNusqKp2BSI4VPtz9P4ojvRGuw'),
                'Content-Type' => 'application/x-www-form-urlencoded',
                'User-Agent' => 'MyApp/0.1 by NeedleworkerFuzzy314'
            ])->asForm()->post('https://www.reddit.com/api/v1/access_token', [
                'grant_type' => 'client_credentials'
            ]);

            if ($tokenResponse->failed()) {
                throw new \Exception("Failed to get access token. HTTP code: {$tokenResponse->status()}");
            }

            $tokenData = $tokenResponse->json();
            if (!isset($tokenData['access_token'])) {
                throw new \Exception('Access token not found in response');
            }

            $accessToken = $tokenData['access_token'];
            Log::info("Successfully obtained access token for r/{$this->subredditName}");

            // Fetch up to 500 posts in batches of 100
            $allPosts = [];
            $after = null;

            while (count($allPosts) < 500) {
                $url = "https://oauth.reddit.com/r/{$this->subredditName}/new";
                
                // IMPORTANT: Call withHeaders() BEFORE get() to avoid method chaining errors
                $postsResponse = Http::withoutVerifying()->withHeaders([
                    'Authorization' => "Bearer {$accessToken}",
                    'User-Agent' => 'MyApp/0.1 by NeedleworkerFuzzy314'
                ])->get($url, [
                    'limit' => 100,
                    'after' => $after
                ]);

                if ($postsResponse->failed()) {
                    throw new \Exception("Failed to fetch posts. HTTP code: {$postsResponse->status()}");
                }

                $data = $postsResponse->json();

                if (!isset($data['data']['children']) || empty($data['data']['children'])) {
                    Log::info("No more posts available for r/{$this->subredditName}");
                    break;
                }

                Log::info("Fetched " . count($data['data']['children']) . " posts for r/{$this->subredditName}");

                $posts = array_map(function($child) {
                    return $child['data'];
                }, $data['data']['children']);

                $allPosts = array_merge($allPosts, $posts);

                if (!empty($data['data']['children'])) {
                    $lastPost = end($data['data']['children']);
                    $after = $lastPost['data']['name'];
                } else {
                    break;
                }

                if (count($data['data']['children']) < 100) {
                    Log::info("Reached end of available posts for r/{$this->subredditName}");
                    break;
                }

                if (count($allPosts) >= 500) {
                    Log::info("Reached maximum post limit (500) for r/{$this->subredditName}");
                    $allPosts = array_slice($allPosts, 0, 500);
                    break;
                }

                sleep(2);
            }
            
            // Log engagement metrics
            $totalUpvotes = array_reduce($allPosts, function($sum, $post) {
                return $sum + ($post['score'] ?? 0);
            }, 0);
            
            $totalComments = array_reduce($allPosts, function($sum, $post) {
                return $sum + ($post['num_comments'] ?? 0);
            }, 0);
            
            $postsWithEngagement = count(array_filter($allPosts, function($post) {
                return ($post['score'] ?? 0) > 0 || ($post['num_comments'] ?? 0) > 0;
            }));
            
            Log::info("Engagement metrics for r/{$this->subredditName}:");
            Log::info("- Total upvotes: {$totalUpvotes}");
            Log::info("- Total comments: {$totalComments}");
            Log::info("- Posts with engagement: {$postsWithEngagement}/" . count($allPosts));
            
            // Prepare the posts data
            $postsData = [
                'count' => count($allPosts),
                'subreddit' => $this->subredditName,
                'fetched_at' => now()->format('Y-m-d H:i:s'),
                'posts' => $allPosts
            ];
            
            // Get the newest post ID for reference
            $newestPostId = null;
            if (!empty($allPosts)) {
                $newestPostId = $allPosts[0]['name']; // First post is the newest
            }
            
            // Update the database record
            DB::table('audience_subreddit_posts')
                ->where('audience_id', $this->audienceId)
                ->where('subreddit_name', $this->subredditName)
                ->update([
                    'posts_data' => json_encode($postsData),
                    'fetched_at' => now(),
                    'newest_post_id' => $newestPostId,
                    'fetch_status' => 'completed'
                ]);
                
            Log::info("Successfully fetched " . count($allPosts) . " posts for r/{$this->subredditName} in audience {$this->audienceId}");
            
        } catch (\Exception $e) {
            Log::error("Error fetching posts for audience {$this->audienceId}, subreddit {$this->subredditName}: " . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
                
            // Update status to failed
            DB::table('audience_subreddit_posts')
                ->where('audience_id', $this->audienceId)
                ->where('subreddit_name', $this->subredditName)
                ->update(['fetch_status' => 'failed']);
            
            // Re-throw to trigger job retry if tries remain
            throw $e;
        }
    }
}
