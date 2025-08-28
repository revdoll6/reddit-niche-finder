<?php

namespace App\Http\Controllers;

use App\Models\Audience;
use App\Models\AudienceSubreddit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AudienceController extends Controller
{
    /**
     * Get all audiences for the authenticated user
     */
    public function index()
    {
        // Get all audiences with their subreddits
        $audiences = Auth::user()->audiences()
            ->with('subreddits')
            ->get();
            
        // Add debug logging to see what data is returned from database
        if ($audiences->count() > 0 && $audiences[0]->subreddits->count() > 0) {
            $firstAudience = $audiences[0];
            $firstSubreddit = $firstAudience->subreddits->first();
            
            \Log::debug('First audience data from database:', [
                'id' => $firstAudience->id,
                'name' => $firstAudience->name
            ]);
            
            \Log::debug('First subreddit data from database:', [
                'name' => $firstSubreddit->subreddit_name,
                'raw_data_sample' => array_intersect_key($firstSubreddit->subreddit_data, array_flip([
                    'posts_per_day', 
                    'opportunity_score',
                    'engagement_rate',
                    'growth_rate',
                    'active_post_engagement',
                    'keyword_engagement'
                ]))
            ]);
        }
            
        // Transform audiences for API response
        $transformedAudiences = $audiences->map(function ($audience) {
            // Transform each audience
            $transformedAudience = [
                'id' => $audience->id,
                'name' => $audience->name,
                'description' => $audience->description,
                'created_at' => $audience->created_at,
                'subreddits' => []
            ];
            
            // Transform subreddits with ALL metrics
            $transformedAudience['subreddits'] = $audience->subreddits->map(function ($subreddit) {
                $data = $subreddit->subreddit_data;
                
                // Create a base subreddit object
                $transformedSubreddit = [
                    'name' => $subreddit->subreddit_name,
                    'icon_img' => $data['icon_img'] ?? null,
                    'subscribers' => $data['subscribers'] ?? 0,
                    'active_users' => $data['active_users'] ?? $data['active_user_count'] ?? 0,
                ];
                
                // Add ALL metrics that might be in the data, with proper fallbacks
                // This ensures no metrics are missed
                $metricsFields = [
                    'posts_per_day',
                    'engagement_rate',
                'growth_rate',
                    'opportunity_score',
                    'active_post_engagement',
                    'active_engagement', // Alternate name
                    'keyword_engagement',
                ];
                
                foreach ($metricsFields as $field) {
                    $transformedSubreddit[$field] = $data[$field] ?? null;
                }
                
                return $transformedSubreddit;
            });
            
            return $transformedAudience;
        });

        // Log a sample of the transformed data
        if ($transformedAudiences->count() > 0 && !empty($transformedAudiences[0]['subreddits'])) {
            \Log::debug('Sample transformed subreddit from API response:', [
                'metrics' => array_intersect_key($transformedAudiences[0]['subreddits'][0], array_flip([
                    'posts_per_day', 
                    'opportunity_score',
                    'engagement_rate',
                    'growth_rate',
                    'active_post_engagement',
                    'keyword_engagement'
                ]))
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $transformedAudiences
        ]);
    }

    /**
     * Store a new audience
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'subreddits' => 'required|array|min:1',
            'subreddits.*.name' => 'required|string',
            'subreddits.*.data' => 'required|array'
        ]);

        try {
            DB::beginTransaction();

            // Create the audience
            $audience = Auth::user()->audiences()->create([
            'name' => $request->name,
                'description' => $request->description
            ]);

            // Log the first subreddit's data to confirm metrics
            if (isset($request->subreddits[0]['data'])) {
                $firstSubreddit = $request->subreddits[0];
                \Log::debug('Saving subreddit with metrics:', [
                    'name' => $firstSubreddit['name'],
                    'posts_per_day' => $firstSubreddit['data']['posts_per_day'] ?? 'missing',
                    'opportunity_score' => $firstSubreddit['data']['opportunity_score'] ?? 'missing',
                    'engagement_rate' => $firstSubreddit['data']['engagement_rate'] ?? 'missing',
                    'growth_rate' => $firstSubreddit['data']['growth_rate'] ?? 'missing',
                    'active_post_engagement' => $firstSubreddit['data']['active_post_engagement'] ?? 'missing',
                    'active_engagement' => $firstSubreddit['data']['active_engagement'] ?? 'missing',
                    'keyword_engagement' => $firstSubreddit['data']['keyword_engagement'] ?? 'missing',
                    'calculated_metrics' => isset($firstSubreddit['data']['calculated_metrics']) ? 'present' : 'not present'
                ]);
            }

            // Add subreddits to the audience - Clean up data to avoid duplication
            foreach ($request->subreddits as $subreddit) {
                // Get the data and remove any calculated_metrics to avoid duplication
                $subredditData = $subreddit['data'];
                
                // Remove duplicate calculated_metrics object if it exists
                if (isset($subredditData['calculated_metrics'])) {
                    // Log what we're removing to verify
                    \Log::debug('Removing calculated_metrics from ' . $subreddit['name'] . ' to avoid duplication');
                    unset($subredditData['calculated_metrics']);
                }
                
                // Make sure active_post_engagement has the correct value
                if (!isset($subredditData['active_post_engagement']) || 
                    $subredditData['active_post_engagement'] === 0 || 
                    $subredditData['active_post_engagement'] === null) {
                    
                    // Try to get the value from active_engagement if available
                    if (isset($subredditData['active_engagement']) && 
                        $subredditData['active_engagement'] !== 0 && 
                        $subredditData['active_engagement'] !== null) {
                        
                        $subredditData['active_post_engagement'] = $subredditData['active_engagement'];
                        \Log::debug("Setting active_post_engagement from active_engagement: " . $subredditData['active_engagement']);
                    }
                }
                
                // Create the audience subreddit with cleaned data
                $audience->subreddits()->create([
                    'subreddit_name' => $subreddit['name'],
                    'subreddit_data' => $subredditData
                ]);
                
                // Create initial post fetch record and queue fetch job
                DB::table('audience_subreddit_posts')->insert([
                    'audience_id' => $audience->id,
                    'subreddit_name' => $subreddit['name'],
                    'posts_data' => json_encode([
                        'count' => 0,
                        'subreddit' => $subreddit['name'],
                        'fetched_at' => now()->format('Y-m-d H:i:s'),
                        'posts' => []
                    ]),
                    'fetch_status' => 'pending',
                    'fetched_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                // Queue a job to fetch posts for this subreddit
                \App\Jobs\FetchSubredditPostsJob::dispatch($audience->id, $subreddit['name'])
                    ->onQueue('posts');
            }

            DB::commit();

            // Log sample of saved data from database for verification
            $savedAudience = Audience::with('subreddits')->find($audience->id);
            if ($savedAudience && $savedAudience->subreddits->count() > 0) {
                $firstSavedSubreddit = $savedAudience->subreddits->first();
                \Log::debug('Verified saved metrics in database:', [
                    'name' => $firstSavedSubreddit->subreddit_name,
                    'metrics' => array_intersect_key($firstSavedSubreddit->subreddit_data, array_flip([
                        'posts_per_day',
                        'opportunity_score',
                        'engagement_rate',
                        'growth_rate',
                        'active_post_engagement',
                        'keyword_engagement'
                    ]))
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Audience created successfully. Post fetching has been queued.',
                'data' => [
                    'audience' => $audience->load('subreddits'),
                    'fetch_status' => 'pending'
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to create audience: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create audience: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific audience
     */
    public function show(Audience $audience)
    {
        // Check if the authenticated user owns this audience
        if ($audience->user_id !== Auth::id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        // Add debug logging to see what data is stored in the database
        \Log::debug('Retrieved audience from database:', [
            'audience_id' => $audience->id,
            'name' => $audience->name,
            'subreddits_count' => $audience->subreddits->count()
        ]);
        
        // Log the first subreddit's data as a sample
        if ($audience->subreddits->count() > 0) {
            $firstSubreddit = $audience->subreddits->first();
            \Log::debug('Sample subreddit data:', [
                'subreddit_name' => $firstSubreddit->subreddit_name,
                'metrics_in_db' => array_filter($firstSubreddit->subreddit_data, function($key) {
                    return in_array($key, [
                        'posts_per_day', 
                        'opportunity_score',
                        'engagement_rate',
                        'growth_rate',
                        'active_post_engagement',
                        'keyword_engagement'
                    ]);
                }, ARRAY_FILTER_USE_KEY)
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $audience->load('subreddits')
        ]);
    }

    /**
     * Delete an audience
     */
    public function destroy(Audience $audience)
    {
        // Check if the authenticated user owns this audience
        if ($audience->user_id !== Auth::id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
        $audience->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Audience deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete audience'
            ], 500);
        }
    }

    /**
     * Get the post fetch status for an audience
     */
    public function fetchStatus(Audience $audience)
    {
        // Check if the authenticated user owns this audience
        if ($audience->user_id !== Auth::id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }
        
        // Get post fetch status for each subreddit
        $statusData = DB::table('audience_subreddit_posts')
            ->where('audience_id', $audience->id)
            ->select('subreddit_name', 'fetch_status', 'fetched_at')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->subreddit_name => [
                    'status' => $item->fetch_status,
                    'fetched_at' => $item->fetched_at
                ]];
            });
        
        return response()->json([
            'status' => 'success',
            'data' => $statusData
        ]);
    }

    /**
     * Get the posts for an audience
     */
    public function getPosts(Audience $audience)
    {
        // Check if the authenticated user owns this audience
        if ($audience->user_id !== Auth::id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }
        
        // Get posts data for all subreddits in this audience
        $postsData = DB::table('audience_subreddit_posts')
            ->where('audience_id', $audience->id)
            ->where('fetch_status', 'completed')
            ->select('subreddit_name', 'posts_data', 'fetched_at')
            ->get()
            ->map(function ($item) {
                $data = json_decode($item->posts_data, true);
                return [
                    'subreddit_name' => $item->subreddit_name,
                    'fetched_at' => $item->fetched_at,
                    'post_count' => $data['count'] ?? 0,
                    'posts' => $data['posts'] ?? []
                ];
            });
        
        return response()->json([
            'status' => 'success',
            'data' => $postsData
        ]);
    }
}
