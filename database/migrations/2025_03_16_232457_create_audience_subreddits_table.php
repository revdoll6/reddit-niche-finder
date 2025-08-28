<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('audiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('audience_subreddits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audience_id')->constrained()->onDelete('cascade');
            $table->string('subreddit_name');
            $table->json('subreddit_data');
            $table->timestamps();
            
            // Add unique constraint to prevent duplicate subreddits in same audience
            $table->unique(['audience_id', 'subreddit_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audience_subreddits');
        Schema::dropIfExists('audiences');
    }
};
