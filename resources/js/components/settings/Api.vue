<template>
  <div class="p-6">
    <div class="max-w-3xl mx-auto">
      <h2 class="text-xl font-semibold text-gray-900 mb-6">API Configuration</h2>
      
      <!-- Reddit API Settings -->
      <div class="bg-white rounded-lg border border-gray-200 p-6 mb-8">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Reddit API Settings</h3>
        <div class="space-y-4">
          <div>
            <label for="client-id" class="block text-sm font-medium text-gray-700 mb-2">Client ID</label>
            <input
              id="client-id"
              type="text"
              v-model="redditConfig.clientId"
              placeholder="Enter your Reddit Client ID"
              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
            >
            <p class="mt-1 text-xs text-gray-500">Found in your Reddit App preferences</p>
          </div>
          
          <div>
            <label for="client-secret" class="block text-sm font-medium text-gray-700 mb-2">Client Secret</label>
            <div class="relative">
              <input
                id="client-secret"
                :type="showSecret ? 'text' : 'password'"
                v-model="redditConfig.clientSecret"
                placeholder="Enter your Reddit Client Secret"
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              >
              <button 
                type="button" 
                @click="toggleSecret" 
                class="absolute inset-y-0 right-0 pr-3 flex items-center"
              >
                <svg 
                  xmlns="http://www.w3.org/2000/svg" 
                  class="h-5 w-5 text-gray-500" 
                  :class="{ 'text-blue-500': showSecret }"
                  fill="none" 
                  viewBox="0 0 24 24" 
                  stroke="currentColor"
                >
                  <path 
                    stroke-linecap="round" 
                    stroke-linejoin="round" 
                    stroke-width="2" 
                    :d="showSecret ? 'M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2V9z' : 'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21'"
                  />
                </svg>
              </button>
            </div>
            <p class="mt-1 text-xs text-gray-500">Keep this secret and secure</p>
          </div>
          
          <div>
            <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Reddit Username</label>
            <input
              id="username"
              type="text"
              v-model="redditConfig.username"
              placeholder="Enter your Reddit username"
              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
            >
          </div>
          
          <div class="flex items-center justify-between">
            <div>
              <h4 class="text-sm font-medium text-gray-900">API Status</h4>
              <p :class="connectionStatusClass">{{ connectionStatus }}</p>
            </div>
            <button 
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-blue-300"
              :disabled="isTestingConnection"
              @click="testConnection"
            >
              <span v-if="isTestingConnection">Testing...</span>
              <span v-else>Test Connection</span>
            </button>
          </div>
          
          <!-- Connection Feedback -->
          <div v-if="connectionMessage" :class="[
            'p-4 rounded-md', 
            connectionSuccess ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'
          ]">
            <p>{{ connectionMessage }}</p>
          </div>
        </div>
      </div>
      
      <!-- API Keys -->
      <div class="bg-white rounded-lg border border-gray-200 p-6 mb-8">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-medium text-gray-900">Your API Keys</h3>
          <button 
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
            @click="generateNewKey"
          >
            Generate New Key
          </button>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Key Name
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  API Key
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Created
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Last Used
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(key, index) in apiKeys" :key="index">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ key.name }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ maskApiKey(key.key) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-500">{{ key.created }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-500">{{ key.lastUsed }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <button 
                    class="text-blue-600 hover:text-blue-800 mr-3"
                    @click="viewKey(key)"
                  >
                    View
                  </button>
                  <button 
                    class="text-red-600 hover:text-red-800"
                    @click="revokeKey(key)"
                  >
                    Revoke
                  </button>
                </td>
              </tr>
              <tr v-if="apiKeys.length === 0">
                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                  No API keys generated yet. Click "Generate New Key" to create one.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      
      <!-- Rate Limiting -->
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Rate Limiting</h3>
        <div class="space-y-4">
          <div>
            <label for="max-requests" class="block text-sm font-medium text-gray-700 mb-2">Max Requests per Minute</label>
            <input
              id="max-requests"
              type="number"
              v-model="rateLimiting.requestsPerMinute"
              min="1"
              max="100"
              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
            >
            <p class="mt-1 text-xs text-gray-500">Reddit API allows up to 60 requests per minute</p>
          </div>
          
          <div>
            <label for="concurrent-requests" class="block text-sm font-medium text-gray-700 mb-2">Max Concurrent Requests</label>
            <input
              id="concurrent-requests"
              type="number"
              v-model="rateLimiting.concurrentRequests"
              min="1"
              max="10"
              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
            >
          </div>
          
          <div class="flex items-center justify-between">
            <div>
              <h4 class="text-sm font-medium text-gray-900">Retry Failed Requests</h4>
              <p class="text-sm text-gray-500">Automatically retry failed API requests</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="rateLimiting.retryFailedRequests" class="sr-only peer">
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
            </label>
          </div>
        </div>
      </div>
      
      <div class="mt-8 flex justify-end">
        <button 
          class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
          @click="saveSettings"
        >
          Save API Settings
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '../../stores/auth';
import axios from 'axios';

const authStore = useAuthStore();

// Reddit API Configuration
const redditConfig = ref({
  clientId: '',
  clientSecret: '',
  username: ''
});

// Show/hide secret toggle
const showSecret = ref(false);

// Connection Status
const isConnected = ref(false);
const isTestingConnection = ref(false);
const connectionMessage = ref('');
const connectionSuccess = ref(null);

// API Keys
const apiKeys = ref([]);

// Rate Limiting
const rateLimiting = ref({
  requestsPerMinute: 60,
  concurrentRequests: 5,
  retryFailedRequests: true
});

// Computed Properties
const connectionStatus = computed(() => {
  return isConnected.value ? 'Connected' : 'Not Connected';
});

const connectionStatusClass = computed(() => {
  return isConnected.value ? 'text-sm text-green-600' : 'text-sm text-red-600';
});

// Methods
const toggleSecret = () => {
  showSecret.value = !showSecret.value;
};

const testConnection = async () => {
  if (!redditConfig.value.clientId || !redditConfig.value.clientSecret || !redditConfig.value.username) {
    connectionMessage.value = 'Please fill in all required fields';
    connectionSuccess.value = false;
    return;
  }

  connectionMessage.value = 'Testing connection...';
  connectionSuccess.value = null;
  isTestingConnection.value = true;

  try {
    // Get CSRF token first
    await authStore.getCsrfToken();
    
    console.log('Testing connection with credentials:', {
      client_id: redditConfig.value.clientId,
      username: redditConfig.value.username
    });

    const response = await fetch('/api/settings/test-connection', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${authStore.token}`,
        'X-XSRF-TOKEN': authStore.getCookie('XSRF-TOKEN')
      },
      credentials: 'include',
      body: JSON.stringify({
        client_id: redditConfig.value.clientId,
        client_secret: redditConfig.value.clientSecret,
        username: redditConfig.value.username
      })
    });

    // Check if unauthorized
    if (response.status === 401) {
      console.error('Unauthorized: User not authenticated');
      connectionMessage.value = 'Authentication required. Please log in again.';
      connectionSuccess.value = false;
      isConnected.value = false;
      
      // Redirect to login after a short delay
      setTimeout(() => {
        authStore.clearAuth();
        window.location.href = '/login';
      }, 2000);
      
      return;
    }

    const data = await response.json();
    console.log('Connection test response:', data);

    if (data.success) {
      connectionMessage.value = 'Connection successful! Your Reddit API credentials are valid.';
      connectionSuccess.value = true;
      isConnected.value = true;
    } else {
      // Extract the error message without the prefix if it exists
      let errorMsg = data.message || 'Connection failed. Please check your credentials.';
      if (errorMsg.startsWith('Failed to connect to Reddit API: ')) {
        errorMsg = errorMsg.substring('Failed to connect to Reddit API: '.length);
      }
      
      connectionMessage.value = errorMsg;
      connectionSuccess.value = false;
      isConnected.value = false;
      
      console.error('Connection test failed:', data.error_details || errorMsg);
    }
  } catch (error) {
    console.error('Connection test error:', error);
    connectionMessage.value = `Connection failed: ${error.message}`;
    connectionSuccess.value = false;
    isConnected.value = false;
  } finally {
    isTestingConnection.value = false;
  }
};

const saveSettings = async () => {
  try {
    // Get CSRF token first
    await authStore.getCsrfToken();
    
    connectionMessage.value = 'Saving settings...';
    connectionSuccess.value = null;
    
    const response = await fetch('/api/settings/api', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${authStore.token}`,
        'X-XSRF-TOKEN': authStore.getCookie('XSRF-TOKEN')
      },
      credentials: 'include',
      body: JSON.stringify({
        redditConfig: {
          clientId: redditConfig.value.clientId,
          clientSecret: redditConfig.value.clientSecret,
          username: redditConfig.value.username
        },
        rateLimiting: {
          requestsPerMinute: parseInt(rateLimiting.value.requestsPerMinute),
          concurrentRequests: parseInt(rateLimiting.value.concurrentRequests),
          retryFailedRequests: Boolean(rateLimiting.value.retryFailedRequests),
        }
      })
    });

    // Check if unauthorized
    if (response.status === 401) {
      console.error('Unauthorized: User not authenticated');
      connectionMessage.value = 'Authentication required. Please log in again.';
      connectionSuccess.value = false;
      
      // Redirect to login after a short delay
      setTimeout(() => {
        authStore.clearAuth();
        window.location.href = '/login';
      }, 2000);
      
      return;
    }

    const data = await response.json();
    console.log('Save settings response:', data);
    
    if (!response.ok) {
      throw new Error(data.message || 'Failed to save settings');
    }

    // Update connection status
    isConnected.value = true;
    connectionMessage.value = 'Settings saved successfully!';
    connectionSuccess.value = true;

    // Load the saved settings
    await loadSettings();
  } catch (error) {
    console.error('Save settings error:', error);
    connectionMessage.value = error.message || 'Failed to save settings';
    connectionSuccess.value = false;
  }
};

const loadSettings = async () => {
  try {
    // Check if user is authenticated
    if (!authStore.isAuthenticated) {
      console.warn('User is not authenticated, skipping settings load');
      return;
    }
    
    // Get CSRF token first
    await authStore.getCsrfToken();
    
    console.log('Loading settings...');
    
    const response = await fetch('/api/settings/api', {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${authStore.token}`,
        'X-XSRF-TOKEN': authStore.getCookie('XSRF-TOKEN')
      },
      credentials: 'include'
    });

    // Check if unauthorized
    if (response.status === 401) {
      console.error('Unauthorized: User not authenticated');
      // Don't show error message to user during initial load
      // Just redirect to login after a short delay
      setTimeout(() => {
        authStore.clearAuth();
        window.location.href = '/login';
      }, 1000);
      
      return;
    }

    const data = await response.json();
    
    if (!response.ok) {
      console.error('Failed to load settings:', data);
      throw new Error(data.message || 'Failed to load settings');
    }

    console.log('Loaded settings:', data);
    
    // Update Reddit config
    if (data.redditConfig) {
      redditConfig.value = {
        clientId: data.redditConfig.clientId || '',
        clientSecret: '',  // Don't set the secret from the server
        username: data.redditConfig.username || '',
      };
      isConnected.value = data.redditConfig.isConnected;
    }

    // Update rate limiting
    if (data.rateLimiting) {
      rateLimiting.value = {
        requestsPerMinute: data.rateLimiting.requestsPerMinute,
        concurrentRequests: data.rateLimiting.concurrentRequests,
        retryFailedRequests: data.rateLimiting.retryFailedRequests,
      };
    }

    // Update API keys
    if (data.apiKeys) {
      apiKeys.value = data.apiKeys;
    }
  } catch (error) {
    console.error('Load settings error:', error);
    // Don't show error message to user during initial load
    // connectionMessage.value = error.message || 'Failed to load settings';
    // connectionSuccess.value = false;
  }
};

const generateNewKey = async () => {
  try {
    // Get CSRF token first
    await authStore.getCsrfToken();
    
    const keyName = prompt('Enter a name for your API key:', `API Key ${apiKeys.value.length + 1}`);
    
    if (!keyName) return;
    
    console.log('Generating new API key:', keyName);
    
    const response = await fetch('/api/settings/generate-key', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${authStore.token}`,
        'X-XSRF-TOKEN': authStore.getCookie('XSRF-TOKEN')
      },
      credentials: 'include',
      body: JSON.stringify({ name: keyName })
    });
    
    // Check if unauthorized
    if (response.status === 401) {
      console.error('Unauthorized: User not authenticated');
      alert('Authentication required. Please log in again.');
      
      // Redirect to login after a short delay
      setTimeout(() => {
        authStore.clearAuth();
        window.location.href = '/login';
      }, 1000);
      
      return;
    }
    
    const data = await response.json();
    console.log('Generate key response:', data);
    
    if (!response.ok) {
      throw new Error(data.message || 'Failed to generate API key');
    }
    
    // Add the new key to the list
    apiKeys.value.push({
      id: data.id,
      name: data.name,
      key: data.key,
      created: data.created,
      lastUsed: data.lastUsed
    });
    
    // Show the full key to the user once
    alert(`Your new API key: ${data.key}\n\nPlease save this key as it won't be fully displayed again.`);
  } catch (error) {
    console.error('Generate key error:', error);
    alert(`Failed to generate API key: ${error.message || 'Unknown error'}`);
  }
};

const viewKey = async (key) => {
  try {
    // Get CSRF token first
    await authStore.getCsrfToken();
    
    const response = await fetch(`/api/settings/key/${key.id}`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${authStore.token}`,
        'X-XSRF-TOKEN': authStore.getCookie('XSRF-TOKEN')
      },
      credentials: 'include'
    });
    
    // Check if unauthorized
    if (response.status === 401) {
      console.error('Unauthorized: User not authenticated');
      alert('Authentication required. Please log in again.');
      
      // Redirect to login after a short delay
      setTimeout(() => {
        authStore.clearAuth();
        window.location.href = '/login';
      }, 1000);
      
      return;
    }
    
    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.message || 'Failed to retrieve API key');
    }
    
    alert(`API Key: ${data.key}`);
  } catch (error) {
    console.error('View key error:', error);
    alert(`Failed to retrieve API key: ${error.message || 'Unknown error'}`);
  }
};

const revokeKey = async (key) => {
  if (!confirm(`Are you sure you want to revoke the key "${key.name}"?`)) {
    return;
  }
  
  try {
    // Get CSRF token first
    await authStore.getCsrfToken();
    
    const response = await fetch(`/api/settings/revoke-key/${key.id}`, {
      method: 'DELETE',
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${authStore.token}`,
        'X-XSRF-TOKEN': authStore.getCookie('XSRF-TOKEN')
      },
      credentials: 'include'
    });
    
    // Check if unauthorized
    if (response.status === 401) {
      console.error('Unauthorized: User not authenticated');
      alert('Authentication required. Please log in again.');
      
      // Redirect to login after a short delay
      setTimeout(() => {
        authStore.clearAuth();
        window.location.href = '/login';
      }, 1000);
      
      return;
    }
    
    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.message || 'Failed to revoke API key');
    }
    
    // Remove the key from the list
    apiKeys.value = apiKeys.value.filter(k => k.id !== key.id);
  } catch (error) {
    console.error('Revoke key error:', error);
    alert(`Failed to revoke API key: ${error.message || 'Unknown error'}`);
  }
};

const maskApiKey = (key) => {
  if (!key) return '';
  return '•'.repeat(key.length - 4) + key.slice(-4);
};

// Call loadSettings when component mounts
onMounted(async () => {
  // Check if user is authenticated before loading settings
  if (authStore.isAuthenticated) {
    await loadSettings();
  } else {
    console.warn('User is not authenticated, skipping initial settings load');
  }
});
</script>