<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SubredditsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('subreddits')->insert([
            [
                'name' => 'startups',
                'description' => 'r/startups is the place to discuss startup problems and solutions.',
                'members' => 1200000,
                'engagement_rate' => 0.75,
                'sentiment_score' => 0.85,
                'last_crawled' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'marketing',
                'description' => 'For marketing professionals to discuss and ask questions.',
                'members' => 500000,
                'engagement_rate' => 0.65,
                'sentiment_score' => 0.78,
                'last_crawled' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'smallbusiness',
                'description' => 'Small business owners helping other small business owners.',
                'members' => 800000,
                'engagement_rate' => 0.82,
                'sentiment_score' => 0.90,
                'last_crawled' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
