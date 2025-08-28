<template>
  <div class="min-h-screen bg-gray-900">
    <!-- Top Navigation Bar -->
    <div class="bg-gray-800 border-b border-gray-700 sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex-1 flex items-center">
            <input
              type="text"
              v-model="searchQuery"
              placeholder="Search subreddits (e.g., coffee, gaming, fitness)..."
              class="w-full max-w-2xl px-4 py-2 bg-gray-700 border-0 rounded-full text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:bg-gray-600"
              @keyup.enter="initiateSearch"
            />
          <button 
              @click="initiateSearch"
              class="ml-2 px-6 py-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition-colors disabled:bg-blue-800 disabled:text-gray-300 flex items-center"
              :disabled="loading || !searchQuery"
            >
              <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>{{ loading ? 'Searching...' : 'Search' }}</span>
          </button>
          </div>
          
          <!-- Remove the rate limit information from here as well -->
          </div>
          </div>
        </div>
        
    <!-- Horizontal Filters Bar -->
    <div class="bg-gray-800 border-b border-gray-700 py-4 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto flex flex-wrap items-center gap-4">
            <!-- Subscriber Range -->
        <div class="flex items-center gap-2 min-w-[200px]">
          <label class="text-sm font-medium text-gray-300">
            Min. Subscribers:
              </label>
                <input
                  type="range"
            v-model="filters.minMembers"
                  min="0"
                  max="10000000"
                  step="1000"
            class="w-32 accent-blue-600"
                />
          <span class="text-sm text-gray-400 w-20">
            {{ formatNumber(filters.minMembers) }}
                </span>
              </div>
              
            <!-- Activity Level -->
        <div class="flex items-center gap-2">
          <label class="text-sm font-medium text-gray-300">Activity:</label>
          <select
            v-model="filters.activityLevel"
            class="bg-gray-700 border-gray-600 text-white rounded-md text-sm focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">Any</option>
            <option value="high">High (50+ posts/day)</option>
            <option value="moderate">Medium (10-50 posts/day)</option>
            <option value="low">Low (< 10 posts/day)</option>
          </select>
      </div>
      
            <!-- Content Types -->
        <div class="flex items-center gap-2">
          <label class="text-sm font-medium text-gray-300">Content:</label>
          <div class="flex gap-2">
            <label class="inline-flex items-center">
              <input type="checkbox" v-model="filters.contentTypes.text" class="text-blue-600 bg-gray-700 border-gray-600 rounded">
              <span class="ml-1 text-sm text-gray-300">Text</span>
              </label>
            <label class="inline-flex items-center">
              <input type="checkbox" v-model="filters.contentTypes.images" class="text-blue-600 bg-gray-700 border-gray-600 rounded">
              <span class="ml-1 text-sm text-gray-300">Images</span>
              </label>
            <label class="inline-flex items-center">
              <input type="checkbox" v-model="filters.contentTypes.videos" class="text-blue-600 bg-gray-700 border-gray-600 rounded">
              <span class="ml-1 text-sm text-gray-300">Videos</span>
              </label>
            <label class="inline-flex items-center">
              <input type="checkbox" v-model="filters.contentTypes.links" class="text-blue-600 bg-gray-700 border-gray-600 rounded">
              <span class="ml-1 text-sm text-gray-300">Links</span>
              </label>
            </div>
        </div>
        
          <!-- Moderation Level -->
        <div class="flex items-center gap-2">
          <label class="text-sm font-medium text-gray-300">Moderation:</label>
            <select
              v-model="filters.moderationLevel"
            class="bg-gray-700 border-gray-600 text-white rounded-md text-sm focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Any</option>
              <option value="strict">Strict</option>
              <option value="moderate">Moderate</option>
              <option value="relaxed">Relaxed</option>
            </select>
        </div>

        <!-- Sort and Export -->
        <div class="flex items-center gap-2 ml-auto">
              <select
                v-model="sortBy"
            class="bg-gray-700 border-gray-600 text-white rounded-md text-sm focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="opportunity">Sort by Opportunity</option>
                <option value="subscribers">Sort by Members</option>
                <option value="growth">Sort by Growth</option>
                <option value="engagement">Sort by Engagement</option>
              </select>
              <button 
                @click="exportResults"
            class="px-4 py-2 bg-gray-700 text-white rounded-md hover:bg-gray-600 text-sm font-medium transition-colors flex items-center gap-2"
                :disabled="!processedResults.length"
              >
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export CSV
              </button>
          </div>
                </div>
                  </div>

    <!-- Selected Subreddits Bar -->
    <div v-if="selectedSubreddits.size > 0" class="bg-blue-900 text-white py-3 px-4 shadow-md">
      <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between">
        <div class="flex items-center gap-4 mb-2 sm:mb-0">
          <span class="text-sm font-medium">{{ selectedSubreddits.size }} subreddits selected</span>
          <div class="flex items-center gap-2">
          <button 
              @click="showSaveAudienceModal = true"
              class="px-3 py-1 bg-blue-600 text-white text-xs rounded-md hover:bg-blue-700 transition-colors"
            >
              Save Audience
            </button>
            <button 
              @click="compareSelected"
              class="px-3 py-1 bg-blue-600 text-white text-xs rounded-md hover:bg-blue-700 transition-colors"
              :disabled="selectedSubreddits.size < 2 || selectedSubreddits.size > 5"
            >
              Compare Selected
          </button>
                      </div>
        </div>
        <div class="flex items-center">
        <button 
          @click="clearSelection"
            class="text-xs text-blue-300 hover:text-white transition-colors"
        >
          Clear Selection
        </button>
        </div>
                    </div>
                  </div>

    <!-- Main Content Area -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
      <div v-if="loading" class="flex flex-col items-center justify-center py-12">
        <div class="w-full max-w-md bg-gray-700 rounded-full h-3 mb-3">
          <div class="bg-blue-600 h-3 rounded-full" :style="`width: ${searchProgress}%`"></div>
              </div>
        <p class="mt-2 text-sm text-gray-400">
          Analyzing subreddits... {{ searchProgress }}% complete
        </p>
            </div>
            
      <div v-else-if="error" class="text-center py-12">
        <p class="text-red-500">{{ error }}</p>
      </div>
      
      <div v-else-if="results.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <SubredditCard
          v-for="subreddit in paginatedResults"
          :key="subreddit.id || subreddit.display_name"
          :subreddit="subreddit"
          :is-selected="selectedSubreddits.has(subreddit.display_name)"
          @toggle-select="toggleSubredditSelection"
          @view-details="viewSubredditDetails"
          class="h-full"
        />
                      </div>

      <div v-else class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-300">No results found</h3>
        <p class="mt-1 text-sm text-gray-500">Try adjusting your search or filters.</p>
            </div>
            
      <!-- Pagination -->
      <div v-if="results.length > itemsPerPage" class="flex justify-center mt-8">
        <nav class="flex items-center bg-gray-800 px-4 py-3 rounded-md shadow-sm">
                    <button 
            @click="changePage(currentPage - 1)"
            :disabled="currentPage === 1"
            class="px-4 py-2 rounded-l-md border border-r-0 border-gray-600 text-gray-400 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500 disabled:opacity-50"
          >
            Previous
                    </button>
          <span class="px-4 py-2 border-t border-b border-gray-600 text-gray-300">
            {{ (currentPage-1)*itemsPerPage+1 }}-{{ Math.min(currentPage*itemsPerPage, results.length) }} 
            of {{ results.length }}
          </span>
                    <button 
            @click="changePage(currentPage + 1)"
            :disabled="currentPage === Math.ceil(results.length / itemsPerPage)"
            class="px-4 py-2 rounded-r-md border border-l-0 border-gray-600 text-gray-400 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500 disabled:opacity-50"
                    >
            Next
                    </button>
        </nav>
      </div>
    </div>
            
    <!-- Audience Builder Panel -->
    <AudienceBuilderPanel
      v-if="selectedSubreddits.size > 0"
      :selected-subreddits="Array.from(selectedSubreddits.keys())"
      @save="showSaveAudienceModal = true"
      @clear="clearSelection"
      @remove="removeFromSelection"
    />

    <!-- Save Audience Modal -->
    <div v-if="showSaveAudienceModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-medium text-white mb-4">Save Audience</h3>
        <div class="space-y-4">
              <div>
            <label class="block text-sm font-medium text-gray-400 mb-1">Audience Name</label>
            <input
              v-model="audienceName"
              type="text"
              class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Enter a name for this audience"
            />
                    </div>
          <div>
            <label class="block text-sm font-medium text-gray-400 mb-1">Description (Optional)</label>
            <textarea
              v-model="audienceDescription"
              class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              rows="3"
              placeholder="Add a description..."
            ></textarea>
                    </div>
          <div class="flex justify-end space-x-4 mt-6">
                    <button 
              @click="showSaveAudienceModal = false"
              class="px-4 py-2 text-gray-400 hover:text-white transition-colors"
            >
              Cancel
                    </button>
                    <button 
              @click="saveAudience"
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
              :disabled="!audienceName.trim()"
                    >
              Save
                    </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Audience Status Modal -->
    <AudienceStatusModal 
      v-if="showStatusModal" 
      :key="`status-modal-${savedAudienceId}`"
      :audience-id="savedAudienceId"
      @close="handleStatusModalClose"
    />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import api from '../../services/api';
import axios from 'axios';
import SubredditCard from './SubredditCard.vue';
import { useSearchStore } from '../../stores/search';
import AudienceBuilderPanel from './AudienceBuilderPanel.vue';
import AudienceStatusModal from '../audiences/AudienceStatusModal.vue';
import { useToast } from 'vue-toast-notification';

const router = useRouter();
const authStore = useAuthStore();
const searchStore = useSearchStore();
const toast = useToast();
const searchQuery = ref('');
const loading = ref(false);
const results = ref([]);
const totalResults = ref(0);
const sortBy = ref('opportunity');
const error = ref('');
const hasApiCredentials = ref(true);
const selectedSubreddits = ref(new Map());
const currentPage = ref(1);
const itemsPerPage = ref(10);
const searchProgress = ref(0);
const searchTimeElapsed = ref(0); // Keep for search duration tracking
const searchTimer = ref(null); // Timer reference
const isSearching = ref(false); // Add a flag to track if a search is currently in progress
const searchDebounce = ref(null); // For debouncing search clicks
const wasRestoredFromStorage = ref(false); // Track if results were restored from storage

// Keep rate limit tracking variables for API purposes but don't display them
const rateLimitUsed = ref(0);
const rateLimitRemaining = ref(100);
const rateLimitReset = ref(600);
const rateLimitResetInitial = ref(600);
const rateLimitTimestamp = ref(null);
const rateLimitTimer = ref(null);

// Updated rate limit fetch function - keeps tracking rate limits but doesn't display them
const rateLimitFetch = async (url, options) => {
  try {
    const response = await fetch(url, options);
    
    // Still track rate limit info from headers for internal use
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
    
    // Still keep the timer to track rate limits internally
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

// Simplified timer function - keeps tracking but doesn't update display
const startRateLimitTimer = () => {
  if (rateLimitTimer.value) {
    clearInterval(rateLimitTimer.value);
  }
  
  rateLimitTimer.value = setInterval(() => {
    if (rateLimitTimestamp.value) {
  const now = Date.now();
      const elapsed = Math.floor((now - rateLimitTimestamp.value) / 1000);
      const initialResetValue = rateLimitResetInitial.value || 600;
      const remaining = Math.max(0, initialResetValue - elapsed);
      
      rateLimitReset.value = remaining;
      
      if (remaining === 0) {
        rateLimitUsed.value = 0;
        rateLimitRemaining.value = 100;
        clearInterval(rateLimitTimer.value);
        rateLimitTimer.value = null;
      }
    }
  }, 1000);
};

// Add the initiateSearch method with debounce and better error handling
const initiateSearch = async () => {
  // Prevent multiple clicks from triggering multiple searches
  if (isSearching.value || loading.value) {
    console.log('Search already in progress, ignoring click');
    return;
  }
  
  // Check for empty query
  if (!searchQuery.value.trim()) {
    error.value = 'Please enter search keywords';
    return;
  }
  
  // Clear any existing debounce timer
  if (searchDebounce.value) {
    clearTimeout(searchDebounce.value);
  }
  
  // Set debounce to prevent rapid consecutive clicks
  searchDebounce.value = setTimeout(async () => {
    try {
      // Set flags to prevent multiple searches
      isSearching.value = true;
      loading.value = true;
      
      console.log('Starting search for:', searchQuery.value);
      
      // Execute the search
      await handleSearch();
      
      // Store results in session if search was successful
      if (results.value && results.value.length > 0) {
        try {
          searchStore.storeResultsInSession({
            query: searchQuery.value,
            results: results.value,
            total_results: results.value.length,
            timestamp: Date.now()
          });
        } catch (storageError) {
          console.warn('Non-critical: Error storing search results in session:', storageError);
          // Continue even if storage fails
        }
      }
    } catch (error) {
      console.error('Search error:', error);
      // Show error message to user
      error.value = error.message || 'An error occurred during search';
      // Reset the results to prevent showing stale data
      results.value = [];
    } finally {
      // Always clean up regardless of success or failure
      loading.value = false;
      isSearching.value = false;
      searchDebounce.value = null;
    }
  }, 300); // 300ms debounce
};

// Update onMounted to remove rate limit display initialization
onMounted(async () => {
  try {
    // Check API credentials first
    const response = await api.get('/api/settings/api');
    const { redditConfig } = response.data;
    hasApiCredentials.value = redditConfig && redditConfig.isConnected;
    
    if (!hasApiCredentials.value) {
      error.value = 'Please configure your Reddit API credentials in Settings before searching.';
      return;
    }

    // Get the search store
    const searchStore = useSearchStore();
    
    // Check if we have valid search state in the store
    if (searchStore.query && Array.isArray(searchStore.results) && searchStore.results.length > 0) {
      console.log('Restoring search state from Pinia store');
      
      // Sync filters with store instead of resetting to defaults
      filters.minMembers = searchStore.filters.minMembers || 0;
      filters.activityLevel = searchStore.filters.activityLevel || '';
      filters.moderationLevel = searchStore.filters.moderationLevel || '';
      filters.engagementRate = searchStore.filters.engagementRate || 0;
      filters.contentTypes.text = searchStore.filters.contentTypes?.text || false;
      filters.contentTypes.images = searchStore.filters.contentTypes?.images || false;
      filters.contentTypes.videos = searchStore.filters.contentTypes?.videos || false;
      filters.contentTypes.links = searchStore.filters.contentTypes?.links || false;
      
      // Set search query
      searchQuery.value = searchStore.query;
      
      // Create a deep copy of the results to ensure reactivity
      const restoredResults = JSON.parse(JSON.stringify(searchStore.results));
      
      // Map store sort field to component sort value
      const storeSort = searchStore.sortBy || 'opportunity_score';
      switch(storeSort) {
        case 'subscribers':
          sortBy.value = 'subscribers';
          break;
        case 'growth_rate':
          sortBy.value = 'growth';
          break;
        case 'engagement_rate':
          sortBy.value = 'engagement';
          break;
        case 'opportunity_score':
        default:
          sortBy.value = 'opportunity';
          break;
      }
      
      console.log('Mapped store sort', storeSort, 'to component sort', sortBy.value);
      
      // Reset error message
      error.value = '';
      
      // Reset to first page
      currentPage.value = 1;
      
      // Flag that results were restored from storage
      wasRestoredFromStorage.value = false; // Set to false to use store filtering
      
      // Log restoration details
      console.log(`Restoring ${restoredResults.length} results from Pinia store`);
      if (restoredResults.length > 0) {
        console.log('First restored result:', restoredResults[0].display_name);
      }
      
      // Update results with a delay to ensure Vue has updated
      setTimeout(() => {
        // First set the results
        results.value = restoredResults;
        
        // Then force a reactivity update
        results.value = [...results.value];
        
        console.log('Forced reactivity update on restored results');
        console.log('Current results length:', results.value.length);
        
        // Log the restored search for analytics
        if (searchQuery.value && results.value && results.value.length > 0) {
          logSearchResults(searchQuery.value, results.value, null, true).catch(err => {
            console.warn('Non-critical error logging restored search results:', err);
          });
        }
      }, 100);
    }
  } catch (err) {
    console.error('Error during component initialization:', err);
    error.value = 'Unable to initialize search component. Please try again later.';
  }
  
  setupScrollHandler();
});

// Clean up timer on component unmount
onUnmounted(() => {
  if (rateLimitTimer.value) {
    clearInterval(rateLimitTimer.value);
  }
  if (searchTimer.value) {
    clearInterval(searchTimer.value);
  }
});

// Filters
const filters = reactive({
  minMembers: 0,  // Changed from minSubscribers to minMembers to match store
  activityLevel: '',
  contentTypes: {
    text: false,
    images: false,
    videos: false,
    links: false
  },
  moderationLevel: '',
  engagementRate: 0
});

// Add a simple caching mechanism
const searchCache = ref({});

// Add logSearchResults function with simplified approach for minimal data
const logSearchResults = async (query, results, totalResults = null, isRestoredSearch = false) => {
  // For restored searches, we can be more lenient with validation
  if (!isRestoredSearch && (!query || !results || !Array.isArray(results) || results.length === 0)) {
    console.log('Missing required data for search logging, skipping log');
    return;
  }
  
  try {
    // Create bare-minimum data structure that meets API requirements
    const payload = {
      query: typeof query === 'string' ? query.trim() : '',
      results: Array.isArray(results) ? results.slice(0, 20) : [], // Use at most 20 results for logging
      timestamp: Date.now()
    };
    
    // Check if we have valid data to send
    if (!payload.query || !Array.isArray(payload.results) || payload.results.length === 0) {
      console.log('Invalid search log payload, skipping log');
      return;
    }
    
    // Log what we're doing
    if (isRestoredSearch) {
      console.log(`Logging restored search for "${payload.query}" with ${payload.results.length} results`);
    } else {
      console.log(`Logging search for "${payload.query}" with ${payload.results.length} results (internal app log)`);
    }
    
    // Make the logging API call
    const response = await api.post('/api/log/search', payload);
    
    console.log(`Search logging successful (internal app log): ${response.status}`);
  } catch (error) {
    console.warn('Non-critical search logging error:', error.message);
    // Don't throw, this is a non-critical operation
  }
};

// Add text normalization function
const normalizeText = (text) => {
  if (!text) return '';
  
  // Convert to lowercase and remove special characters
  const normalized = text.toLowerCase()
    .replace(/[^a-z0-9\s]/g, ' ')
    .replace(/\s+/g, ' ')
    .trim();
    
  // Remove common English stop words
  const stopWords = new Set([
    'a', 'an', 'and', 'are', 'as', 'at', 'be', 'by', 'for', 'from', 
    'has', 'he', 'in', 'is', 'it', 'its', 'of', 'on', 'that', 'the', 
    'to', 'was', 'were', 'will', 'with'
  ]);
  
  return normalized
    .split(' ')
    .filter(word => !stopWords.has(word))
    .join(' ');
};

// Optimize post fetching with parallel requests and caching
const postCache = ref(new Map());

// Add the missing processedResults computed property
const processedResults = computed(() => {
  // If we have results and we were not restored from storage, use the store's filtered and sorted results
  if (results.value && results.value.length > 0) {
    console.log(`Processing ${results.value.length} results through store's getters`);
    
    // Use the search store's filtered and sorted results
    const searchStore = useSearchStore();
    
    // Log the number of results after applying filters for debugging
    console.log(`Filter results: ${searchStore.filteredResults.length} of ${results.value.length} passed filters`);
    
    // Sort the filtered results based on selected criteria
    let sortedResults = [...searchStore.filteredResults];
    
    switch (sortBy.value) {
      case 'subscribers':
        sortedResults.sort((a, b) => (b.subscribers || 0) - (a.subscribers || 0));
        break;
      case 'growth':
        sortedResults.sort((a, b) => (b.growth_rate || 0) - (a.growth_rate || 0));
        break;
      case 'engagement':
        sortedResults.sort((a, b) => (b.engagement_rate || 0) - (a.engagement_rate || 0));
        break;
      case 'opportunity':
      default:
        sortedResults.sort((a, b) => (b.opportunity_score || 0) - (a.opportunity_score || 0));
        break;
    }
    
    return sortedResults;
  }
  
  // If we have no results, return empty array
  return [];
});

// Helper function to fetch 100 posts for multiple subreddits in parallel
const fetchSubredditPostsBatch = async (subreddits, accessToken, fetchFn) => {
  const fetchPromises = subreddits.map(async subreddit => {
    const subredditName = subreddit.display_name;
    
    // Check cache first
    if (postCache.value.has(subredditName)) {
      console.log(`Using cached posts for r/${subredditName}: ${postCache.value.get(subredditName).length} posts`);
      return { subredditName, posts: postCache.value.get(subredditName) };
    }

    try {
      console.log(`Fetching posts for r/${subredditName}...`);
      
      // Log the API request we're making
      console.log(`API Request for r/${subredditName}:`);
      console.log(`- New posts: https://oauth.reddit.com/r/${subredditName}/new?limit=100`);
      
      const newPostsResponse = await fetchFn(`https://oauth.reddit.com/r/${subredditName}/new?limit=100`, {
        headers: {
          'Authorization': `Bearer ${accessToken}`,
          'User-Agent': 'MyApp/0.1 by NeedleworkerFuzzy314'
        }
      });
      
      // Log HTTP status code
      console.log(`HTTP Status Code for r/${subredditName}:`);
      console.log(`- New posts: ${newPostsResponse.status} ${newPostsResponse.statusText}`);
      
      // Parse JSON response
      const newPostsData = await newPostsResponse.json();
      
      // Log response structure
      console.log(`Response structure for r/${subredditName}:`);
      console.log(`- New posts has data: ${Boolean(newPostsData.data)}`);
      console.log(`- New posts has children: ${Boolean(newPostsData.data?.children)}`);
      console.log(`- New posts children count: ${newPostsData.data?.children?.length || 0}`);
      
      // Check for error messages in the response
      if (newPostsData.error) {
        console.error(`API Error for r/${subredditName}:`);
        console.error(`- New posts error: ${newPostsData.error || 'none'}, message: ${newPostsData.message || 'none'}`);
      }

      const posts = (newPostsData.data?.children || []).map(child => child.data);
      
      // Log post count and details
      console.log(`Fetched ${posts.length} posts for r/${subredditName}`);
      console.log(`- New posts: ${newPostsData.data?.children?.length || 0}`);
      
      // Log engagement metrics for the posts
      const totalUpvotes = posts.reduce((sum, post) => sum + (post.score || 0), 0);
      const totalComments = posts.reduce((sum, post) => sum + (post.num_comments || 0), 0);
      const postsWithEngagement = posts.filter(post => (post.score > 0 || post.num_comments > 0)).length;
      
      console.log(`Engagement metrics for r/${subredditName}:`);
      console.log(`- Total upvotes: ${totalUpvotes}`);
      console.log(`- Total comments: ${totalComments}`);
      console.log(`- Posts with engagement: ${postsWithEngagement}/${posts.length}`);
      console.log(`- Average upvotes per post: ${(totalUpvotes / Math.max(1, posts.length)).toFixed(2)}`);
      console.log(`- Average comments per post: ${(totalComments / Math.max(1, posts.length)).toFixed(2)}`);

      // Store in cache with 5-minute expiration
      postCache.value.set(subredditName, posts);
      setTimeout(() => postCache.value.delete(subredditName), 5 * 60 * 1000);

      return { subredditName, posts };
    } catch (err) {
      console.error(`Error fetching posts for ${subredditName}:`, err);
      console.error(`Error details: ${err.message}`);
      console.error(`Error stack: ${err.stack}`);
      return { subredditName, posts: [] };
    }
  });

  return Promise.all(fetchPromises);
};

// Update the paginatedResults computed property to properly handle all cases
const paginatedResults = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage.value;
  const endIndex = startIndex + itemsPerPage.value;
  
  // Always use processed results which now correctly uses the store's filtering
  return processedResults.value.slice(startIndex, endIndex);
});

// Replace the setupInfiniteScroll function with a manual scroll handler
const setupScrollHandler = () => {
  const scrollEl = document.querySelector('.results-container');
  if (!scrollEl) return;
  
  scrollEl.addEventListener('scroll', () => {
    if (loading.value) return;
    
    const { scrollTop, scrollHeight, clientHeight } = scrollEl;
    const scrolledToBottom = scrollTop + clientHeight >= scrollHeight - 200;
    
    if (scrolledToBottom && currentPage.value < Math.ceil(processedResults.value.length / itemsPerPage.value)) {
      currentPage.value++;
    }
  });
};

// Add the initiateSearch method
const handleSearch = async () => {
  if (!searchQuery.value.trim()) {
    error.value = 'Please enter search keywords';
    return;
  }

  loading.value = true;
  error.value = '';
  currentPage.value = 1;
  searchProgress.value = 0;
  
  // Reset the restored from storage flag
  wasRestoredFromStorage.value = false;
  
  // Start timer for search duration only
  searchTimeElapsed.value = 0;
  
  // Clear previous timer if exists
  if (searchTimer.value) {
    clearInterval(searchTimer.value);
  }
  
  console.log('--------------------------------------------------');
  console.log('STARTING SEARCH FOR: ' + searchQuery.value);
  console.log('--------------------------------------------------');
  
  // Start a timer to track search duration
  const startTime = Date.now();
  searchTimer.value = setInterval(() => {
    searchTimeElapsed.value = Math.round((Date.now() - startTime) / 1000);
  }, 1000);
  
  // Get the search store
  const searchStore = useSearchStore();
  
  // Check cache first
  const cacheKey = searchQuery.value.trim().toLowerCase();
  if (searchCache.value[cacheKey]) {
    console.log('Using cached results');
    results.value = searchCache.value[cacheKey];
    
    // Update search store
    try {
      searchStore.updateSearchState({
        query: searchQuery.value,
        results: results.value,
        total_results: results.value.length,
        timestamp: Date.now()
      });
    } catch (error) {
      console.warn('Error updating search state:', error);
      // Continue execution even if localStorage fails
    }
    
    // Log cached results
    if (searchQuery.value && results.value && results.value.length > 0) {
      logSearchResults(searchQuery.value, results.value).catch(err => {
        console.warn('Non-critical error logging cached search results:', err);
      });
    }

    loading.value = false;
    return;
  }
  
  results.value = [];

  try {
    // Get access token
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

    // Search using both endpoints in parallel with limit=100
    const [searchResponse, autocompleteResponse] = await Promise.all([
      rateLimitFetch(`https://oauth.reddit.com/subreddits/search?q=${encodeURIComponent(searchQuery.value)}&sort=relevance&include_over_18=false&show_users=false&limit=100`, {
        headers: {
          'Authorization': `Bearer ${tokenData.access_token}`,
          'User-Agent': 'MyApp/0.1 by NeedleworkerFuzzy314'
        }
      }),
      rateLimitFetch(`https://oauth.reddit.com/api/subreddit_autocomplete_v2?query=${encodeURIComponent(searchQuery.value)}&include_categories=false&include_over_18=false&exact=false&limit=100`, {
        headers: {
          'Authorization': `Bearer ${tokenData.access_token}`,
          'User-Agent': 'MyApp/0.1 by NeedleworkerFuzzy314'
        }
      })
    ]);

    const [searchData, autocompleteData] = await Promise.all([
      searchResponse.json(),
      autocompleteResponse.json()
    ]);

    if (!searchData.data?.children && !autocompleteData.data?.children) {
      throw new Error('Invalid response format from Reddit API');
    }

    // Process and merge results from both endpoints
    const searchResults = (searchData.data?.children || []).map(child => ({
        ...child.data,
        source: 'search'
    }));
    
    const autocompleteResults = (autocompleteData.data?.children || []).map(child => ({
        ...child.data,
        source: 'autocomplete'
    }));

    // Merge results and remove duplicates
    const seenSubreddits = new Set();
    const mergedResults = [...searchResults, ...autocompleteResults].filter(subreddit => {
        if (!subreddit.display_name || seenSubreddits.has(subreddit.display_name)) {
          return false;
        }
        seenSubreddits.add(subreddit.display_name);
        return true;
    });

    if (mergedResults.length === 0) {
      results.value = [];
      error.value = 'No subreddits found matching your search criteria';
      loading.value = false;
    return;
  }

    // Process all found subreddits with detailed data
    const detailedResults = await fetchDetailedDataInBatches(mergedResults, tokenData.access_token, rateLimitFetch);
    
    // Make sure we have valid results before ranking
    const filteredResults = detailedResults.filter(result => result !== null && result !== undefined);
    
    if (filteredResults.length === 0) {
      results.value = [];
      error.value = 'No detailed results could be retrieved';
      loading.value = false;
    return;
  }

    // Apply TF-IDF ranking to the results
    const rankedResults = rankSubredditsByRelevance(
      filteredResults,
      searchQuery.value
    );

    // Store the ranked results
    results.value = rankedResults || [];

    // Update search store if we have results
    if (results.value.length > 0) {
      try {
        // Create a copy of the results to ensure full persistence
        const resultsCopy = JSON.parse(JSON.stringify(results.value));
        
        // Log what we're trying to store
        console.log(`Storing ${resultsCopy.length} results to search store with query "${searchQuery.value}"`);
        console.log('First result sample:', resultsCopy[0]?.display_name);
        
        // Store in cache for faster retrieval
        searchCache.value[cacheKey] = resultsCopy;
        
        // Update the search store including the current sort order
        searchStore.updateSearchState({
          query: searchQuery.value,
          results: resultsCopy,
          total_results: resultsCopy.length,
          timestamp: Date.now(),
          sortBy: sortBy.value
        });
        
        console.log('Search state successfully stored');
        
        // Double-check localStorage to make sure it was saved
        setTimeout(() => {
          const storedQuery = localStorage.getItem('lastSearchQuery');
          if (storedQuery === searchQuery.value) {
            console.log('Verified: Search query saved to localStorage');
          } else {
            console.warn('Warning: Search query may not have been saved correctly');
          }
        }, 100);
      } catch (error) {
        console.error('Error saving search state:', error);
      }
      
      // Log the search results
      if (searchQuery.value && results.value && results.value.length > 0) {
        logSearchResults(searchQuery.value, results.value).catch(err => {
          console.warn('Non-critical error logging search results:', err);
        });
      }
    } else {
      error.value = 'No subreddits found matching your search criteria';
    }
    
  } catch (err) {
    console.error('Search error:', err);
    error.value = err.message || 'An error occurred while searching';
    results.value = [];
  } finally {
    // Stop the timer
    if (searchTimer.value) {
      clearInterval(searchTimer.value);
      searchTimer.value = null;
    }
    
    loading.value = false;
  }

  // Cache the results
  if (results.value.length > 0) {
    searchCache.value[cacheKey] = [...results.value];
  }
};

// Update these utility functions for the combined keyword matching approach
const exactPhraseMatch = (text, query) => {
  if (!text || !query) return 0;
  
  // Check for exact match
  if (text.toLowerCase().includes(query.toLowerCase())) {
    // Calculate how much of the text is covered by the query
    const coverage = query.length / Math.max(text.length, 1);
    // Return a score between 50-100 based on coverage
    return 50 + (coverage * 50);
  }
  
  // Check for partial matches
  const queryTerms = query.toLowerCase().split(/\s+/);
  const matchedTerms = queryTerms.filter(term => 
    text.toLowerCase().includes(term)
  );
  
  if (matchedTerms.length === 0) return 0;
  
  // Calculate percentage of query terms that match
  const matchPercentage = matchedTerms.length / queryTerms.length;
  // Scale to 0-40 range for partial matches
  return matchPercentage * 40;
};

const generateNGrams = (text, minGram = 2, maxGram = 3) => {
  if (!text) return [];
  const words = text.toLowerCase().split(/\s+/);
  const ngrams = [];
  
  // Generate n-grams for each size between minGram and maxGram
  for (let n = minGram; n <= maxGram; n++) {
    for (let i = 0; i <= words.length - n; i++) {
      ngrams.push(words.slice(i, i + n).join(' '));
    }
  }
  
  return ngrams;
};

const nGramMatch = (text, query, minGram = 2, maxGram = 3) => {
  if (!text || !query) return 0;
  
  const textNGrams = generateNGrams(text, minGram, maxGram);
  const queryNGrams = generateNGrams(query, minGram, maxGram);
  
  if (queryNGrams.length === 0) return 0;
  
  // Count how many query n-grams appear in the text n-grams
  const matchingNGrams = queryNGrams.filter(queryNGram => 
    textNGrams.includes(queryNGram)
  );
  
  // Calculate percentage of matching n-grams
  const matchPercentage = matchingNGrams.length / queryNGrams.length;
  
  // Scale to 0-100 range
  return matchPercentage * 100;
};

const jaccardSimilarity = (text, query) => {
  if (!text || !query) return 0;
  
  const textSet = new Set(text.toLowerCase().split(/\s+/));
  const querySet = new Set(query.toLowerCase().split(/\s+/));
  
  if (textSet.size === 0 || querySet.size === 0) return 0;
  
  // Calculate intersection
  const intersection = new Set([...textSet].filter(word => querySet.has(word)));
  
  // Calculate union
  const union = new Set([...textSet, ...querySet]);
  
  // Return Jaccard similarity (intersection / union)
  return intersection.size / union.size;
};

// Replace the rankSubredditsByRelevance function with the new combined approach
const rankSubredditsByRelevance = (subreddits, searchQuery) => {
  const normalizedQuery = normalizeText(searchQuery);
  const queryTerms = normalizedQuery.split(' ');
  
  return subreddits.map(subreddit => {
    // Calculate base relevancy score from subreddit metadata
    // Ensure topics is an array before joining
    const topics = Array.isArray(subreddit.topics) ? subreddit.topics : [];
    const subredditText = normalizeText(`${subreddit.display_name || ''} ${subreddit.title || ''} ${subreddit.public_description || ''} ${topics.join(' ')}`);
    
    // Base relevancy calculation using combined approach
    const exactMatchBase = exactPhraseMatch(subredditText, normalizedQuery);
    const nGramMatchBase = nGramMatch(subredditText, normalizedQuery);
    const jaccardScoreBase = jaccardSimilarity(subredditText, normalizedQuery) * 100;
    
    // Combined score with weights
    const baseRelevancyScore = Math.min(100, 
      (exactMatchBase * 0.5) + 
      (nGramMatchBase * 0.3) + 
      (jaccardScoreBase * 0.2)
    );
    
    // Track matches for debugging
    const nameMatches = queryTerms.map(term => ({
      term,
      matches: ((subreddit.display_name || '').toLowerCase().match(new RegExp(term, 'g')) || []).length
    }));
    
    const textMatches = queryTerms.map(term => ({
      term,
      matches: (subredditText.match(new RegExp(term, 'g')) || []).length
    }));
    
    // Calculate content relevancy score from all posts
    const postsToAnalyze = subreddit.all_posts || subreddit.recent_posts || [];
    
    // Initialize aggregated post scores
    let totalExactMatchScore = 0;
    let totalNGramMatchScore = 0;
    let totalJaccardScore = 0;
    
    // Calculate individual post scores
    const postScores = postsToAnalyze.map(post => {
      const postText = normalizeText(`${post.title || ''} ${post.selftext || ''} ${post.link_flair_text || ''}`);
      
      // Apply the combined approach
      const exactMatchScore = exactPhraseMatch(postText, normalizedQuery);
      const nGramMatchScore = nGramMatch(postText, normalizedQuery);
      const jaccardScore = jaccardSimilarity(postText, normalizedQuery) * 100;
      
      // Add to totals for averaging later
      totalExactMatchScore += exactMatchScore;
      totalNGramMatchScore += nGramMatchScore;
      totalJaccardScore += jaccardScore;
      
      // Calculate term matches for debugging
      const termMatches = {};
      queryTerms.forEach(term => {
        const regex = new RegExp(term, 'g');
        const matches = (postText.match(regex) || []).length;
        if (matches > 0) {
          termMatches[term] = matches;
        }
      });
      
      // Combined score with weights
      const score = Math.min(100, 
        (exactMatchScore * 0.5) + 
        (nGramMatchScore * 0.3) + 
        (jaccardScore * 0.2)
      );
      
          return {
        title: post.title,
        selftext: post.selftext || '',
        score,
        exact_match_score: exactMatchScore,
        ngram_match_score: nGramMatchScore,
        jaccard_score: jaccardScore,
        term_matches: termMatches,
        exact_match: exactMatchScore > 40, // Changed from boolean to threshold
        ngram_match: nGramMatchScore > 40, // Changed from boolean to threshold
        raw_frequencies: queryTerms.map(term => {
          const regex = new RegExp(term, 'g');
          return { term, count: (postText.match(regex) || []).length };
        })
      };
    });
    
    // Calculate average scores for posts
    const postCount = Math.max(1, postsToAnalyze.length);
    const avgExactMatchScore = totalExactMatchScore / postCount;
    const avgNGramMatchScore = totalNGramMatchScore / postCount;
    const avgJaccardScore = totalJaccardScore / postCount;
    
    // Count posts with matches (score > 20)
    const postsWithMatches = postScores.filter(post => post.score > 20).length;
    
    // Calculate weighted average for content score
    // Sort posts by score in descending order
    const sortedScores = [...postScores].sort((a, b) => b.score - a.score);
    
    // Calculate weighted sum giving more importance to higher scoring posts
    const weightedSum = sortedScores.reduce((sum, post, index) => {
      // Weight decreases by position (first posts count more)
      const weight = 1 / (index + 1);
      return sum + (post.score * weight);
    }, 0);
    
    // Calculate total weight
    const totalWeight = sortedScores.reduce((sum, _, index) => sum + (1 / (index + 1)), 0);
    
    // Calculate weighted average
    const weightedAverage = totalWeight > 0 ? weightedSum / totalWeight : 0;
    
    // Add bonus for consistency if many posts are relevant
    const relevantPosts = postScores.filter(post => post.score > 30).length;
    const consistencyBonus = (relevantPosts / Math.max(1, postsToAnalyze.length)) * 20;
    
    const content_relevancy_score = Math.min(100, weightedAverage + consistencyBonus);
    
    // Create detailed debug information
    const debugInfo = {
      query: searchQuery,
      normalized_query: normalizedQuery,
      query_terms: queryTerms,
      base_score_details: {
        name_matches: nameMatches,
        text_matches: textMatches,
        exact_match: exactMatchBase > 40,
        ngram_match: nGramMatchBase > 40,
        jaccard_score: jaccardScoreBase,
        final_percentage: baseRelevancyScore
      },
      content_score_details: {
        posts_analyzed: postsToAnalyze.length,
        matching_posts_count: postsWithMatches,
        weighted_average: weightedAverage,
        consistency_bonus: consistencyBonus,
        relevant_posts_count: relevantPosts,
        final_percentage: content_relevancy_score
      },
      post_scores: postScores.slice(0, 10) // Only include top 10 posts in debug info to keep it manageable
    };
    
    console.debug(`Relevancy scores for r/${subreddit.display_name}:`, debugInfo);

    return {
      ...subreddit,
      // Combined scores
      base_relevancy_score: baseRelevancyScore,
      content_relevancy_score: content_relevancy_score,
      
      // Individual metadata scores
      metadata_exact_match_score: exactMatchBase,
      metadata_ngram_match_score: nGramMatchBase,
      metadata_jaccard_score: jaccardScoreBase,
      
      // Individual post scores
      posts_exact_match_score: avgExactMatchScore,
      posts_ngram_match_score: avgNGramMatchScore,
      posts_jaccard_score: avgJaccardScore,
      
      relevancy_debug: debugInfo
    };
  })
  .sort((a, b) => {
    const baseScoreDiff = b.base_relevancy_score - a.base_relevancy_score;
    if (Math.abs(baseScoreDiff) > 10) return baseScoreDiff;
    return b.content_relevancy_score - a.content_relevancy_score;
  });
};

// Update the batch processing function
const fetchDetailedDataInBatches = async (subreddits, accessToken, fetchFn) => {
  const batchSize = 5; // Reduced batch size for parallel processing
  const results = [];
  
  for (let i = 0; i < subreddits.length; i += batchSize) {
    const batch = subreddits.slice(i, i + batchSize);
    
    try {
      // Fetch subreddit info and posts in parallel for the entire batch
      const [aboutResponses, postsData] = await Promise.all([
        Promise.all(
          batch.map(subreddit =>
            fetchFn(`https://oauth.reddit.com/r/${subreddit.display_name}/about`, {
              headers: {
        'Authorization': `Bearer ${accessToken}`,
                'User-Agent': 'MyApp/0.1 by NeedleworkerFuzzy314'
              }
            }).then(res => res.json())
          )
        ),
        fetchSubredditPostsBatch(batch, accessToken, fetchFn)
      ]);

      // Process each subreddit in the batch
      const batchResults = batch.map((subreddit, index) => {
        try {
          const aboutData = aboutResponses[index].data;
          const posts = postsData.find(p => p.subredditName === subreddit.display_name)?.posts || [];

          // Calculate scores and metrics
    const baseRelevancyScore = calculateBaseRelevancyScore(subreddit, searchQuery.value);
    const contentRelevancyScore = calculateContentRelevancyScore(posts, searchQuery.value);
    const postsPerDay = calculatePostsPerDay(posts);
    const engagementMetrics = calculateEngagementRate(posts, subreddit.subscribers, searchQuery.value);
        const growthRate = calculateGrowthRate(subreddit);

        return {
      ...subreddit,
          id: subreddit.id,
          name: subreddit.name,
          display_name: subreddit.display_name,
          title: subreddit.title,
          public_description: subreddit.public_description,
          subscribers: subreddit.subscribers,
            active_user_count: aboutData?.active_user_count || 0,
          created_utc: subreddit.created_utc,
          posts_per_day: postsPerDay,
          engagement_rate: engagementMetrics.totalEngagement,
          active_engagement: engagementMetrics.activeEngagement,
          keyword_engagement: engagementMetrics.keywordEngagement,
          growth_rate: growthRate,
          opportunity_score: calculateOpportunityScore({
            display_name: subreddit.display_name,
            subscribers: subreddit.subscribers,
            engagement_rate: engagementMetrics.totalEngagement,
            keyword_engagement: engagementMetrics.keywordEngagement,
            content_relevancy_score: contentRelevancyScore,
            base_relevancy_score: baseRelevancyScore,
            posts_per_day: postsPerDay,
            growth_rate: growthRate
          }, results),
      base_relevancy_score: baseRelevancyScore,
      content_relevancy_score: contentRelevancyScore,
      content_types: analyzeContentTypes(posts),
            moderation_level: analyzeModerationLevel(aboutData),
      topics: analyzeTopics(posts),
          icon_img: subreddit.icon_img,
          header_img: subreddit.header_img,
      source: subreddit.source,
      // Store only 5 posts for display in the UI
      recent_posts: posts.slice(0, 5).map(post => ({
        title: post.title,
        selftext: post.selftext || '',
        score: post.score,
        num_comments: post.num_comments,
        created_utc: post.created_utc,
        link_flair_text: post.link_flair_text || '',
        is_self: post.is_self,
        is_video: post.is_video,
        domain: post.domain,
        permalink: post.permalink,
        url: post.url
      })),
      // Store all posts for relevancy calculation
      all_posts: posts
        };
      } catch (err) {
          console.error(`Error processing subreddit ${subreddit.display_name}:`, err);
        return null;
      }
      });

      results.push(...batchResults);
      
      // Update progress
      searchProgress.value = Math.min(100, Math.round((i + batchSize) / subreddits.length * 100));
      
      // Add a small delay between batches to avoid rate limiting
      await new Promise(resolve => setTimeout(resolve, 500));
  } catch (err) {
      console.error('Error processing batch:', err);
    }
  }
  
  return results;
};

// Replace the calculateContentRelevancyScore function to use the combined approach
const calculateContentRelevancyScore = (posts, query) => {
  const normalizedQuery = normalizeText(query);
  const queryTerms = normalizedQuery.split(' ');
  
  if (!posts || posts.length === 0) return 0;

  // Initialize aggregated scores
  let totalExactMatchScore = 0;
  let totalNGramMatchScore = 0;
  let totalJaccardScore = 0;

  // Calculate relevancy score for each post using the combined approach
  const postScores = posts.map(post => {
    const postText = normalizeText(`${post.title || ''} ${post.selftext || ''} ${post.link_flair_text || ''}`);
    
    // Apply the combined approach
    const exactMatchScore = exactPhraseMatch(postText, normalizedQuery);
    const nGramMatchScore = nGramMatch(postText, normalizedQuery);
    const jaccardScore = jaccardSimilarity(postText, normalizedQuery) * 100;
    
    // Add to totals for averaging later
    totalExactMatchScore += exactMatchScore;
    totalNGramMatchScore += nGramMatchScore;
    totalJaccardScore += jaccardScore;
    
    // Calculate term matches for debugging
    const termMatches = {};
    queryTerms.forEach(term => {
      const regex = new RegExp(term, 'g');
      const matches = (postText.match(regex) || []).length;
      if (matches > 0) {
        termMatches[term] = matches;
      }
    });
    
    // Combined score with weights
    const score = Math.min(100, 
      (exactMatchScore * 0.5) + 
      (nGramMatchScore * 0.3) + 
      (jaccardScore * 0.2)
    );
    
    return {
      title: post.title,
      selftext: post.selftext || '',
      score,
      exact_match_score: exactMatchScore,
      ngram_match_score: nGramMatchScore,
      jaccard_score: jaccardScore,
      term_matches: termMatches
    };
  });

  // Calculate average scores
  const postCount = Math.max(1, posts.length);
  const avgExactMatchScore = totalExactMatchScore / postCount;
  const avgNGramMatchScore = totalNGramMatchScore / postCount;
  const avgJaccardScore = totalJaccardScore / postCount;

  // Calculate weighted average, giving more weight to higher scoring posts
  const sortedScores = [...postScores].sort((a, b) => b.score - a.score);
  const weightedSum = sortedScores.reduce((sum, post, index) => {
    // Weight decreases by position (first posts count more)
    const weight = 1 / (index + 1);
    return sum + (post.score * weight);
  }, 0);
  
  const totalWeight = sortedScores.reduce((sum, _, index) => sum + (1 / (index + 1)), 0);
  
  // Final weighted average (0-100)
  const weightedAverage = totalWeight > 0 ? weightedSum / totalWeight : 0;

  // Add bonus for consistency if many posts are relevant
  const relevantPosts = postScores.filter(post => post.score > 30).length;
  const consistencyBonus = (relevantPosts / posts.length) * 20; // Up to 20% bonus

  // Final score capped at 100
  return Math.min(100, weightedAverage + consistencyBonus);
};

// Replace the calculateBaseRelevancyScore function to use the combined approach
const calculateBaseRelevancyScore = (subreddit, query) => {
  const normalizedQuery = normalizeText(query);
  
  // Ensure topics is an array before joining
  const topics = Array.isArray(subreddit.topics) ? subreddit.topics : [];
  const subredditText = normalizeText(`${subreddit.display_name || ''} ${subreddit.title || ''} ${subreddit.public_description || ''} ${topics.join(' ')}`);
  
  // Apply the combined approach
  const exactMatchScore = exactPhraseMatch(subredditText, normalizedQuery);
  const nGramMatchScore = nGramMatch(subredditText, normalizedQuery);
  const jaccardScore = jaccardSimilarity(subredditText, normalizedQuery) * 100;
  
  // Combined score with weights
  return Math.min(100, 
    (exactMatchScore * 0.5) + 
    (nGramMatchScore * 0.3) + 
    (jaccardScore * 0.2)
  );
};

// Updated metric calculation functions
const calculatePostsPerDay = (newPosts) => {
  const now = Date.now() / 1000;
  const oneDayAgo = now - 86400;
  return newPosts.filter(post => post.created_utc > oneDayAgo).length;
};

const calculateEngagementRate = (posts, subscribers, searchQuery) => {
  console.log(`\n========== ENGAGEMENT PER POST CALCULATION ==========`);
  console.log(`Calculating engagement per post for subreddit with ${subscribers} subscribers and ${posts?.length || 0} posts`);
  console.log(`Search query: "${searchQuery}"`);
  
  // Guard against invalid inputs
  if (!Array.isArray(posts) || posts.length === 0 || !subscribers || subscribers <= 0) {
    console.warn(`Invalid inputs for engagement calculation:`, { 
      postsIsArray: Array.isArray(posts),
      postsLength: posts?.length || 0, 
      subscribers: subscribers,
      subscribersValid: subscribers > 0
    });
    console.log(`Returning 0 engagement per post due to invalid inputs`);
    console.log(`=================================================\n`);
    // Return both property names for active engagement when invalid inputs
    return { 
      totalEngagement: 0, 
      activeEngagement: 0, 
      active_post_engagement: 0, 
      keywordEngagement: 0 
    };
  }
  
  // Calculate raw engagement metrics
  let totalUpvotes = 0;
  let totalComments = 0;
  let postsWithEngagement = 0;
  
  // Keyword-specific metrics
  let keywordPosts = 0;
  let keywordUpvotes = 0;
  let keywordComments = 0;
  
  // Normalize search query for case-insensitive matching
  const normalizedQuery = searchQuery ? searchQuery.toLowerCase() : '';
  
  // Log the first 5 posts to see their engagement values
  console.log(`Sample of first 5 posts (if available):`);
  posts.slice(0, 5).forEach((post, index) => {
    const upvotes = post.score || 0;
    const comments = post.num_comments || 0;
    console.log(`Post #${index + 1}: Title: "${post.title?.substring(0, 30)}...", Upvotes: ${upvotes}, Comments: ${comments}`);
  });
  
  posts.forEach(post => {
    const upvotes = post.score || 0;
    const comments = post.num_comments || 0;
    
    totalUpvotes += upvotes;
    totalComments += comments;
    
    if (upvotes > 0 || comments > 0) {
      postsWithEngagement++;
    }
    
    // Check if post contains the search keyword
    const postTitle = (post.title || '').toLowerCase();
    const postContent = (post.selftext || '').toLowerCase();
    const containsKeyword = normalizedQuery && (postTitle.includes(normalizedQuery) || postContent.includes(normalizedQuery));
    
    if (containsKeyword) {
      keywordPosts++;
      keywordUpvotes += upvotes;
      keywordComments += comments;
    }
  });
  
  // Calculate average engagement per post
  const avgUpvotesPerPost = totalUpvotes / posts.length;
  const avgCommentsPerPost = totalComments / posts.length;
  
  // Calculate total engagement (upvotes + comments)
  const totalEngagement = totalUpvotes + totalComments;
  
  // Calculate raw engagement per post (total engagement / number of posts)
  const totalPostEngagement = totalEngagement / posts.length;
  
  // Calculate active post engagement (total engagement / number of posts with engagement)
  const activePostEngagement = postsWithEngagement > 0 ? totalEngagement / postsWithEngagement : 0;
  
  // Calculate keyword engagement (engagement of posts with keyword / number of posts with keyword)
  const keywordEngagement = keywordPosts > 0 ? (keywordUpvotes + keywordComments) / keywordPosts : 0;
  
  console.log(`Raw engagement metrics:`);
  console.log(`- Total upvotes across all posts: ${totalUpvotes}`);
  console.log(`- Total comments across all posts: ${totalComments}`);
  console.log(`- Posts with any engagement: ${postsWithEngagement}/${posts.length}`);
  console.log(`- Average upvotes per post: ${avgUpvotesPerPost.toFixed(2)}`);
  console.log(`- Average comments per post: ${avgCommentsPerPost.toFixed(2)}`);
  console.log(`- Total Post Engagement (all posts): ${totalPostEngagement.toFixed(2)}`);
  console.log(`- Active Post Engagement (only posts with engagement): ${activePostEngagement.toFixed(2)}`);
  
  console.log(`\nKeyword engagement metrics:`);
  console.log(`- Posts containing keyword "${normalizedQuery}": ${keywordPosts}/${posts.length}`);
  console.log(`- Upvotes on keyword posts: ${keywordUpvotes}`);
  console.log(`- Comments on keyword posts: ${keywordComments}`);
  console.log(`- Keyword Engagement: ${keywordEngagement.toFixed(2)}`);
  
  // Store post statistics for logging
  const postStats = {
    total_posts_fetched: posts.length,
    posts_with_engagement: postsWithEngagement,
    total_upvotes: totalUpvotes,
    total_comments: totalComments,
    avg_upvotes_per_post: avgUpvotesPerPost,
    avg_comments_per_post: avgCommentsPerPost,
    total_post_engagement: totalPostEngagement,
    active_post_engagement: activePostEngagement,
    keyword_posts: keywordPosts,
    keyword_upvotes: keywordUpvotes,
    keyword_comments: keywordComments,
    keyword_engagement: keywordEngagement,
    subscribers: subscribers
  };
  
  // Store post statistics for debugging
  if (!window.postStatsCache) {
    window.postStatsCache = {};
  }
  
  console.log(`=================================================\n`);
  
  // Return all engagement metrics - Include BOTH property names for active post engagement
  // to ensure compatibility with different parts of the code
  return { 
    totalEngagement: totalPostEngagement, 
    activeEngagement: activePostEngagement,      // Include this format for backward compatibility
    active_post_engagement: activePostEngagement, // Include this format for direct use
    keywordEngagement: keywordEngagement
  };
};

const calculateGrowthRate = (subreddit) => {
  const ageInDays = (Date.now() / 1000 - subreddit.created_utc) / 86400;
  return Number(((subreddit.subscribers / ageInDays) * 30).toFixed(2));
};

const calculateOpportunityScore = (metrics, allSubreddits = []) => {
  if (!metrics.subscribers || !metrics.engagement_rate) return 0;

  // Weight assignments for different factors
  const weights = {
    engagement: 0.15,     // General engagement rate
    keyword: 0.25,        // Keyword-specific engagement
    content: 0.25,        // Content relevancy
    base: 0.05,          // Base relevancy
    activity: 0.10,      // Posts per day
    growth: 0.10,        // Growth rate
    size: 0.10           // Subscriber count impact
  };

  // Normalize each metric to a 0-100 scale
  const normalizedMetrics = {
    // Engagement: Log scale to handle wide range of values
    engagement: Math.min(100, 20 * Math.log(metrics.engagement_rate + 1)),
    
    // Keyword engagement: Similar log scale
    keyword: Math.min(100, 20 * Math.log(metrics.keyword_engagement + 1)),
    
    // Content and base relevancy are already 0-100
    content: metrics.content_relevancy_score,
    base: metrics.base_relevancy_score,
    
    // Activity (posts per day): Log scale with diminishing returns
    activity: Math.min(100, 25 * Math.log(metrics.posts_per_day + 1)),
    
    // Growth rate: Log scale to handle exponential growth
    growth: Math.min(100, 20 * Math.log(metrics.growth_rate + 1)),
    
    // Size: Logarithmic subscriber count normalized to 0-100
    size: Math.min(100, 20 * Math.log10(metrics.subscribers + 1))
  };

  // Calculate weighted sum
  const weightedScore = Object.entries(weights).reduce((score, [key, weight]) => {
    return score + (normalizedMetrics[key] * weight);
  }, 0);

  // Apply sigmoid transformation to get final score between 0-100
  // Using a modified sigmoid that's more sensitive in the middle range
  const sigmoid = (x) => 100 / (1 + Math.exp(-0.1 * (x - 50)));
  const finalScore = Number(sigmoid(weightedScore).toFixed(2));

  // Log metrics for analysis
  logOpportunityScoreMetrics({
    subreddit_name: metrics.display_name,
    raw_score: weightedScore,
    normalized_score: finalScore,
    engagement_per_post: metrics.engagement_rate,
    keyword_engagement: metrics.keyword_engagement,
    content_relevancy: metrics.content_relevancy_score,
    base_relevancy: metrics.base_relevancy_score,
    posts_per_day: metrics.posts_per_day,
    growth_rate: metrics.growth_rate,
    subscriber_count: metrics.subscribers,
    normalized_metrics: normalizedMetrics,
    weights: weights
  });

  return finalScore;
};

// Add logging function for opportunity score metrics
const logOpportunityScoreMetrics = async (metrics) => {
  if (!metrics || typeof metrics !== 'object') {
    console.log('Invalid metrics for logging, skipping opportunity score log');
    return;
  }
  
  try {
    // Create a metrics object with all required fields
    const completeMetrics = {
      subreddit_name: metrics.display_name || 'unknown',
      normalized_score: metrics.normalized_score || 0,
      raw_score: metrics.raw_score || 0,
      
      // Required raw metrics
      engagement_per_post: metrics.engagement_rate || 0,
      keyword_engagement: metrics.keyword_engagement || 0,
      posts_per_day: metrics.posts_per_day || 0,
      growth_rate: metrics.growth_rate || 0,
      subscriber_count: metrics.subscribers || 0,
      
      // Required normalized metrics
      normalized_metrics: {
        engagement: metrics.normalized_engagement || 0,
        keyword: metrics.normalized_keyword_engagement || 0,
        content: metrics.content_relevancy_score || 0,
        base: metrics.base_relevancy_score || 0,
        activity: metrics.normalized_activity || 0,
        growth: metrics.normalized_growth_rate || 0,
        size: metrics.normalized_size || 0
      },
      
      timestamp: new Date().toISOString()
    };

    // Log the actual payload size for debugging
    const payloadSize = JSON.stringify(completeMetrics).length;
    console.log(`Sending opportunity metrics (${payloadSize} bytes) for r/${completeMetrics.subreddit_name} (internal app log)`);
    
    // Send complete data to server - directly using api, not rate-limited fetch
    const response = await api.post('/api/log/opportunity-metrics', completeMetrics);
    console.log('Opportunity metrics logging successful (internal app log):', response.status);
  } catch (error) {
    // Log detailed error information but don't disrupt the application
    console.error('Error logging opportunity metrics:', error);
    console.error('Error response:', error.response?.data);
    console.error('Error status:', error.response?.status);
  }
};

// Content analysis functions
const analyzeContentTypes = (posts) => {
  const types = new Set();
  
  posts.forEach(post => {
    if (post.is_self) types.add('text');
    if (post.is_video) types.add('videos');
    if (post.domain === 'i.redd.it' || post.domain === 'imgur.com') types.add('images');
    if (!post.is_self && !post.is_video && post.domain !== 'i.redd.it') types.add('links');
  });
  
  return Array.from(types);
};

// Update analyzeModerationLevel to handle missing data
const analyzeModerationLevel = (basicInfo) => {
  if (!basicInfo) return 'unknown';
  
  const rulesCount = basicInfo.rules?.length || 0;
  const modCount = basicInfo.moderators?.length || 0;
  
  if (rulesCount > 10 || modCount > 5) return 'strict';
  if (rulesCount > 5 || modCount > 2) return 'moderate';
  return 'relaxed';
};

const analyzeTopics = (posts) => {
  // Extract common words from titles and flairs
  const words = posts.flatMap(post => {
    const titleWords = post.title ? post.title.toLowerCase().split(/\W+/) : [];
    const flairWords = post.link_flair_text ? post.link_flair_text.toLowerCase().split(/\W+/) : [];
    return [...titleWords, ...flairWords];
  });
  
  // Count word frequencies and return top 5 topics
  const wordCounts = words.reduce((acc, word) => {
    if (word.length > 3) { // Ignore short words
      acc[word] = (acc[word] || 0) + 1;
    }
    return acc;
  }, {});
  
  return Object.entries(wordCounts)
    .sort(([,a], [,b]) => b - a)
    .slice(0, 5)
    .map(([word]) => word);
};

// Helper functions
const formatNumber = (num) => {
  if (!num) return '0';
  if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
  if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
  return num.toString();
};

const formatPercentage = (value) => {
  if (value === null || value === undefined || isNaN(value)) return 'N/A';
  
  // For very small values (less than 0.1%), show 2 decimal places
  if (value > 0 && value < 0.1) {
    return value.toFixed(2) + '%';
  }
  
  // For normal values, show 1 decimal place
  return value.toFixed(1) + '%';
};

const getActivityLevel = (postsPerDay) => {
  if (postsPerDay >= 50) return 'high';
  if (postsPerDay >= 10) return 'medium';
  return 'low';
};

const getOpportunityScoreClass = (score) => {
  if (score >= 80) return 'bg-green-100 text-green-800';
  if (score >= 60) return 'bg-blue-100 text-blue-800';
  if (score >= 40) return 'bg-yellow-100 text-yellow-800';
  return 'bg-red-100 text-red-800';
};

// Actions
const trackSubreddit = async (subreddit) => {
  try {
    await api.post(`/api/reddit/subreddits/${subreddit.name}/track`);
    selectedSubreddits.value.add(subreddit.name);
  } catch (error) {
    console.error('Error tracking subreddit:', error);
  }
};

const viewSubredditDetails = (subreddit) => {
  router.push({
    name: 'subreddit-detail',
    params: {
      name: subreddit.name
    }
  });
};

const exportResults = () => {
  const data = processedResults.value.map(subreddit => ({
    name: subreddit.display_name,
    subscribers: subreddit.subscribers,
    active_users: subreddit.active_user_count,
    posts_per_day: subreddit.posts_per_day,
    engagement_rate: subreddit.engagement_rate,
    growth_rate: subreddit.growth_rate,
    opportunity_score: subreddit.opportunity_score,
    topics: subreddit.topics.join(', '),
    content_types: subreddit.content_types.join(', '),
    moderation_level: subreddit.moderation_level,
    description: subreddit.public_description
  }));

  const csv = convertToCSV(data);
  downloadCSV(csv, 'subreddit-analysis.csv');
};

const convertToCSV = (data) => {
  const headers = Object.keys(data[0]);
  const rows = data.map(obj => headers.map(header => JSON.stringify(obj[header])));
  return [headers, ...rows].map(row => row.join(',')).join('\n');
};

const downloadCSV = (csv, filename) => {
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.download = filename;
  link.click();
  URL.revokeObjectURL(link.href);
};

// Add this method for pagination
const changePage = (page) => {
  currentPage.value = page;
  // Scroll back to top of results when changing pages
  document.querySelector('.results-container')?.scrollTo(0, 0);
};

// Add formatEngagement function
const formatEngagement = (value) => {
  if (value === null || value === undefined || isNaN(value)) return 'N/A';
  
  // For very small values, show 2 decimal places
  if (value > 0 && value < 1) {
    return value.toFixed(2);
  }
  
  // For normal values, show 1 decimal place
  return value.toFixed(1);
};

// Add methods
const toggleSubredditSelection = (subreddit) => {
  console.log('Toggle subreddit selection called for:', subreddit.display_name);
  
  try {
    if (selectedSubreddits.value.has(subreddit.display_name)) {
      console.log('Removing subreddit from selection');
      // Create a new Map to ensure reactivity
      const newMap = new Map(selectedSubreddits.value);
      newMap.delete(subreddit.display_name);
      selectedSubreddits.value = newMap;
  } else {
      console.log('Adding subreddit to selection');
      // Create a clean copy with all metrics properly set before adding to the map
      const cleanSubreddit = { ...subreddit };
      
      // Ensure base_relevancy_score and content_relevancy_score are numeric values
      cleanSubreddit.base_relevancy_score = typeof cleanSubreddit.base_relevancy_score === 'number' ? 
        cleanSubreddit.base_relevancy_score : parseFloat(cleanSubreddit.base_relevancy_score) || 0;
        
      cleanSubreddit.content_relevancy_score = typeof cleanSubreddit.content_relevancy_score === 'number' ? 
        cleanSubreddit.content_relevancy_score : parseFloat(cleanSubreddit.content_relevancy_score) || 0;
      
      // Ensure active_post_engagement has the correct value
      if (!cleanSubreddit.active_post_engagement || cleanSubreddit.active_post_engagement === 0) {
        // Try to get it from activeEngagement or active_engagement fields
        if (cleanSubreddit.activeEngagement && cleanSubreddit.activeEngagement !== 0) {
          cleanSubreddit.active_post_engagement = cleanSubreddit.activeEngagement;
        } else if (cleanSubreddit.active_engagement && cleanSubreddit.active_engagement !== 0) {
          cleanSubreddit.active_post_engagement = cleanSubreddit.active_engagement;
        } else {
          // If all else fails, use the engagement_rate as a fallback
          cleanSubreddit.active_post_engagement = cleanSubreddit.engagement_rate || 0;
        }
      }
      
      console.log(`DEBUG - Setting subreddit ${subreddit.display_name} with metrics:`, {
        posts_per_day: cleanSubreddit.posts_per_day,
        opportunity_score: cleanSubreddit.opportunity_score,
        engagement_rate: cleanSubreddit.engagement_rate,
        growth_rate: cleanSubreddit.growth_rate,
        active_post_engagement: cleanSubreddit.active_post_engagement,
        keyword_engagement: cleanSubreddit.keyword_engagement,
        base_relevancy_score: cleanSubreddit.base_relevancy_score,
        content_relevancy_score: cleanSubreddit.content_relevancy_score
      });
      
      // Create a new Map to ensure reactivity
      const newMap = new Map(selectedSubreddits.value);
      newMap.set(subreddit.display_name, cleanSubreddit);
      selectedSubreddits.value = newMap;
      
      console.log('Selected subreddits count:', selectedSubreddits.value.size);
    }
  } catch (error) {
    console.error('Error toggling subreddit selection:', error);
  }
};

const clearSelection = () => {
  selectedSubreddits.value = new Map();
};

const viewAudience = () => {
  const selectedIds = Array.from(selectedSubreddits).map(name => {
    const subreddit = results.value.find(r => r.name === name);
    return subreddit.id;
  });
  
  router.push({
    name: 'audience-detail',
    params: {
      ids: selectedIds.join(',')
    }
  });
};

const compareSelected = () => {
  const selectedIds = Array.from(selectedSubreddits.value.values()).map(subreddit => {
    return subreddit.id;
  });
  
  router.push({
    name: 'comparison-tool',
    query: {
      ids: selectedIds.join(',')
    }
  });
};

// Audience builder state
const showSaveAudienceModal = ref(false);
const audienceName = ref('');
const audienceDescription = ref('');
const showStatusModal = ref(false);
const savedAudienceId = ref(null);

// Methods
const saveAudience = async () => {
  if (!audienceName.value.trim()) return;
  
  try {
    // Get the full subreddit data for each selected subreddit
    const selectedSubredditData = Array.from(selectedSubreddits.value.entries()).map(([name, subreddit]) => {
      // Debug log for metrics availability
      console.log(`DEBUG - Selected subreddit metrics for ${name}:`, {
        posts_per_day: subreddit.posts_per_day,
        opportunity_score: subreddit.opportunity_score,
        engagement_rate: subreddit.engagement_rate,
        growth_rate: subreddit.growth_rate,
        active_post_engagement: subreddit.active_post_engagement,
        active_engagement: subreddit.active_engagement, // Check if this variant exists
        keyword_engagement: subreddit.keyword_engagement
      });
      
      // Fix: Handle the case where active_post_engagement might be stored as active_engagement
      // Get the non-zero value between the two possible field names
      const activePostEngagementValue = 
        (subreddit.active_post_engagement !== undefined && !isNaN(subreddit.active_post_engagement) && subreddit.active_post_engagement !== 0)
          ? parseFloat(subreddit.active_post_engagement)
          : (subreddit.active_engagement !== undefined && !isNaN(subreddit.active_engagement) && subreddit.active_engagement !== 0)
            ? parseFloat(subreddit.active_engagement)
            : (subreddit.activeEngagement !== undefined && !isNaN(subreddit.activeEngagement) && subreddit.activeEngagement !== 0)
              ? parseFloat(subreddit.activeEngagement)
              : parseFloat(subreddit.engagement_rate) || 0; // Fallback to engagement_rate if nothing else is available
      
      // Create a clean version of the subreddit data without circular references
      const cleanData = {
        id: subreddit.id,
        display_name: subreddit.display_name,
        title: subreddit.title || '',
        subscribers: subreddit.subscribers || 0,
        active_user_count: subreddit.active_user_count || 0,
        icon_img: subreddit.icon_img || '',
        public_description: subreddit.public_description || '',
        created_utc: subreddit.created_utc || 0,
        
        // Include only essential metrics directly at the root level, avoid duplication
        posts_per_day: subreddit.posts_per_day !== undefined && !isNaN(subreddit.posts_per_day) 
          ? parseFloat(subreddit.posts_per_day) : 0,
        opportunity_score: subreddit.opportunity_score !== undefined && !isNaN(subreddit.opportunity_score) 
          ? parseFloat(subreddit.opportunity_score) : 0,
        engagement_rate: subreddit.engagement_rate !== undefined && !isNaN(subreddit.engagement_rate) 
          ? parseFloat(subreddit.engagement_rate) : 0,
        growth_rate: subreddit.growth_rate !== undefined && !isNaN(subreddit.growth_rate) 
          ? parseFloat(subreddit.growth_rate) : 0,
        active_post_engagement: activePostEngagementValue, // Use the fixed value
        keyword_engagement: subreddit.keyword_engagement !== undefined && !isNaN(subreddit.keyword_engagement) 
          ? parseFloat(subreddit.keyword_engagement) : 0
      };
      
      // Log the metrics data being saved
      console.log(`DEBUG - Saving metrics for r/${name}:`, {
        posts_per_day: cleanData.posts_per_day,
        opportunity_score: cleanData.opportunity_score,
        engagement_rate: cleanData.engagement_rate,
        growth_rate: cleanData.growth_rate,
        active_post_engagement: cleanData.active_post_engagement, // Should now show correct value
        keyword_engagement: cleanData.keyword_engagement
      });
      
      return {
        name,
        data: cleanData
      };
    });
    
    // Log the entire payload being sent to the server
    console.log('DEBUG - Saving audience with subreddits:', selectedSubredditData);
    
    // Save the audience
    const response = await api.post('/api/audiences', {
      name: audienceName.value,
      description: audienceDescription.value,
      subreddits: selectedSubredditData
    });
    
    if (response.data.status === 'success') {
      toast.success('Audience saved successfully!');
      
      // Store the saved audience ID and show the status modal
      savedAudienceId.value = response.data.data.audience.id;
      audienceName.value = '';
      audienceDescription.value = '';
      showSaveAudienceModal.value = false;
      selectedSubreddits.value.clear();
      
      // Show the status modal
      showStatusModal.value = true;
      
      // Check if metrics were properly saved in the response
      console.log('DEBUG - Server response from saving audience:', response.data);
    }
  } catch (error) {
    console.error('Error saving audience:', error);
    toast.error('Failed to save audience');
  }
};

const handleStatusModalClose = () => {
  showStatusModal.value = false;
  
  // Use a slight delay before navigation to ensure the modal is fully unmounted
  setTimeout(() => {
    // Navigate to the dashboard after closing the modal
    router.push('/dashboard/saved-audiences');
  }, 100);
};

const removeFromSelection = (subredditName) => {
  console.log('Removing subreddit from selection:', subredditName);
  const newMap = new Map(selectedSubreddits.value);
  newMap.delete(subredditName);
  selectedSubreddits.value = newMap;
};

// Watch for changes in search query
watch(searchQuery, () => {
  // Reset results when query changes - don't modify processedResults directly
  results.value = [];
  totalResults.value = 0;
});

// Add watch to sync filters with store
// Add this after the existing watch statements
watch(filters, (newFilters) => {
  console.log('Syncing filters to store:', newFilters);
  // Clone the filters to avoid reactivity issues
  const filtersToSync = JSON.parse(JSON.stringify(newFilters));
  searchStore.setFilters(filtersToSync);
}, { deep: true });

// Watch for store filter changes to sync back to component
watch(() => searchStore.filters, (newFilters) => {
  console.log('Store filters changed, updating component:', newFilters);
  // Update local filters without triggering the watch above
  Object.keys(filters).forEach(key => {
    if (key === 'contentTypes') {
      Object.keys(filters.contentTypes).forEach(type => {
        filters.contentTypes[type] = newFilters.contentTypes?.[type] || false;
      });
    } else {
      filters[key] = newFilters[key] !== undefined ? newFilters[key] : filters[key];
    }
  });
}, { deep: true });

// Add a watch for sortBy to sync with store
watch(sortBy, (newSort) => {
  console.log('Syncing sort criteria to store:', newSort);
  // Map our sort values to the store's expected values
  let storeSort = 'opportunity_score';
  switch(newSort) {
    case 'subscribers':
      storeSort = 'subscribers';
      break;
    case 'growth':
      storeSort = 'growth_rate';
      break;
    case 'engagement':
      storeSort = 'engagement_rate';
      break;
    case 'opportunity':
    default:
      storeSort = 'opportunity_score';
      break;
  }
  searchStore.setSortBy(storeSort);
});

// Watch selectedSubreddits for debugging
watch(selectedSubreddits, (newValue) => {
  console.log('selectedSubreddits changed, new size:', newValue.size);
}, { deep: true });
</script> 
<style scoped>
.accent-blue-600::-webkit-slider-thumb {
  background: #2563eb;
}
.accent-blue-600::-moz-range-thumb {
  background: #2563eb;
}
</style> 
