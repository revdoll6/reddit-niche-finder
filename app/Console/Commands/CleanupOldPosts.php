<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanupOldPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reddit:cleanup-old-posts {--days=30 : Number of days to keep posts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old posts data for audiences not viewed recently';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $this->info("Cleaning up posts data older than {$days} days...");
        
        try {
            // Delete posts for audiences not viewed in specified days
            $oldRecordsCount = DB::table('audience_subreddit_posts')
                ->whereNotIn('audience_id', function($query) use ($days) {
                    $query->select('id')
                        ->from('audiences')
                        ->whereRaw("updated_at > DATE_SUB(NOW(), INTERVAL {$days} DAY)");
                })
                ->delete();
                
            $this->info("Deleted posts for {$oldRecordsCount} audience-subreddit combinations not viewed in {$days} days");
            Log::info("Cleanup completed: Deleted {$oldRecordsCount} old post records older than {$days} days");
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error cleaning up old posts: " . $e->getMessage());
            Log::error("Error in cleanup command: " . $e->getMessage());
            
            return Command::FAILURE;
        }
    }
}
