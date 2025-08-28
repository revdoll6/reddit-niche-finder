<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RedditApiService;
use App\Models\User;

class TestSearchMetricsData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:search-metrics {query=fitness} {--user=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test search results to check for metrics data';

    protected $redditService;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = $this->argument('query');
        $userId = $this->option('user');
        
        $this->info("Testing search metrics for query: '{$query}'");
        
        // Get a user with Reddit API credentials
        $user = User::find($userId);
        if (!$user) {
            $this->error("User with ID {$userId} not found. Please provide a valid user ID with --user option.");
            return Command::FAILURE;
        }
        
        // Check if user has Reddit credentials
        if (!$user->redditCredentials) {
            $this->error("User does not have Reddit API credentials set up.");
            return Command::FAILURE;
        }
        
        // Create the Reddit API service
        $this->redditService = new RedditApiService($user);
        
        try {
            // Authenticate with Reddit API
            $this->info("Authenticating with Reddit API...");
            $this->redditService->authenticate();
            
            // Perform the search
            $this->info("Searching for '{$query}'...");
            $searchData = $this->redditService->searchSubreddits($query, 5); // Limit to 5 results for brevity
            
            if (!isset($searchData['data']['children']) || empty($searchData['data']['children'])) {
                $this->warn("No search results found for '{$query}'");
                return Command::FAILURE;
            }
            
            $this->info("Found " . count($searchData['data']['children']) . " results.");
            
            // Check metrics in the search results
            $table = [];
            
            foreach ($searchData['data']['children'] as $result) {
                $subreddit = $result['data'];
                $name = $subreddit['display_name'] ?? 'Unknown';
                
                // Get metrics values directly
                $metrics = [
                    'posts_per_day' => $subreddit['posts_per_day'] ?? 'Not set',
                    'opportunity_score' => $subreddit['opportunity_score'] ?? 'Not set',
                    'engagement_rate' => $subreddit['engagement_rate'] ?? 'Not set',
                    'growth_rate' => $subreddit['growth_rate'] ?? 'Not set',
                    'active_post_engagement' => $subreddit['active_post_engagement'] ?? 'Not set',
                    'keyword_engagement' => $subreddit['keyword_engagement'] ?? 'Not set',
                ];
                
                // Check if metrics exist in calculated_metrics
                $hasCalculatedMetrics = isset($subreddit['calculated_metrics']);
                
                $table[] = [
                    'name' => $name,
                    'metrics_direct' => $hasCalculatedMetrics ? 'Yes' : 'No',
                    'posts_per_day' => $metrics['posts_per_day'],
                    'opportunity_score' => $metrics['opportunity_score'],
                    'engagement_rate' => $metrics['engagement_rate'],
                    'growth_rate' => $metrics['growth_rate'],
                    'active_post_engagement' => $metrics['active_post_engagement'],
                    'keyword_engagement' => $metrics['keyword_engagement'],
                ];
                
                // For the first result, print full calculated metrics
                if (count($table) === 1 && $hasCalculatedMetrics) {
                    $this->info("\nCalculated metrics for r/{$name}:");
                    $this->line(json_encode($subreddit['calculated_metrics'], JSON_PRETTY_PRINT));
                }
            }
            
            // Output the table
            $this->table(
                ['Subreddit', 'Has Calc', 'Posts/Day', 'Opportunity', 'Engagement', 'Growth', 'Active Eng', 'Keyword Eng'],
                $table
            );
            
            $this->info("\nSearch test completed successfully.");
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("Error testing search: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
