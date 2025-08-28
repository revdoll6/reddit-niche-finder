<template>
  <div class="min-h-screen bg-gray-900">
    <!-- Header -->
    <div class="bg-gray-800 border-b border-gray-700">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <button
              @click="$router.back()"
              class="mr-4 text-gray-400 hover:text-white"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
            </button>
            <div>
              <h1 class="text-2xl font-bold text-white">{{ audienceName || 'Untitled Audience' }}</h1>
              <p class="text-gray-400 text-sm mt-1">{{ subreddits.length }} subreddits selected</p>
            </div>
          </div>
          <div class="flex items-center space-x-4">
            <button
              @click="saveAudience"
              v-if="!isExistingAudience"
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
            >
              Save Audience
            </button>
            <button
              @click="exportReport"
              class="px-4 py-2 bg-gray-700 text-white rounded-md hover:bg-gray-600 transition-colors"
            >
              Export Report
            </button>
          </div>
        </div>
        
        <!-- Tabs -->
        <div class="flex space-x-8 mt-6">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="currentTab = tab.id"
            :class="[
              'text-sm font-medium pb-4 border-b-2',
              currentTab === tab.id
                ? 'text-blue-500 border-blue-500'
                : 'text-gray-400 border-transparent hover:text-gray-300 hover:border-gray-300'
            ]"
          >
            {{ tab.name }}
          </button>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 flex justify-center">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
    </div>

    <!-- Tab Content -->
    <div v-else class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Overview Tab -->
      <div v-if="currentTab === 'overview'" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="bg-gray-800 rounded-lg p-4">
            <h3 class="text-base font-medium text-white mb-3">Total Reach</h3>
            <div class="mt-1">
              <div class="text-2xl font-bold text-white">{{ formatNumber(totalSubscribers) }}</div>
              <p class="text-gray-400 text-xs mt-1">Combined subscribers</p>
            </div>
          </div>
          
          <div class="bg-gray-800 rounded-lg p-4">
            <h3 class="text-base font-medium text-white mb-3">Average Engagement</h3>
            <div class="mt-1">
              <div class="text-2xl font-bold text-white">
                {{ isNaN(averageEngagement) ? 'N/A' : averageEngagement.toFixed(1) }}
              </div>
              <p class="text-gray-400 text-xs mt-1">Across all subreddits</p>
            </div>
          </div>
          
          <div class="bg-gray-800 rounded-lg p-4">
            <h3 class="text-base font-medium text-white mb-3">Growth Rate</h3>
            <div class="mt-1">
              <div class="text-2xl font-bold text-green-500">
                {{ isNaN(averageGrowth) ? 'N/A' : '+' + averageGrowth.toFixed(1) + '%' }}
              </div>
              <p class="text-gray-400 text-xs mt-1">Monthly average</p>
            </div>
          </div>
        </div>
        
        <!-- Subreddits Grid -->
        <div class="bg-gray-800 rounded-lg p-4">
          <h3 class="text-base font-medium text-white mb-4">Selected Subreddits</h3>
          
          <div v-if="subreddits.length === 0" class="text-center py-8">
            <p class="text-gray-400 text-sm">No subreddits selected. Add subreddits from the Explorer page.</p>
            <router-link
              to="/discovery/explorer"
              class="inline-block px-3 py-1.5 mt-3 bg-blue-600 text-white text-xs rounded-md hover:bg-blue-700 transition-colors"
            >
              Go to Explorer
            </router-link>
          </div>
          
          <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
              v-for="subreddit in subreddits"
              :key="subreddit.name"
              class="bg-gray-800 border border-gray-700 rounded-lg hover:border-blue-500 transition-all duration-200 overflow-hidden shadow-sm"
            >
              <!-- Subreddit Header -->
              <div class="p-3 border-b border-gray-700">
                <div class="flex items-center gap-2">
                  <div class="subreddit-icon">
                    <img v-if="subreddit.icon_img" :src="subreddit.icon_img" 
                      class="w-8 h-8 rounded-full bg-gray-700"
                      alt="Subreddit icon"
                      @error="$event.target.src = 'https://www.redditstatic.com/desktop2x/img/favicon/apple-icon-57x57.png'"
                    />
                    <div v-else class="w-8 h-8 rounded-full bg-gray-700 flex items-center justify-center text-white text-xs font-bold">
                      r/
                    </div>
                  </div>
                  <div>
                    <h4 class="text-sm font-medium text-white">r/{{ subreddit.name }}</h4>
                    <p class="text-xs text-gray-400">{{ formatNumber(subreddit.subscribers) }} members</p>
                  </div>
                </div>
              </div>
              
              <!-- Subreddit Stats -->
              <div class="p-4">
                <!-- Metrics Grid -->
                <div class="p-3">
                  <!-- Metrics Grid -->
                  <div class="grid grid-cols-3 gap-2">
                    <!-- Engagement Rate -->
                    <div class="bg-gray-700 rounded-lg p-2">
                      <div class="text-xs text-gray-400 mb-0.5">Engagement</div>
                      <div class="text-sm font-bold text-white">
                        {{ formatValue(subreddit.engagement_rate) }}
                      </div>
                    </div>
                    
                    <!-- Growth Rate -->
                    <div class="bg-gray-700 rounded-lg p-2">
                      <div class="text-xs text-gray-400 mb-0.5">Growth</div>
                      <div class="text-sm font-bold text-green-400">
                        +{{ formatValue(subreddit.growth_rate, '%') }}
                      </div>
                    </div>
                    
                    <!-- Posts per Day -->
                    <div class="bg-gray-700 rounded-lg p-2">
                      <div class="text-xs text-gray-400 mb-0.5">Posts/Day</div>
                      <div class="text-sm font-bold text-white">
                        {{ formatValue(subreddit.posts_per_day) }}
                      </div>
                    </div>
                    
                    <!-- Opportunity Score -->
                    <div class="bg-gray-700 rounded-lg p-2">
                      <div class="text-xs text-gray-400 mb-0.5">Opportunity</div>
                      <div class="text-sm font-bold text-white">
                        {{ formatValue(subreddit.opportunity_score) }}
                      </div>
                    </div>
                    
                    <!-- Active Post Engagement -->
                    <div class="bg-gray-700 rounded-lg p-2">
                      <div class="text-xs text-gray-400 mb-0.5">Active Eng</div>
                      <div class="text-sm font-bold text-white">
                        {{ formatValue(subreddit.active_post_engagement) }}
                      </div>
                    </div>
                    
                    <!-- Keyword Engagement -->
                    <div class="bg-gray-700 rounded-lg p-2">
                      <div class="text-xs text-gray-400 mb-0.5">Keyword Eng</div>
                      <div class="text-sm font-bold text-white">
                        {{ formatValue(subreddit.keyword_engagement) }}
                      </div>
                    </div>
                  </div>
                  
                  <!-- Actions -->
                  <div class="flex gap-2 mt-3">
                    <a 
                      :href="`https://reddit.com/r/${subreddit.display_name || subreddit.name}`" 
                      target="_blank"
                      class="flex-1 py-1 text-xs px-2 bg-gray-700 text-gray-300 hover:bg-gray-600 rounded-lg text-center"
                    >
                      Visit
                    </a>
                    <button 
                      @click="viewSubredditDetails(subreddit)"
                      class="flex-1 py-1 text-xs px-2 bg-blue-600 text-white hover:bg-blue-700 rounded-lg text-center"
                    >
                      Details
                    </button>
                    <button 
                      @click="removeSubreddit(subreddit)"
                      class="py-1 px-2 bg-red-600 text-white hover:bg-red-700 rounded-lg"
                    >
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Combined Metrics Tab -->
      <div v-if="currentTab === 'metrics'" class="space-y-8">
        <div class="bg-gray-800 rounded-lg p-6">
          <h3 class="text-lg font-medium text-white mb-6">Engagement Distribution</h3>
          <div class="h-64 flex items-center justify-center text-gray-400">
            <p>Engagement metrics visualization coming soon</p>
          </div>
        </div>

        <div class="bg-gray-800 rounded-lg p-6">
          <h3 class="text-lg font-medium text-white mb-6">Subreddit Comparison</h3>
          <div class="overflow-x-auto">
            <table class="w-full text-left">
              <thead>
                <tr class="border-b border-gray-700">
                  <th class="py-3 px-4 text-gray-400 font-medium">Subreddit</th>
                  <th class="py-3 px-4 text-gray-400 font-medium">Subscribers</th>
                  <th class="py-3 px-4 text-gray-400 font-medium">Engagement</th>
                  <th class="py-3 px-4 text-gray-400 font-medium">Posts/Day</th>
                  <th class="py-3 px-4 text-gray-400 font-medium">Growth</th>
                  <th class="py-3 px-4 text-gray-400 font-medium">Score</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="subreddit in subreddits" :key="subreddit.name" class="border-b border-gray-700">
                  <td class="py-3 px-4 text-white">r/{{ subreddit.display_name || subreddit.name }}</td>
                  <td class="py-3 px-4 text-white">{{ formatNumber(subreddit.subscribers) }}</td>
                  <td class="py-3 px-4 text-white">{{ formatValue(subreddit.engagement_rate) }}</td>
                  <td class="py-3 px-4 text-white">{{ formatValue(subreddit.posts_per_day) }}</td>
                  <td class="py-3 px-4 text-green-400">+{{ formatValue(subreddit.growth_rate, '%') }}</td>
                  <td class="py-3 px-4 text-white">{{ formatValue(subreddit.opportunity_score) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Content Analysis Tab -->
      <div v-if="currentTab === 'content'" class="space-y-8">
        <div v-if="loadingPosts" class="bg-gray-800 rounded-lg p-6 flex justify-center items-center">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
          <span class="ml-3 text-gray-300">Loading posts...</span>
        </div>
        
        <div v-else-if="subredditPosts.length === 0" class="bg-gray-800 rounded-lg p-6">
          <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-400">No posts found</h3>
            <p class="mt-1 text-sm text-gray-500">
              No posts have been fetched yet or the fetch process is still in progress.
            </p>
            <div class="mt-6">
              <button @click="fetchPosts" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                Refresh Posts
              </button>
            </div>
          </div>
        </div>
        
        <div v-else v-for="subredditData in subredditPosts" :key="subredditData.subreddit_name" class="bg-gray-800 rounded-lg p-6">
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-medium text-white">r/{{ subredditData.subreddit_name }}</h3>
            <span class="text-xs text-gray-400">{{ subredditData.post_count }} posts • Last updated: {{ formatDateTime(subredditData.fetched_at) }}</span>
          </div>
          
          <div class="space-y-4">
            <div v-for="(post, index) in subredditData.posts.slice(0, expandedSubreddits[subredditData.subreddit_name] ? undefined : 5)" 
                :key="post.id" 
                class="border border-gray-700 rounded-lg p-4 hover:border-gray-600 transition-colors">
              <div class="flex justify-between">
                <h4 class="text-sm font-medium text-white mb-2">{{ post.title }}</h4>
                <div class="flex items-center text-xs text-gray-400">
                  <span class="mr-3">
                    <svg class="inline-block w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z" />
                    </svg>
                    {{ post.score }}
                  </span>
                  <span>
                    <svg class="inline-block w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zM7 8H5v2h2V8zm2 0h2v2H9V8zm6 0h-2v2h2V8z" clip-rule="evenodd" />
                    </svg>
                    {{ post.num_comments }}
                  </span>
                </div>
              </div>
              <p v-if="post.selftext" class="text-xs text-gray-300 mt-2 line-clamp-3">{{ post.selftext }}</p>
              <div class="mt-3 flex justify-between items-center">
                <div>
                  <span v-if="post.link_flair_text" class="inline-block px-2 py-1 text-xs bg-gray-700 rounded text-gray-300">
                    {{ post.link_flair_text }}
                  </span>
                </div>
                <a :href="`https://reddit.com${post.permalink}`" target="_blank" class="text-xs text-blue-400 hover:text-blue-300">
                  View on Reddit
                </a>
              </div>
            </div>
            
            <div v-if="subredditData.posts.length > 5" class="text-center pt-2">
              <button 
                @click="toggleExpand(subredditData.subreddit_name)" 
                class="px-4 py-2 text-xs text-blue-400 hover:text-blue-300"
              >
                {{ expandedSubreddits[subredditData.subreddit_name] ? 'Show Less' : `Show All (${subredditData.posts.length})` }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Member Analysis Tab -->
      <div v-if="currentTab === 'members'" class="space-y-8">
        <div class="bg-gray-800 rounded-lg p-6">
          <h3 class="text-lg font-medium text-white mb-6">Audience Demographics</h3>
          <div class="h-64 flex items-center justify-center text-gray-400">
            <p>Demographics visualization coming soon</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Save Audience Dialog -->
    <div v-if="showSaveDialog" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
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
            <label class="block text-sm font-medium text-gray-400 mb-1">Description</label>
            <textarea
              v-model="audienceDescription"
              class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              rows="3"
              placeholder="Add a description..."
            ></textarea>
          </div>
          <div class="flex justify-end space-x-4 mt-6">
            <button
              @click="showSaveDialog = false"
              class="px-4 py-2 text-gray-400 hover:text-white transition-colors"
            >
              Cancel
            </button>
            <button
              @click="handleSaveAudience"
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
            >
              Save
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../services/api';

const route = useRoute();
const router = useRouter();

// Define a toast utility function for notifications
const toast = {
  success: (message) => {
    console.log('SUCCESS:', message);
    alert(`✅ ${message}`);
  },
  error: (message) => {
    console.error('ERROR:', message);
    alert(`❌ ${message}`);
  },
  info: (message) => {
    console.log('INFO:', message);
    alert(`ℹ️ ${message}`);
  }
};

const subreddits = ref([]);
const currentTab = ref('overview');
const loading = ref(true);
const showSaveDialog = ref(false);
const audienceName = ref('');
const audienceDescription = ref('');
const isExistingAudience = ref(false);
const audienceId = ref(null);

// References for posts display
const subredditPosts = ref([]);
const loadingPosts = ref(false);
const expandedSubreddits = ref({});

const tabs = [
  { id: 'overview', name: 'Overview' },
  { id: 'metrics', name: 'Combined Metrics' },
  { id: 'content', name: 'Content Analysis' },
  { id: 'members', name: 'Member Analysis' }
];

// Computed properties for metrics
const totalSubscribers = computed(() => {
  return subreddits.value.reduce((total, sr) => total + (Number(sr.subscribers) || 0), 0);
});

const averageEngagement = computed(() => {
  if (subreddits.value.length === 0) return 0;
  const validEngagements = subreddits.value
    .map(sr => Number(sr.engagement_rate))
    .filter(rate => !isNaN(rate));
  return validEngagements.length > 0 
    ? validEngagements.reduce((total, rate) => total + rate, 0) / validEngagements.length
    : NaN;
});

const averageGrowth = computed(() => {
  if (subreddits.value.length === 0) return 0;
  const validGrowth = subreddits.value
    .map(sr => Number(sr.growth_rate))
    .filter(rate => !isNaN(rate));
  return validGrowth.length > 0
    ? validGrowth.reduce((total, rate) => total + rate, 0) / validGrowth.length
    : NaN;
});

// Watch for tab changes to load posts when Content Analysis tab is selected
watch(currentTab, (newTab) => {
  if (newTab === 'content' && audienceId.value && subredditPosts.value.length === 0) {
    fetchPosts();
  }
});

onMounted(async () => {
  try {
    if (route.params.ids) {
      // Check if it's a numeric ID (existing audience) or a comma-separated list
      if (!isNaN(route.params.ids) && !route.params.ids.includes(',')) {
        // It's an existing audience ID
        audienceId.value = parseInt(route.params.ids);
        isExistingAudience.value = true;
        await fetchAudience(audienceId.value);
        
        // If Content Analysis tab is selected initially, fetch posts
        if (currentTab.value === 'content') {
          fetchPosts();
        }
      } else {
        // It's a comma-separated list of subreddit IDs or names
        const ids = route.params.ids.split(',');
        await fetchSubreddits(ids);
      }
    }
  } catch (error) {
    console.error('Error fetching audience details:', error);
    toast.error('Failed to load audience details');
  } finally {
    loading.value = false;
  }
});

const fetchAudience = async (id) => {
  try {
    const response = await api.get(`/api/audiences/${id}`);
    if (response.data.status === 'success') {
      const audience = response.data.data;
      audienceName.value = audience.name;
      audienceDescription.value = audience.description || '';
      
      // Log the raw audience data from API
      console.log('DEBUG - Raw audience data from API:', audience);
      
      // Extract subreddit data from the audience
      if (audience.subreddits && audience.subreddits.length) {
        // Log the first subreddit as a sample to check its structure
        if (audience.subreddits.length > 0) {
          console.log('DEBUG - Sample subreddit from API:', audience.subreddits[0]);
          // Check both direct properties and subreddit_data
          const sampleData = audience.subreddits[0].subreddit_data || {};
          console.log('DEBUG - Sample subreddit metrics:', {
            posts_per_day: sampleData.posts_per_day,
            opportunity_score: sampleData.opportunity_score,
            engagement_rate: sampleData.engagement_rate,
            growth_rate: sampleData.growth_rate,
            active_post_engagement: sampleData.active_post_engagement,
            keyword_engagement: sampleData.keyword_engagement
          });
        }
      
        subreddits.value = audience.subreddits.map(sr => {
          // Access the data properly depending on the structure
          const data = sr.subreddit_data || {};
          
          // Create a properly formatted subreddit object with all metrics
          return {
            id: data.id || '',
            name: sr.subreddit_name,
            display_name: sr.subreddit_name,
            title: data.title || '',
            subscribers: data.subscribers || 0,
            icon_img: data.icon_img || '',
            active_users: data.active_user_count || 0,
            
            // Extract all metrics directly from data object, with fallbacks
            posts_per_day: parseFloat(data.posts_per_day) || 0,
            opportunity_score: parseFloat(data.opportunity_score) || 0,
            engagement_rate: parseFloat(data.engagement_rate) || 0,
            growth_rate: parseFloat(data.growth_rate) || 0,
            active_post_engagement: parseFloat(data.active_post_engagement) || 0,
            keyword_engagement: parseFloat(data.keyword_engagement) || 0
          };
        });

        // Log processed subreddits with metrics for verification
        console.log('DEBUG - Processed subreddits with metrics:', 
          subreddits.value.map(sr => ({
            name: sr.name,
            posts_per_day: sr.posts_per_day,
            opportunity_score: sr.opportunity_score,
            engagement_rate: sr.engagement_rate
          }))
        );
      }
    }
  } catch (error) {
    console.error('Error fetching audience:', error);
    toast.error('Failed to load audience');
  }
};

const fetchSubreddits = async (ids) => {
  try {
    // Determine if we're using names or IDs
    let subredditsData = [];
    
    if (ids.some(id => isNaN(id) || id.includes('_'))) {
      // These are likely subreddit names, not IDs
      const subredditPromises = ids.map(name => 
        api.get(`/api/reddit/subreddits/${name}`)
      );
      
      const responses = await Promise.all(subredditPromises);
      subredditsData = responses.map(response => response.data).filter(Boolean);
    } else {
      // These are likely numeric IDs
      const response = await api.get('/api/reddit/subreddits/batch', { params: { ids } });
      subredditsData = response.data || [];
    }
    
    // Log the raw data for debugging
    console.log('Raw subreddits data from API:', subredditsData);
    
    // Process subreddits to ensure all required metrics exist
    subreddits.value = subredditsData.map(sr => {
      // Extract metrics or use defaults if they don't exist
      const calculatedMetrics = sr.calculated_metrics || {};
      
      // Create a properly formatted subreddit object with all metrics
      return {
        ...sr,
        // Extract metrics with proper fallback chain
        engagement_rate: sr.engagement_rate || calculatedMetrics.engagement_rate || 0,
        growth_rate: sr.growth_rate || calculatedMetrics.growth_rate || 0, 
        posts_per_day: sr.posts_per_day || calculatedMetrics.posts_per_day || 0,
        opportunity_score: sr.opportunity_score || calculatedMetrics.opportunity_score || 0,
        active_post_engagement: sr.active_post_engagement || 
                                sr.active_engagement || 
                                calculatedMetrics.active_post_engagement || 
                                calculatedMetrics.active_engagement || 
                                0,
        keyword_engagement: sr.keyword_engagement || calculatedMetrics.keyword_engagement || 0
      };
    });
    
    // Log the processed subreddits with metrics
    console.log('Processed subreddits with metrics:', subreddits.value);
    
  } catch (error) {
    console.error('Error fetching subreddits:', error);
    toast.error('Failed to load subreddits');
  }
};

const formatNumber = (num) => {
  if (num === null || num === undefined || isNaN(num)) return 'N/A';
  if (num >= 1000000) {
    return (num / 1000000).toFixed(1) + 'M';
  } else if (num >= 1000) {
    return (num / 1000).toFixed(1) + 'K';
  }
  return Math.round(num).toString();
};

const formatValue = (value, suffix = '') => {
  // Check for null or undefined values
  if (value === null || value === undefined) {
    return 'N/A';
  }
  
  // Convert strings to numbers if they're numeric
  if (typeof value === 'string' && !isNaN(value)) {
    value = parseFloat(value);
  }
  
  // Check if it's a valid number
  if (typeof value !== 'number' || isNaN(value)) {
    return 'N/A';
  }
  
  // For actual zero values, just show '0'
  if (value === 0) {
    return '0' + suffix;
  }
  
  // For very small values, show 2 decimal places
  if (value > 0 && value < 1) {
    return value.toFixed(2) + suffix;
  }
  
  // For normal values, show 1 decimal place
  return value.toFixed(1) + suffix;
};

const removeSubreddit = (subreddit) => {
  subreddits.value = subreddits.value.filter(sr => 
    sr.name !== subreddit.name && sr.display_name !== subreddit.display_name
  );
};

const viewSubredditDetails = (subreddit) => {
  const name = subreddit.display_name || subreddit.name;
  router.push(`/discovery/subreddit/${name}`);
};

const saveAudience = () => {
  if (subreddits.value.length === 0) {
    toast.error('You need to select at least one subreddit');
    return;
  }
  showSaveDialog.value = true;
};

const handleSaveAudience = async () => {
  if (!audienceName.value.trim()) {
    toast.error('Please enter an audience name');
    return;
  }
  
  try {
    // Ensure all metrics are included when saving
    const subredditData = subreddits.value.map(sr => ({
      name: sr.display_name || sr.name,
      data: {
        id: sr.id,
        display_name: sr.display_name || sr.name,
        title: sr.title || '',
        icon_img: sr.icon_img || '',
        subscribers: sr.subscribers || 0,
        active_user_count: sr.active_user_count || sr.active_users || 0,
        public_description: sr.public_description || '',
        created_utc: sr.created_utc || 0,
        
        // Make sure to include all metrics with proper fallbacks
        engagement_rate: typeof sr.engagement_rate === 'number' ? sr.engagement_rate : 0,
        growth_rate: typeof sr.growth_rate === 'number' ? sr.growth_rate : 0,
        posts_per_day: typeof sr.posts_per_day === 'number' ? sr.posts_per_day : 0,
        opportunity_score: typeof sr.opportunity_score === 'number' ? sr.opportunity_score : 0,
        active_post_engagement: typeof sr.active_post_engagement === 'number' ? sr.active_post_engagement : 0,
        keyword_engagement: typeof sr.keyword_engagement === 'number' ? sr.keyword_engagement : 0,
        
        // Include calculated_metrics to ensure complete data preservation
        calculated_metrics: {
        engagement_rate: sr.engagement_rate || 0,
        growth_rate: sr.growth_rate || 0,
        posts_per_day: sr.posts_per_day || 0,
        opportunity_score: sr.opportunity_score || 0,
        active_post_engagement: sr.active_post_engagement || 0,
        keyword_engagement: sr.keyword_engagement || 0
        }
      }
    }));
    
    // Log what we're saving
    console.log('Saving audience with subreddit data:', subredditData);
    
    const response = await api.post('/api/audiences', {
      name: audienceName.value,
      description: audienceDescription.value,
      subreddits: subredditData
    });
    
    showSaveDialog.value = false;
    toast.success('Audience saved successfully');
    
    // Redirect to dashboard
    router.push('/dashboard/saved-audiences');
  } catch (error) {
    console.error('Error saving audience:', error);
    toast.error('Failed to save audience');
  }
};

const exportReport = async () => {
  if (subreddits.value.length === 0) {
    toast.error('You need to select at least one subreddit');
    return;
  }
  
  toast.info('Export functionality coming soon');
  
  // Placeholder for future export functionality
  // try {
  //   const response = await api.get('/api/audiences/export', {
  //     params: {
  //       subreddits: subreddits.value.map(sr => sr.id)
  //     },
  //     responseType: 'blob'
  //   });
  //   
  //   const url = window.URL.createObjectURL(new Blob([response.data]));
  //   const link = document.createElement('a');
  //   link.href = url;
  //   link.setAttribute('download', `${audienceName.value || 'audience'}_report.pdf`);
  //   document.body.appendChild(link);
  //   link.click();
  //   link.remove();
  // } catch (error) {
  //   console.error('Error exporting report:', error);
  //   toast.error('Failed to export report');
  // }
};

// Function to fetch posts for the audience
const fetchPosts = async () => {
  if (!audienceId.value) {
    toast.error('Cannot fetch posts - no audience ID');
    return;
  }
  
  loadingPosts.value = true;
  
  try {
    const response = await api.get(`/api/audiences/${audienceId.value}/posts`);
    subredditPosts.value = response.data.data;
    
    // Initialize expanded state for each subreddit
    subredditPosts.value.forEach(sr => {
      if (!expandedSubreddits.value.hasOwnProperty(sr.subreddit_name)) {
        expandedSubreddits.value[sr.subreddit_name] = false;
      }
    });
    
    if (subredditPosts.value.length === 0) {
      toast.info('No posts found. The fetching process might still be in progress.');
    }
  } catch (error) {
    console.error('Error fetching posts:', error);
    toast.error('Failed to load posts');
  } finally {
    loadingPosts.value = false;
  }
};

// Toggle expanded state for a subreddit's posts
const toggleExpand = (subredditName) => {
  expandedSubreddits.value[subredditName] = !expandedSubreddits.value[subredditName];
};

// Format date and time
const formatDateTime = (dateString) => {
  if (!dateString) return 'Unknown';
  
  try {
    return new Date(dateString).toLocaleString();
  } catch (e) {
    return dateString;
  }
};
</script>

<style scoped>
.subreddit-icon {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style> 