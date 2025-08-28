<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class TestSearchLogging extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:search-logging';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the search logging API endpoint';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing search logging endpoint...');
        
        try {
            // Create test data
            $testData = [
                'query' => 'test query',
                'results' => [
                    [
                        'id' => 'test1',
                        'name' => 'test1',
                        'display_name' => 'Test Subreddit 1',
                        'subscribers' => 1000,
                        'description' => 'Test description 1'
                    ],
                    [
                        'id' => 'test2',
                        'name' => 'test2',
                        'display_name' => 'Test Subreddit 2',
                        'subscribers' => 2000,
                        'description' => 'Test description 2'
                    ]
                ],
                'timestamp' => now()->toISOString(),
                'total_results' => 2
            ];
            
            $this->info('Prepared test payload:');
            $this->line(json_encode($testData, JSON_PRETTY_PRINT));
            
            // First test debug endpoint
            $this->info('Calling debug endpoint...');
            $response = Http::post(url('/api/debug/log-search'), $testData);
            
            $this->info('Debug endpoint response:');
            $this->line(json_encode($response->json(), JSON_PRETTY_PRINT));
            
            if ($response->successful()) {
                $this->info('Debug endpoint call was successful');
            } else {
                $this->error('Debug endpoint call failed with status: ' . $response->status());
            }
            
            // Then test actual endpoint
            $this->info('Calling search logging endpoint...');
            $response = Http::post(url('/api/log/search'), $testData);
            
            $this->info('Search endpoint response:');
            $this->line(json_encode($response->json(), JSON_PRETTY_PRINT));
            
            if ($response->successful()) {
                $this->info('✅ Search logging test SUCCESSFUL');
            } else {
                $this->error('❌ Search logging test FAILED with status: ' . $response->status());
            }
            
            // Check if log file was created
            $logPath = storage_path('logs/search.log');
            if (file_exists($logPath)) {
                $this->info('Search log file exists: ' . $logPath);
                
                // Show last few lines of the log
                $this->info('Last 10 lines of search log:');
                $logContent = file_get_contents($logPath);
                $logLines = array_slice(explode("\n", $logContent), -10);
                foreach ($logLines as $line) {
                    if (!empty(trim($line))) {
                        $this->line($line);
                    }
                }
            } else {
                $this->warn('Search log file does not exist: ' . $logPath);
            }
            
        } catch (\Exception $e) {
            $this->error('Error during test: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }
        
        return Command::SUCCESS;
    }
} 