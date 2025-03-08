<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SubredditAnalyticsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subreddits = DB::table('subreddits')->get();
        
        foreach ($subreddits as $subreddit) {
            // Create analytics for the last 30 days
            for ($i = 0; $i < 30; $i++) {
                $date = Carbon::now()->subDays($i);
                
                DB::table('subreddit_analytics')->insert([
                    'subreddit_id' => $subreddit->id,
                    'date' => $date->format('Y-m-d'),
                    'active_users' => rand(1000, 5000),
                    'posts_count' => rand(50, 200),
                    'comments_count' => rand(500, 2000),
                    'avg_engagement' => rand(50, 95) / 100,
                    'sentiment_trend' => rand(60, 90) / 100,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
    }
}
