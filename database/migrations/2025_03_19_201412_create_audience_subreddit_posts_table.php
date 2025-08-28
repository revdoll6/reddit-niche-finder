<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAudienceSubredditPostsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('audience_subreddit_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audience_id')->constrained()->onDelete('cascade');
            $table->string('subreddit_name');
            $table->json('posts_data')->nullable();
            $table->timestamp('fetched_at')->nullable();
            $table->string('newest_post_id')->nullable();
            $table->enum('fetch_status', ['pending', 'in_progress', 'completed', 'failed'])->default('pending');
            $table->timestamps();
            
            // Add index for efficient queries
            $table->index(['audience_id', 'subreddit_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audience_subreddit_posts');
    }
}
