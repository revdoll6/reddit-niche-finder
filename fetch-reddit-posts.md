# Fetching Reddit Posts: Implementation Guide

This document provides an in-depth explanation of how Reddit posts are fetched in the `Explorer.vue` component, and how to properly implement this approach in PHP applications.

## Table of Contents
1. [Overview](#overview)
2. [Authentication](#authentication)
3. [Fetching Posts](#fetching-posts)
4. [Rate Limiting](#rate-limiting)
5. [Error Handling](#error-handling)
6. [Implementation in PHP](#implementation-in-php)
7. [Common Issues & Solutions](#common-issues--solutions)

## Overview

The `Explorer.vue` component uses JavaScript's fetch API to retrieve data from Reddit's API. The process involves:

1. Obtaining an access token using client credentials
2. Using the token to make authenticated requests to Reddit's API endpoints
3. Processing the responses and extracting post data
4. Handling rate limits and errors gracefully

## Authentication

Authentication with Reddit's API is performed using the OAuth 2.0 client credentials flow:

```javascript
// From Explorer.vue
const tokenResponse = await rateLimitFetch('https://www.reddit.com/api/v1/access_token', {
  method: 'POST',
  headers: {
    'Authorization': 'Basic ' + btoa('kkb3ZVEGF5hwlrtNqjjq5A:jqnUTNusqKp2BSI4VPtz9P4ojvRGuw'),
    'Content-Type': 'application/x-www-form-urlencoded',
    'User-Agent': 'MyApp/0.1 by NeedleworkerFuzzy314'
  },
  body: 'grant_type=client_credentials'
});

const tokenData = await tokenResponse.json();
    
if (!tokenData.access_token) {
  throw new Error('Failed to get access token');
}
```

Key points:
- The Authorization header uses Basic authentication with client ID and secret encoded in base64
- The Content-Type is set to `application/x-www-form-urlencoded`
- The User-Agent header is required by Reddit's API
- The request body contains `grant_type=client_credentials`

## Fetching Posts

Once an access token is obtained, it's used to fetch posts from subreddits:

```javascript
// From Explorer.vue - fetchSubredditPostsBatch function
const newPostsResponse = await fetchFn(`https://oauth.reddit.com/r/${subredditName}/new?limit=100`, {
  headers: {
    'Authorization': `Bearer ${accessToken}`,
    'User-Agent': 'MyApp/0.1 by NeedleworkerFuzzy314'
  }
});

// Parse JSON response
const newPostsData = await newPostsResponse.json();

// Extract post data
const posts = (newPostsData.data?.children || []).map(child => child.data);
```

Key points:
- Requests are made to `https://oauth.reddit.com/r/{subredditName}/new`
- The Authorization header uses the Bearer token scheme with the access token
- The limit parameter is set to 100 to get the maximum number of posts per request
- Posts are extracted from the `data.children` array in the response, and each child's `data` property is taken

## Rate Limiting

Reddit imposes rate limits on API requests. The `Explorer.vue` component handles this by tracking rate limit information from response headers:

```javascript
// From Explorer.vue - rateLimitFetch function
const rateLimitFetch = async (url, options) => {
  try {
    const response = await fetch(url, options);
    
    // Track rate limit info from headers for internal use
    const used = response.headers.get('X-Ratelimit-Used');
    const remaining = response.headers.get('X-Ratelimit-Remaining');
    const reset = response.headers.get('X-Ratelimit-Reset');
    
    if (used) rateLimitUsed.value = parseInt(used);
    if (remaining) rateLimitRemaining.value = parseInt(remaining);
    
    if (reset) {
      const resetValue = parseInt(reset);
      rateLimitResetInitial.value = resetValue;
      rateLimitReset.value = resetValue;
    }
    
    rateLimitTimestamp.value = Date.now();
    
    // Keep the timer to track rate limits internally
    startRateLimitTimer();
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    return response;
  } catch (error) {
    console.error('Fetch error:', error);
    throw error;
  }
};
```

Key points:
- Response headers contain rate limit information
- The application tracks used requests, remaining requests, and reset time
- Timers are used to calculate when rate limits will reset

## Error Handling

The application handles errors at multiple levels:

```javascript
// From Explorer.vue - fetchSubredditPostsBatch function
try {
  // Make request...
  
  // Check for error messages in the response
  if (newPostsData.error) {
    console.error(`API Error for r/${subredditName}:`);
    console.error(`- New posts error: ${newPostsData.error || 'none'}, message: ${newPostsData.message || 'none'}`);
  }
  
  // Process data...
} catch (err) {
  console.error(`Error fetching posts for ${subredditName}:`, err);
  console.error(`Error details: ${err.message}`);
  console.error(`Error stack: ${err.stack}`);
  return { subredditName, posts: [] };
}
```

Key points:
- HTTP errors are caught and logged
- Error messages in successful responses are detected and handled
- In case of errors, empty arrays are returned instead of failing the entire batch

## Implementation in PHP

When implementing this approach in PHP, there are two main options:

### Option 1: Using Laravel's HTTP Client

```php
// Get access token
$response = Http::withHeaders([
    'Authorization' => 'Basic ' . base64_encode('kkb3ZVEGF5hwlrtNqjjq5A:jqnUTNusqKp2BSI4VPtz9P4ojvRGuw'),
    'Content-Type' => 'application/x-www-form-urlencoded',
    'User-Agent' => 'MyApp/0.1 by NeedleworkerFuzzy314'
])->post('https://www.reddit.com/api/v1/access_token', [
    'grant_type' => 'client_credentials'
]);

if ($response->failed()) {
    throw new \Exception("Failed to get access token. HTTP code: {$response->status()}");
}

$tokenData = $response->json();
if (!isset($tokenData['access_token'])) {
    throw new \Exception('Access token not found in response');
}

$accessToken = $tokenData['access_token'];

// Fetch posts
$response = Http::withHeaders([
    'Authorization' => "Bearer {$accessToken}",
    'User-Agent' => 'MyApp/0.1 by NeedleworkerFuzzy314'
])->get("https://oauth.reddit.com/r/{$subredditName}/new?limit=100");

if ($response->failed()) {
    throw new \Exception("Failed to fetch posts. HTTP code: {$response->status()}");
}

$data = $response->json();
$posts = array_map(function($child) {
    return $child['data'];
}, $data['data']['children'] ?? []);
```

### Option 2: Using PHP's cURL Functions

```php
// Get access token
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://www.reddit.com/api/v1/access_token',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
    CURLOPT_HTTPHEADER => [
        'Authorization: Basic ' . base64_encode('kkb3ZVEGF5hwlrtNqjjq5A:jqnUTNusqKp2BSI4VPtz9P4ojvRGuw'),
        'Content-Type: application/x-www-form-urlencoded',
        'User-Agent: MyApp/0.1 by NeedleworkerFuzzy314'
    ],
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => 0
]);

$tokenResponse = curl_exec($ch);
$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($statusCode !== 200) {
    throw new \Exception("Failed to get access token. HTTP code: {$statusCode}");
}

$tokenData = json_decode($tokenResponse, true);
if (!isset($tokenData['access_token'])) {
    throw new \Exception('Access token not found in response');
}

$accessToken = $tokenData['access_token'];

// Fetch posts
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => "https://oauth.reddit.com/r/{$subredditName}/new?limit=100",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer {$accessToken}",
        'User-Agent: MyApp/0.1 by NeedleworkerFuzzy314'
    ],
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => 0
]);

$postsResponse = curl_exec($ch);
$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($statusCode !== 200) {
    throw new \Exception("Failed to fetch posts. HTTP code: {$statusCode}");
}

$data = json_decode($postsResponse, true);
$posts = array_map(function($child) {
    return $child['data'];
}, $data['data']['children'] ?? []);
```

## Common Issues & Solutions

### 1. Method Chaining Order in Laravel

When using Laravel's HTTP client, the order of method chaining is important:

```php
// CORRECT:
$response = Http::withHeaders([
    // headers...
])->post('https://example.com', [
    // data...
]);

// INCORRECT:
$response = Http::post('https://example.com', [
    // data...
])->withHeaders([
    // headers...
]);
```

The incorrect order will cause a "Call to undefined method GuzzleHttp\Psr7\Response::withHeaders()" error because withHeaders() should be called before making the request.

### 2. SSL Certificate Verification

Reddit's API may have SSL certificate verification issues with some PHP setups. Options to handle this:

```php
// With Laravel HTTP client:
$response = Http::withoutVerifying()->withHeaders([
    // headers...
])->post('https://www.reddit.com/api/v1/access_token', [
    // data...
]);

// With curl:
curl_setopt_array($ch, [
    // other options...
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => 0
]);
```

### 3. Rate Limiting

To handle rate limits in PHP, store and check rate limit headers:

```php
// With Laravel HTTP client:
$rateLimit = [
    'used' => $response->header('X-Ratelimit-Used'),
    'remaining' => $response->header('X-Ratelimit-Remaining'),
    'reset' => $response->header('X-Ratelimit-Reset')
];

// With curl:
$rateLimit = [
    'used' => curl_getinfo($ch, CURLINFO_HEADER_OUT) // Parse headers from this
];

// If rate limit is reached, delay next request
if (isset($rateLimit['remaining']) && $rateLimit['remaining'] < 5) {
    sleep(5); // Wait 5 seconds before next request
}
```

### 4. Proper Error Handling

Always check for both HTTP errors and application-level errors in the JSON response:

```php
// Check HTTP status
if ($response->failed()) {
    throw new \Exception("HTTP error: {$response->status()}");
}

// Check JSON response for errors
$data = $response->json();
if (isset($data['error'])) {
    throw new \Exception("API error: {$data['error']} - {$data['message']}");
}
```

## Conclusion

When fetching Reddit posts in a PHP application, follow these key principles:

1. Use the correct authentication flow with proper headers
2. Make sure method chaining is in the correct order when using Laravel's HTTP client
3. Handle SSL certificate verification based on your environment
4. Respect and track rate limits
5. Implement robust error handling at multiple levels

By following this guide, you can implement Reddit API integration that closely matches the successful implementation in Explorer.vue. 