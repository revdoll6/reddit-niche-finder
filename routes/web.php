<?php

use Illuminate\Support\Facades\Route;

// Catch all routes and direct them to the Vue app
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');

// Debug route for testing Reddit API connection
Route::get('/debug/test-reddit-api', function () {
    // Your Reddit API credentials
    $clientId = 'kkb3ZVEGF5hwlrtNqjjq5A';
    $clientSecret = 'jqnUTNusqKp2BSI4VPtz9P4ojvRGuw';
    $username = 'NeedleworkerFuzzy314';
    $baseUserAgent = 'MyApp/0.1';
    $userAgent = $baseUserAgent . ' by ' . $username;

    echo "<h1>Testing Reddit API Connection</h1>";
    echo "<p>Step 1: Getting access token...</p>";

    // Initialize curl for token request
    $ch = curl_init();

    // Set curl options for token request
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://www.reddit.com/api/v1/access_token',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
        CURLOPT_SSL_VERIFYPEER => false,  // Disable SSL verification for testing
        CURLOPT_HTTPHEADER => [
            'User-Agent: ' . $userAgent
        ],
        CURLOPT_USERPWD => $clientId . ':' . $clientSecret
    ]);

    // Execute token request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    $curlErrno = curl_errno($ch);

    echo "<p>Token Response (HTTP $httpCode):</p>";
    echo "<pre>";
    if ($response === false) {
        echo "cURL Error ($curlErrno): $curlError";
    } else {
        echo htmlspecialchars($response);
    }
    echo "</pre>";

    // Parse response
    $data = json_decode($response, true);

    if (isset($data['access_token'])) {
        echo "<p>✅ Access token received!</p>";
        $accessToken = $data['access_token'];
        
        echo "<p>Step 2: Testing API endpoint...</p>";
        
        // Reset curl options for API request
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://oauth.reddit.com/api/v1/me',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => false,
            CURLOPT_SSL_VERIFYPEER => false,  // Disable SSL verification for testing
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken,
                'User-Agent: ' . $userAgent
            ]
        ]);
        
        // Execute API request
        $apiResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        $curlErrno = curl_errno($ch);
        
        echo "<p>API Response (HTTP $httpCode):</p>";
        echo "<pre>";
        if ($apiResponse === false) {
            echo "cURL Error ($curlErrno): $curlError";
        } else {
            echo htmlspecialchars($apiResponse);
        }
        echo "</pre>";
        
        if ($httpCode === 200) {
            echo "<p>✅ API test successful!</p>";
        } else {
            echo "<p>❌ API test failed!</p>";
        }
    } else {
        echo "<p>❌ Failed to get access token!</p>";
        if (isset($data['error'])) {
            echo "<p>Error: " . htmlspecialchars($data['error']) . "</p>";
            if (isset($data['error_description'])) {
                echo "<p>Description: " . htmlspecialchars($data['error_description']) . "</p>";
            }
        }
    }

    // Clean up
    curl_close($ch);
    
    return "";
});
