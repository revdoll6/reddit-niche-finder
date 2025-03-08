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
        Schema::create('competitor_activity', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subreddit_id')->constrained()->onDelete('cascade');
            $table->string('competitor_name');
            $table->text('post_content');
            $table->float('engagement_rate')->default(0);
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->index(['subreddit_id', 'competitor_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competitor_activity');
    }
};
