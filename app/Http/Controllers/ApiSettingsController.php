<?php

namespace App\Http\Controllers;

use App\Models\ApiCredential;
use App\Models\ApiKey;
use App\Models\ApiRateLimit;
use App\Services\RedditApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class ApiSettingsController extends Controller
{
    /**
     * Get the API settings for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Get the authenticated user
        $user = Auth::user();
        
        // If no authenticated user, return error
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        
        $redditCredentials = $user->redditCredentials;
        $redditRateLimits = $user->redditRateLimits;
        $apiKeys = $user->apiKeys;
        
        return response()->json([
            'redditConfig' => $redditCredentials ? [
                'clientId' => $redditCredentials->client_id,
                'clientSecret' => null, // Don't send the secret to the frontend
                'username' => $redditCredentials->username,
                'userAgent' => $redditCredentials->user_agent,
                'isConnected' => $redditCredentials->is_connected,
                'lastConnectedAt' => $redditCredentials->last_connected_at,
            ] : null,
            'rateLimiting' => $redditRateLimits ? [
                'requestsPerMinute' => $redditRateLimits->requests_per_minute,
                'concurrentRequests' => $redditRateLimits->concurrent_requests,
                'retryFailedRequests' => $redditRateLimits->retry_failed_requests,
            ] : [
                'requestsPerMinute' => 60,
                'concurrentRequests' => 5,
                'retryFailedRequests' => true,
            ],
            'apiKeys' => $apiKeys->map(function ($key) {
                // Get last 4 characters of the key
                $maskedKey = str_repeat('•', strlen($key->key) - 4) . substr($key->key, -4);
                
                return [
                    'id' => $key->id,
                    'name' => $key->name,
                    'key' => $maskedKey, // Show masked version of the key
                    'created' => $key->created_at->toDateString(),
                    'lastUsed' => $key->last_used_at ? $key->last_used_at->toDateString() : 'Never',
                ];
            }),
        ]);
    }

    /**
     * Save the API settings for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'redditConfig.clientId' => 'required|string',
                'redditConfig.clientSecret' => 'required|string',
                'redditConfig.username' => 'required|string',
                'rateLimiting.requestsPerMinute' => 'required|integer|min:1|max:100',
                'rateLimiting.concurrentRequests' => 'required|integer|min:1|max:10',
                'rateLimiting.retryFailedRequests' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Get the authenticated user
            $user = Auth::user();
            
            // If no authenticated user, return error
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }
            
            // Construct user agent from base user agent and username
            $baseUserAgent = 'MyApp/0.1';
            $username = $request->input('redditConfig.username');
            $userAgent = "{$baseUserAgent} by {$username}";
            
            // Create temporary credentials for testing
            $tempCredentials = new ApiCredential([
                'user_id' => $user->id,
                'provider' => 'reddit',
                'client_id' => $request->input('redditConfig.clientId'),
                'client_secret' => $request->input('redditConfig.clientSecret'),
                'username' => $username,
                'user_agent' => $userAgent,
            ]);
            
            // Test the connection before saving
            $redditService = new RedditApiService();
            $redditService->setCredentials($tempCredentials);
            
            try {
                $connectionSuccess = $redditService->testConnection();
            } catch (\Exception $e) {
                \Log::error('Connection test failed during save: ' . $e->getMessage());
                $connectionSuccess = false;
            }
            
            // Save Reddit API credentials
            $redditCredentials = $user->redditCredentials;
            
            if (!$redditCredentials) {
                $redditCredentials = new ApiCredential([
                    'user_id' => $user->id,
                    'provider' => 'reddit',
                ]);
            }
            
            $redditCredentials->fill([
                'client_id' => $request->input('redditConfig.clientId'),
                'client_secret' => $request->input('redditConfig.clientSecret'),
                'username' => $username,
                'user_agent' => $userAgent,
                'is_connected' => $connectionSuccess,
                'last_connected_at' => $connectionSuccess ? now() : null,
            ]);
            
            $redditCredentials->save();
            
            // Save rate limiting settings
            $redditRateLimits = $user->redditRateLimits;
            
            if (!$redditRateLimits) {
                $redditRateLimits = new ApiRateLimit([
                    'user_id' => $user->id,
                    'provider' => 'reddit',
                ]);
            }
            
            $redditRateLimits->fill([
                'requests_per_minute' => $request->input('rateLimiting.requestsPerMinute'),
                'concurrent_requests' => $request->input('rateLimiting.concurrentRequests'),
                'retry_failed_requests' => $request->input('rateLimiting.retryFailedRequests'),
            ]);
            
            $redditRateLimits->save();
            
            // Generate a default API key if none exists
            if ($user->apiKeys->isEmpty()) {
                $apiKey = new ApiKey([
                    'user_id' => $user->id,
                    'name' => 'Default Key',
                    'key' => ApiKey::generateKey(),
                ]);
                $apiKey->save();
            }
            
            // Refresh the user's data to get the latest changes
            $user->refresh();
            
            return response()->json([
                'message' => 'API settings saved successfully',
                'redditConfig' => [
                    'clientId' => $redditCredentials->client_id,
                    'username' => $redditCredentials->username,
                    'userAgent' => $redditCredentials->user_agent,
                    'isConnected' => $redditCredentials->is_connected,
                    'lastConnectedAt' => $redditCredentials->last_connected_at,
                ],
                'rateLimiting' => [
                    'requestsPerMinute' => $redditRateLimits->requests_per_minute,
                    'concurrentRequests' => $redditRateLimits->concurrent_requests,
                    'retryFailedRequests' => $redditRateLimits->retry_failed_requests,
                ],
                'apiKeys' => $user->apiKeys->map(function ($key) {
                    return [
                        'id' => $key->id,
                        'name' => $key->name,
                        'key' => null, // Don't send the actual key to the frontend
                        'created' => $key->created_at->toDateString(),
                        'lastUsed' => $key->last_used_at ? $key->last_used_at->toDateString() : 'Never',
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to save API settings: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to save API settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test the Reddit API connection.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function testConnection(Request $request)
    {
        try {
            // Get the authenticated user
            $user = Auth::user();
            
            // If no authenticated user, return error
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }
            
            $validator = Validator::make($request->all(), [
                'client_id' => 'required|string',
                'client_secret' => 'required|string',
                'username' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Validation failed: ' . implode(', ', $validator->errors()->all())
                ], 422);
            }

            // Construct user agent from base user agent and username
            $baseUserAgent = 'MyApp/0.1';
            $username = $request->input('username');
            $userAgent = "{$baseUserAgent} by {$username}";

            // Create temporary credentials for testing
            $credentials = new ApiCredential([
                'user_id' => $user->id, // Use the authenticated user's ID
                'provider' => 'reddit',
                'client_id' => $request->input('client_id'),
                'client_secret' => $request->input('client_secret'),
                'username' => $username,
                'user_agent' => $userAgent,
            ]);
            
            // Test the connection
            $redditService = new RedditApiService();
            $redditService->setCredentials($credentials);
            
            try {
                $success = $redditService->testConnection();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Successfully connected to Reddit API',
                ]);
            } catch (\Exception $e) {
                \Log::error('Reddit API connection test error: ' . $e->getMessage());
                
                // Extract more user-friendly error message
                $errorMessage = $e->getMessage();
                
                // Handle common curl errors
                if (strpos($errorMessage, 'cURL Error') !== false) {
                    if (strpos($errorMessage, 'Could not resolve host') !== false) {
                        $errorMessage = 'Could not connect to Reddit API. Please check your internet connection.';
                    } else if (strpos($errorMessage, 'SSL certificate problem') !== false) {
                        $errorMessage = 'SSL certificate verification failed. This is usually a local configuration issue.';
                    } else if (strpos($errorMessage, 'Operation timed out') !== false) {
                        $errorMessage = 'Connection to Reddit API timed out. Please try again later.';
                    }
                }
                // Handle common Reddit API errors
                else if (strpos($errorMessage, 'invalid_grant') !== false) {
                    $errorMessage = 'Invalid credentials. Please check your Client ID and Client Secret.';
                } else if (strpos($errorMessage, 'unsupported_grant_type') !== false) {
                    $errorMessage = 'The Reddit API does not support this authentication method.';
                } else if (strpos($errorMessage, 'invalid_client') !== false) {
                    $errorMessage = 'Invalid Client ID or Client Secret. Please check your credentials.';
                } else if (strpos($errorMessage, 'Connection refused') !== false) {
                    $errorMessage = 'Could not connect to Reddit API. Please check your internet connection.';
                }
                
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to Reddit API: ' . $errorMessage,
                    'error_details' => $e->getMessage()
                ], 400);
            }
        } catch (\Exception $e) {
            \Log::error('API Settings controller error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred: ' . $e->getMessage(),
                'error_details' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Generate a new API key for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateKey(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Get the authenticated user
        $user = Auth::user();
        
        // If no authenticated user, return error
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        
        $apiKey = new ApiKey([
            'user_id' => $user->id,
            'name' => $request->input('name'),
            'key' => ApiKey::generateKey(),
        ]);
        
        $apiKey->save();
        
        return response()->json([
            'id' => $apiKey->id,
            'name' => $apiKey->name,
            'key' => $apiKey->key, // Only time we send the full key
            'created' => $apiKey->created_at->toDateString(),
            'lastUsed' => 'Never',
        ]);
    }

    /**
     * Get a specific API key for the authenticated user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getKey($id)
    {
        // Get the authenticated user
        $user = Auth::user();
        
        // If no authenticated user, return error
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        
        $apiKey = $user->apiKeys()->findOrFail($id);
        
        return response()->json([
            'id' => $apiKey->id,
            'name' => $apiKey->name,
            'key' => $apiKey->key,
            'created' => $apiKey->created_at->toDateString(),
            'lastUsed' => $apiKey->last_used_at ? $apiKey->last_used_at->toDateString() : 'Never',
        ]);
    }

    /**
     * Revoke a specific API key for the authenticated user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function revokeKey($id)
    {
        // Get the authenticated user
        $user = Auth::user();
        
        // If no authenticated user, return error
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        
        $apiKey = $user->apiKeys()->findOrFail($id);
        
        $apiKey->delete();
        
        return response()->json([
            'message' => 'API key revoked successfully',
        ]);
    }
} 