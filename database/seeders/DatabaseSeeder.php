<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

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
