<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserSubredditTrackingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = DB::table('users')->get();
        $subreddits = DB::table('subreddits')->get();

        foreach ($users as $user) {
            foreach ($subreddits as $index => $subreddit) {
                DB::table('user_subreddit_tracking')->insert([
                    'user_id' => $user->id,
                    'subreddit_id' => $subreddit->id,
                    'is_favorite' => $index === 0, // First subreddit is favorite
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
    }
}
