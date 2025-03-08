<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CompetitorActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subreddits = DB::table('subreddits')->get();
        $competitors = ['TechStartup', 'DigitalAgency', 'BusinessGrowth'];

        foreach ($subreddits as $subreddit) {
            foreach ($competitors as $competitor) {
                DB::table('competitor_activity')->insert([
                    'subreddit_id' => $subreddit->id,
                    'competitor_name' => $competitor,
                    'post_content' => "Check out our latest {$competitor} product launch!",
                    'engagement_rate' => rand(50, 95) / 100,
                    'posted_at' => Carbon::now()->subDays(rand(1, 30)),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
    }
}
