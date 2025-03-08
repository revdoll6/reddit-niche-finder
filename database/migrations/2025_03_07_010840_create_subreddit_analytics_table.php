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
        Schema::create('subreddit_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subreddit_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->integer('active_users')->default(0);
            $table->integer('posts_count')->default(0);
            $table->integer('comments_count')->default(0);
            $table->float('avg_engagement')->default(0);
            $table->float('sentiment_trend')->default(0);
            $table->timestamps();

            $table->unique(['subreddit_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subreddit_analytics');
    }
};
