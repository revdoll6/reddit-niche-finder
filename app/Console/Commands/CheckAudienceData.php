<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Audience;
use App\Models\AudienceSubreddit;

class CheckAudienceData extends Command
{
    protected $signature = 'check:audience-data';
    protected $description = 'Check audience data in the database for missing metrics';

    public function handle()
    {
        $this->info('Checking audience data in the database...');
        
        $audiences = Audience::with('subreddits')->get();
        
        if ($audiences->isEmpty()) {
            $this->error('No audiences found in the database.');
            return Command::FAILURE;
        }
        
        $this->info('Found ' . $audiences->count() . ' audiences in the database.');
        
        foreach ($audiences as $audience) {
            $this->info("\n====== Audience: {$audience->name} (ID: {$audience->id}) ======");
            $this->info('Subreddits count: ' . $audience->subreddits->count());
            
            if ($audience->subreddits->isEmpty()) {
                $this->warn('No subreddits in this audience.');
                continue;
            }
            
            $this->info("\nChecking for metrics in subreddit data:");
            $table = [];
            
            foreach ($audience->subreddits as $subreddit) {
                $data = $subreddit->subreddit_data;
                
                // Create a row for the table
                $row = [
                    'name' => $subreddit->subreddit_name,
                    'posts_per_day' => $this->formatMetricValue($data, 'posts_per_day'),
                    'opportunity_score' => $this->formatMetricValue($data, 'opportunity_score'),
                    'engagement_rate' => $this->formatMetricValue($data, 'engagement_rate'),
                    'growth_rate' => $this->formatMetricValue($data, 'growth_rate'),
                    'active_post_engagement' => $this->formatMetricValue($data, 'active_post_engagement'),
                    'keyword_engagement' => $this->formatMetricValue($data, 'keyword_engagement'),
                ];
                
                $table[] = $row;
                
                // For the first few subreddits, print the full data structure
                if (count($table) <= 1) {
                    $this->info("\nRaw data for r/{$subreddit->subreddit_name}:");
                    $this->line(json_encode($data, JSON_PRETTY_PRINT));
                }
            }
            
            // Output the table
            $this->table(
                ['Subreddit', 'Posts/Day', 'Opportunity', 'Engagement', 'Growth', 'Active Eng', 'Keyword Eng'],
                $table
            );
            
            // Check for metrics in calculated_metrics
            $firstSubreddit = $audience->subreddits->first();
            if ($firstSubreddit && isset($firstSubreddit->subreddit_data['calculated_metrics'])) {
                $this->info("\nCalculated metrics found for r/{$firstSubreddit->subreddit_name}:");
                $calculatedMetrics = $firstSubreddit->subreddit_data['calculated_metrics'];
                $this->line(json_encode($calculatedMetrics, JSON_PRETTY_PRINT));
            } else {
                $this->warn("\nNo calculated_metrics found in subreddit data.");
            }
        }
        
        $this->info("\nDone checking audience data.\n");
        return Command::SUCCESS;
    }
    
    private function formatMetricValue($data, $key)
    {
        // Check direct property
        if (isset($data[$key])) {
            return $data[$key] . ' (direct)';
        }
        
        // Check in calculated_metrics
        if (isset($data['calculated_metrics']) && isset($data['calculated_metrics'][$key])) {
            return $data['calculated_metrics'][$key] . ' (calc)';
        }
        
        // Check for alternate name (only for active_post_engagement)
        if ($key === 'active_post_engagement' && isset($data['active_engagement'])) {
            return $data['active_engagement'] . ' (alt)';
        }
        
        return 'Missing';
    }
} 