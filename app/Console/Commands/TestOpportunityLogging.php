<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestOpportunityLogging extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:opportunity-logging';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the opportunity metrics logging API endpoint';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing opportunity metrics logging endpoint...');
        
        try {
            // Create test data
            $testData = [
                'subreddit_name' => 'TestSubreddit',
                'normalized_score' => 75.5,
                'raw_score' => 82.3,
                'engagement_per_post' => 12.5,
                'keyword_engagement' => 18.7,
                'posts_per_day' => 5.2,
                'growth_rate' => 3.8,
                'subscriber_count' => 12500,
                'normalized_metrics' => [
                    'engagement' => 65.5,
                    'keyword' => 78.2,
                    'content' => 72.0,
                    'base' => 85.3,
                    'activity' => 52.1,
                    'growth' => 42.5,
                    'size' => 35.8
                ],
                'timestamp' => now()->toIso8601String()
            ];
            
            $this->info('Prepared test payload:');
            $this->line(json_encode($testData, JSON_PRETTY_PRINT));
            
            // Call the endpoint
            $this->info('Calling opportunity metrics logging endpoint...');
            $response = Http::post(url('/api/log/opportunity-metrics'), $testData);
            
            $this->info('Response:');
            $this->line(json_encode($response->json(), JSON_PRETTY_PRINT));
            
            if ($response->successful()) {
                $this->info('✅ Opportunity metrics logging test SUCCESSFUL');
            } else {
                $this->error('❌ Opportunity metrics logging test FAILED with status: ' . $response->status());
            }
            
            // Check if log file exists
            $logPath = storage_path('logs/opportunity_metrics.log');
            if (file_exists($logPath)) {
                $this->info('Opportunity metrics log file exists: ' . $logPath);
                
                // Show last few lines of the log
                $this->info('Last 15 lines of log:');
                $logContent = file_get_contents($logPath);
                $logLines = array_slice(explode("\n", $logContent), -15);
                foreach ($logLines as $line) {
                    if (!empty(trim($line))) {
                        $this->line($line);
                    }
                }
            } else {
                $this->warn('Opportunity metrics log file does not exist: ' . $logPath);
            }
            
        } catch (\Exception $e) {
            $this->error('Error during test: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }
        
        return Command::SUCCESS;
    }
} 