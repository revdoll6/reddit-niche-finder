// Test script for search logging endpoints

// Simple function to fetch CSRF token
async function getCSRFToken() {
    try {
        const response = await fetch('/sanctum/csrf-cookie', {
            method: 'GET',
            credentials: 'include'
        });
        return document.cookie.split('; ')
            .find(row => row.startsWith('XSRF-TOKEN='))
            ?.split('=')[1];
    } catch (error) {
        console.error('Error fetching CSRF token:', error);
        return null;
    }
}

// Test the search logging endpoints
async function testSearchLogging() {
    try {
        // Get CSRF token for Laravel
        const csrfToken = await getCSRFToken();
        if (!csrfToken) {
            console.error('Failed to get CSRF token');
            return;
        }
        
        console.log('CSRF Token obtained:', csrfToken);
        
        // Create test data
        const testData = {
            query: 'test query',
            results: [
                {
                    id: 'test1',
                    name: 'test1',
                    display_name: 'Test Subreddit 1',
                    subscribers: 1000,
                    description: 'Test description 1'
                },
                {
                    id: 'test2',
                    name: 'test2',
                    display_name: 'Test Subreddit 2',
                    subscribers: 2000,
                    description: 'Test description 2'
                }
            ],
            timestamp: new Date().toISOString(),
            total_results: 2
        };
        
        console.log('Test payload prepared:', testData);
        
        // First test the debug endpoint
        console.log('Testing debug endpoint...');
        const debugResponse = await fetch('/api/debug/log-search', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify(testData)
        });
        
        const debugResult = await debugResponse.json();
        console.log('Debug endpoint response:', debugResult);
        
        // Then test the actual endpoint
        console.log('Testing actual search log endpoint...');
        const searchResponse = await fetch('/api/log/search', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify(testData)
        });
        
        const searchResult = await searchResponse.json();
        console.log('Search log endpoint response:', searchResult);
        console.log('Status code:', searchResponse.status);
        
        // Report overall result
        if (searchResponse.ok) {
            console.log('✅ Search logging test SUCCESSFUL');
        } else {
            console.log('❌ Search logging test FAILED');
        }
    } catch (error) {
        console.error('Error during test:', error);
    }
}

// Run the test
testSearchLogging();

// Export for use in console if needed
export { testSearchLogging }; 