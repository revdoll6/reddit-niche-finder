<template>
  <div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-semibold text-white">Saved Audiences</h1>
      <router-link
        to="/discovery/explorer"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        Create New Audience
      </router-link>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-900/50 text-red-100 p-4 rounded-lg">
      {{ error }}
    </div>

    <!-- Empty State -->
    <div v-else-if="!audiences.length" class="bg-gray-800 rounded-xl border border-gray-700 p-12 text-center">
      <div class="flex flex-col items-center justify-center">
        <svg class="w-16 h-16 text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        <h2 class="text-xl font-medium text-white mb-2">No audiences yet</h2>
        <p class="text-gray-400 mb-6">Create your first audience by searching for subreddits and selecting the ones that match your target demographic.</p>
        <router-link
          to="/discovery/explorer"
          class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          Find Subreddits
        </router-link>
      </div>
    </div>

    <!-- Audiences Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="audience in audiences"
        :key="audience.id"
        class="bg-gray-800 rounded-xl border border-gray-700 hover:border-blue-500 transition-all duration-200 overflow-hidden"
      >
        <!-- Audience Header -->
        <div class="p-4 border-b border-gray-700">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-white">{{ audience.name }}</h3>
            <div class="flex items-center space-x-2">
              <button
                @click="deleteAudience(audience.id)"
                class="p-1 text-gray-400 hover:text-red-500 transition-colors"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </div>
          </div>
          <p v-if="audience.description" class="text-gray-400 text-sm mt-1">{{ audience.description }}</p>
        </div>

        <!-- Audience Preview -->
        <div class="p-4">
          <!-- Subreddit Icons -->
          <div class="flex flex-wrap gap-2 mb-4">
            <div
              v-for="subreddit in audience.subreddits"
              :key="subreddit.name"
              class="relative group"
            >
              <img
                v-if="subreddit.icon_img"
                :src="subreddit.icon_img"
                :alt="'r/' + subreddit.name"
                class="w-10 h-10 rounded-full bg-gray-700"
                @error="$event.target.src = 'https://www.redditstatic.com/desktop2x/img/favicon/apple-icon-57x57.png'"
              />
              <div v-else class="w-10 h-10 rounded-full bg-gray-700 flex items-center justify-center text-white font-bold text-sm">
                r/
              </div>
              <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                <div class="bg-black bg-opacity-75 text-white text-xs px-2 py-1 rounded">
                  r/{{ subreddit.name }}
                </div>
              </div>
            </div>
          </div>

          <!-- Audience Stats -->
          <div class="grid grid-cols-2 gap-4">
            <div class="bg-gray-700 rounded-lg p-3">
              <div class="text-sm text-gray-400 mb-1">Total Reach</div>
              <div class="text-lg font-bold text-white">
                {{ formatNumber(getTotalReach(audience)) }}
              </div>
            </div>
            <div class="bg-gray-700 rounded-lg p-3">
              <div class="text-sm text-gray-400 mb-1">Subreddits</div>
              <div class="text-lg font-bold text-white">
                {{ audience.subreddits.length }}
              </div>
            </div>
          </div>

          <!-- View Details Button -->
          <button
            @click="viewAudienceDetails(audience.id)"
            class="w-full mt-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition-colors text-sm font-medium"
          >
            View Details
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../services/api';

const router = useRouter();
const audiences = ref([]);
const loading = ref(true);
const error = ref(null);

onMounted(async () => {
  await fetchAudiences();
});

const fetchAudiences = async () => {
  try {
    const response = await api.get('/api/audiences');
    
    // Log the raw API response
    console.log('DEBUG - Raw audiences API response:', response.data);
    
    // Check for metrics data in the first audience (if available)
    if (response.data.data.length > 0) {
      const firstAudience = response.data.data[0];
      const firstSubreddit = firstAudience.subreddits[0];
      
      console.log('DEBUG - First audience in response:', {
        id: firstAudience.id,
        name: firstAudience.name,
        subreddits_count: firstAudience.subreddits.length
      });
      
      if (firstSubreddit) {
        console.log('DEBUG - First subreddit metrics in SavedAudiences:', {
          name: firstSubreddit.name,
          posts_per_day: firstSubreddit.posts_per_day,
          opportunity_score: firstSubreddit.opportunity_score,
          active_post_engagement: firstSubreddit.active_post_engagement,
          keyword_engagement: firstSubreddit.keyword_engagement
        });
      }
    }
    
    audiences.value = response.data.data;
  } catch (err) {
    error.value = 'Failed to load audiences';
    console.error('Error fetching audiences:', err);
  } finally {
    loading.value = false;
  }
};

const deleteAudience = async (audienceId) => {
  if (!confirm('Are you sure you want to delete this audience?')) return;

  try {
    await api.delete(`/api/audiences/${audienceId}`);
    audiences.value = audiences.value.filter(a => a.id !== audienceId);
  } catch (err) {
    console.error('Error deleting audience:', err);
    // Show error toast
  }
};

const viewAudienceDetails = (audienceId) => {
  router.push(`/discovery/audience/${audienceId}`);
};

const getTotalReach = (audience) => {
  return audience.subreddits.reduce((total, subreddit) => total + (subreddit.subscribers || 0), 0);
};

const formatNumber = (num) => {
  if (num >= 1000000) {
    return (num / 1000000).toFixed(1) + 'M';
  }
  if (num >= 1000) {
    return (num / 1000).toFixed(1) + 'K';
  }
  return num.toString();
};
</script> 