<?php

// Your Reddit API credentials
$clientId = 'kkb3ZVEGF5hwlrtNqjjq5A';
$clientSecret = 'jqnUTNusqKp2BSI4VPtz9P4ojvRGuw';
$username = 'NeedleworkerFuzzy314';
$baseUserAgent = 'MyApp/0.1';
$userAgent = $baseUserAgent . ' by ' . $username;

echo "🔍 Testing Reddit API Connection...\n";
echo "Step 1: Getting access token...\n";

// Initialize curl for token request
$ch = curl_init();

// Set curl options for token request
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://www.reddit.com/api/v1/access_token',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
    CURLOPT_SSL_VERIFYPEER => false,  // Only for testing
    CURLOPT_HTTPHEADER => [
        'User-Agent: ' . $userAgent
    ],
    CURLOPT_USERPWD => $clientId . ':' . $clientSecret
]);

// Execute token request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "Token Response (HTTP $httpCode):\n";
echo $response . "\n\n";

// Parse response
$data = json_decode($response, true);

if (isset($data['access_token'])) {
    echo "✅ Access token received!\n";
    $accessToken = $data['access_token'];
    
    echo "Step 2: Testing API endpoint...\n";
    
    // Reset curl options for API request
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://oauth.reddit.com/api/v1/me',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => false,
        CURLOPT_SSL_VERIFYPEER => false,  // Only for testing
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $accessToken,
            'User-Agent: ' . $userAgent
        ]
    ]);
    
    // Execute API request
    $apiResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    echo "API Response (HTTP $httpCode):\n";
    echo $apiResponse . "\n";
    
    if ($httpCode === 200) {
        echo "✅ API test successful!\n";
    } else {
        echo "❌ API test failed!\n";
    }
} else {
    echo "❌ Failed to get access token!\n";
    if (isset($data['error'])) {
        echo "Error: " . $data['error'] . "\n";
        if (isset($data['error_description'])) {
            echo "Description: " . $data['error_description'] . "\n";
        }
    }
}

// Clean up
curl_close($ch); 