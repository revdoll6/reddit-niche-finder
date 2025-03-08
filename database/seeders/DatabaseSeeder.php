<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UsersTableSeeder::class,
            SubredditsTableSeeder::class,
            KeywordsTableSeeder::class,
            UserSubredditTrackingSeeder::class,
            CompetitorActivitySeeder::class,
            SubredditAnalyticsSeeder::class,
        ]);
    }
}
