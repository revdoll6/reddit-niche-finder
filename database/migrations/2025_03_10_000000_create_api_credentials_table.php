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
        Schema::create('api_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('provider')->default('reddit'); // For future expansion to other APIs
            $table->string('client_id')->nullable();
            $table->text('client_secret')->nullable(); // Will be encrypted
            $table->string('username')->nullable();
            $table->string('user_agent')->nullable();
            $table->boolean('is_connected')->default(false);
            $table->timestamp('last_connected_at')->nullable();
            $table->timestamps();
            
            // Unique constraint to ensure one set of credentials per provider per user
            $table->unique(['user_id', 'provider']);
        });
        
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('key', 64)->unique();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });
        
        Schema::create('api_rate_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('provider')->default('reddit');
            $table->integer('requests_per_minute')->default(60);
            $table->integer('concurrent_requests')->default(5);
            $table->boolean('retry_failed_requests')->default(true);
            $table->timestamps();
            
            // Unique constraint to ensure one set of rate limits per provider per user
            $table->unique(['user_id', 'provider']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_rate_limits');
        Schema::dropIfExists('api_keys');
        Schema::dropIfExists('api_credentials');
    }
}; 