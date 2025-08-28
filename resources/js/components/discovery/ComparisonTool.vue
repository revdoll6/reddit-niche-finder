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
            <h1 class="text-2xl font-bold text-white">Subreddit Comparison</h1>
          </div>
          <div class="flex items-center space-x-4">
            <button
              @click="saveComparison"
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
              :disabled="subreddits.length < 2"
            >
              Save Comparison
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Subreddit Selection -->
      <div class="bg-gray-800 rounded-lg p-6 mb-8">
        <h2 class="text-lg font-medium text-white mb-4">Select Subreddits to Compare (2-5)</h2>
        <div class="flex flex-wrap gap-4">
          <div
            v-for="subreddit in subreddits"
            :key="subreddit.name"
            class="flex items-center bg-gray-700 rounded-lg px-4 py-2"
          >
            <span class="text-white font-medium">r/{{ subreddit.name }}</span>
            <button
              @click="removeSubreddit(subreddit)"
              class="ml-3 text-gray-400 hover:text-white"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <button
            v-if="subreddits.length < 5"
            @click="showSubredditSearch = true"
            class="flex items-center bg-gray-700 rounded-lg px-4 py-2 text-gray-400 hover:text-white hover:bg-gray-600 transition-colors"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Subreddit
          </button>
        </div>
      </div>

      <!-- Comparison Metrics -->
      <div v-if="subreddits.length >= 2" class="space-y-8">
        <!-- Side-by-Side Metrics -->
        <div class="bg-gray-800 rounded-lg p-6">
          <h2 class="text-lg font-medium text-white mb-6">Key Metrics Comparison</h2>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="text-left">
                  <th class="pb-4 text-gray-400 font-medium">Metric</th>
                  <th
                    v-for="subreddit in subreddits"
                    :key="subreddit.name"
                    class="pb-4 text-gray-400 font-medium px-6"
                  >
                    r/{{ subreddit.name }}
                  </th>
                </tr>
              </thead>
              <tbody class="text-white">
                <tr>
                  <td class="py-3 text-gray-400">Subscribers</td>
                  <td
                    v-for="subreddit in subreddits"
                    :key="subreddit.name"
                    class="py-3 px-6 font-medium"
                  >
                    {{ formatNumber(subreddit.subscribers) }}
                  </td>
                </tr>
                <tr>
                  <td class="py-3 text-gray-400">Daily Active Users</td>
                  <td
                    v-for="subreddit in subreddits"
                    :key="subreddit.name"
                    class="py-3 px-6 font-medium"
                  >
                    {{ formatNumber(subreddit.active_users) }}
                  </td>
                </tr>
                <tr>
                  <td class="py-3 text-gray-400">Posts per Day</td>
                  <td
                    v-for="subreddit in subreddits"
                    :key="subreddit.name"
                    class="py-3 px-6 font-medium"
                  >
                    {{ subreddit.posts_per_day }}
                  </td>
                </tr>
                <tr>
                  <td class="py-3 text-gray-400">Engagement Rate</td>
                  <td
                    v-for="subreddit in subreddits"
                    :key="subreddit.name"
                    class="py-3 px-6 font-medium"
                  >
                    {{ (subreddit.engagement_rate * 100).toFixed(1) }}%
                  </td>
                </tr>
                <tr>
                  <td class="py-3 text-gray-400">Growth Rate</td>
                  <td
                    v-for="subreddit in subreddits"
                    :key="subreddit.name"
                    class="py-3 px-6 font-medium"
                  >
                    {{ subreddit.growth_rate }}%
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Audience Overlap -->
        <div class="bg-gray-800 rounded-lg p-6">
          <h2 class="text-lg font-medium text-white mb-6">Audience Overlap</h2>
          <div class="h-64">
            <!-- Add audience overlap visualization here -->
          </div>
        </div>

        <!-- Strengths & Weaknesses -->
        <div class="bg-gray-800 rounded-lg p-6">
          <h2 class="text-lg font-medium text-white mb-6">Strengths & Weaknesses Analysis</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div
              v-for="subreddit in subreddits"
              :key="subreddit.name"
              class="space-y-4"
            >
              <h3 class="text-white font-medium">r/{{ subreddit.name }}</h3>
              <div class="space-y-2">
                <div class="flex items-start">
                  <span class="text-green-500 mr-2">✓</span>
                  <span class="text-gray-300">High engagement rate</span>
                </div>
                <div class="flex items-start">
                  <span class="text-green-500 mr-2">✓</span>
                  <span class="text-gray-300">Active moderation</span>
                </div>
                <div class="flex items-start">
                  <span class="text-red-500 mr-2">×</span>
                  <span class="text-gray-300">Lower growth rate</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div
        v-else
        class="bg-gray-800 rounded-lg p-8 text-center"
      >
        <svg class="w-16 h-16 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
        <h3 class="text-lg font-medium text-white mb-2">Select Subreddits to Compare</h3>
        <p class="text-gray-400">Add at least 2 subreddits to see the comparison analysis</p>
      </div>
    </div>

    <!-- Subreddit Search Dialog -->
    <div v-if="showSubredditSearch" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium text-white">Add Subreddit</h3>
          <button
            @click="showSubredditSearch = false"
            class="text-gray-400 hover:text-white"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div class="relative">
          <input
            v-model="searchQuery"
            type="text"
            class="w-full px-4 py-2 bg-gray-700 border-0 rounded-md text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500"
            placeholder="Search subreddits..."
            @input="handleSearch"
          />
        </div>
        <div class="mt-4 space-y-2 max-h-60 overflow-y-auto">
          <button
            v-for="result in searchResults"
            :key="result.name"
            @click="addSubreddit(result)"
            class="w-full text-left px-4 py-2 rounded-md hover:bg-gray-700 transition-colors"
          >
            <div class="text-white font-medium">r/{{ result.name }}</div>
            <div class="text-sm text-gray-400">{{ formatNumber(result.subscribers) }} members</div>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../services/api';

const route = useRoute();
const router = useRouter();
const subreddits = ref([]);
const showSubredditSearch = ref(false);
const searchQuery = ref('');
const searchResults = ref([]);
const loading = ref(false);

onMounted(async () => {
  if (route.query.ids) {
    try {
      const ids = route.query.ids.split(',');
      const response = await api.get('/api/subreddits/batch', { params: { ids } });
      subreddits.value = response.data;
    } catch (error) {
      console.error('Error fetching subreddits:', error);
    }
  }
});

const handleSearch = async () => {
  if (!searchQuery.value) {
    searchResults.value = [];
    return;
  }

  try {
    loading.value = true;
    const response = await api.get('/api/subreddits/search', {
      params: { q: searchQuery.value }
    });
    searchResults.value = response.data.filter(
      result => !subreddits.value.some(sr => sr.name === result.name)
    );
  } catch (error) {
    console.error('Error searching subreddits:', error);
  } finally {
    loading.value = false;
  }
};

const addSubreddit = (subreddit) => {
  if (subreddits.value.length < 5) {
    subreddits.value.push(subreddit);
    showSubredditSearch.value = false;
    searchQuery.value = '';
    searchResults.value = [];
  }
};

const removeSubreddit = (subreddit) => {
  subreddits.value = subreddits.value.filter(sr => sr.name !== subreddit.name);
};

const saveComparison = async () => {
  try {
    await api.post('/api/comparisons', {
      subreddits: subreddits.value.map(sr => sr.id)
    });
    // Show success message or redirect
  } catch (error) {
    console.error('Error saving comparison:', error);
  }
};

const formatNumber = (num) => {
  return new Intl.NumberFormat().format(num);
};
</script> 