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
            <h1 class="text-2xl font-bold text-white">r/{{ subreddit.name }}</h1>
          </div>
          <div class="flex items-center space-x-4">
            <button
              @click="addToAudience"
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
            >
              Add to Audience
            </button>
            <a
              :href="`https://reddit.com/r/${subreddit.name}`"
              target="_blank"
              class="px-4 py-2 bg-gray-700 text-white rounded-md hover:bg-gray-600 transition-colors"
            >
              Visit Subreddit
            </a>
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

    <!-- Tab Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Overview Tab -->
      <div v-if="currentTab === 'overview'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-gray-800 rounded-lg p-6">
          <h3 class="text-lg font-medium text-white mb-4">Key Metrics</h3>
          <div class="space-y-4">
            <div class="flex justify-between">
              <span class="text-gray-400">Subscribers</span>
              <span class="text-white font-medium">{{ formatNumber(subreddit.subscribers) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-400">Daily Active Users</span>
              <span class="text-white font-medium">{{ formatNumber(subreddit.active_users) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-400">Posts per Day</span>
              <span class="text-white font-medium">{{ subreddit.posts_per_day }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-400">Engagement Rate</span>
              <span class="text-white font-medium">{{ (subreddit.engagement_rate * 100).toFixed(1) }}%</span>
            </div>
          </div>
        </div>

        <div class="bg-gray-800 rounded-lg p-6">
          <h3 class="text-lg font-medium text-white mb-4">Community Health</h3>
          <div class="space-y-4">
            <div class="flex justify-between items-center">
              <span class="text-gray-400">Growth Trend</span>
              <div class="flex items-center">
                <span class="text-green-500 mr-2">↑</span>
                <span class="text-white font-medium">{{ subreddit.growth_rate }}%</span>
              </div>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-gray-400">Moderation Level</span>
              <span class="text-white font-medium capitalize">{{ subreddit.moderation_level }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-gray-400">Content Quality</span>
              <div class="flex items-center">
                <span v-for="i in 5" :key="i" class="text-yellow-500">★</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Content Analysis Tab -->
      <div v-if="currentTab === 'content'" class="space-y-8">
        <div class="bg-gray-800 rounded-lg p-6">
          <h3 class="text-lg font-medium text-white mb-6">Content Distribution</h3>
          <div class="h-64">
            <!-- Add content distribution chart here -->
          </div>
        </div>

        <div class="bg-gray-800 rounded-lg p-6">
          <h3 class="text-lg font-medium text-white mb-6">Popular Topics</h3>
          <div class="flex flex-wrap gap-2">
            <span
              v-for="topic in popularTopics"
              :key="topic.name"
              class="px-3 py-1 bg-gray-700 rounded-full text-sm text-white"
            >
              {{ topic.name }}
            </span>
          </div>
        </div>
      </div>

      <!-- User Activity Tab -->
      <div v-if="currentTab === 'activity'" class="space-y-8">
        <div class="bg-gray-800 rounded-lg p-6">
          <h3 class="text-lg font-medium text-white mb-6">Activity Heatmap</h3>
          <div class="h-64">
            <!-- Add activity heatmap here -->
          </div>
        </div>

        <div class="bg-gray-800 rounded-lg p-6">
          <h3 class="text-lg font-medium text-white mb-6">User Demographics</h3>
          <div class="h-64">
            <!-- Add demographics chart here -->
          </div>
        </div>
      </div>

      <!-- Growth Trends Tab -->
      <div v-if="currentTab === 'growth'" class="space-y-8">
        <div class="bg-gray-800 rounded-lg p-6">
          <h3 class="text-lg font-medium text-white mb-6">Subscriber Growth</h3>
          <div class="h-64">
            <!-- Add subscriber growth chart here -->
          </div>
        </div>

        <div class="bg-gray-800 rounded-lg p-6">
          <h3 class="text-lg font-medium text-white mb-6">Engagement Metrics</h3>
          <div class="h-64">
            <!-- Add engagement metrics chart here -->
          </div>
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
const subreddit = ref({});
const currentTab = ref('overview');
const loading = ref(true);

const tabs = [
  { id: 'overview', name: 'Overview' },
  { id: 'content', name: 'Content Analysis' },
  { id: 'activity', name: 'User Activity' },
  { id: 'growth', name: 'Growth Trends' }
];

const popularTopics = ref([
  { name: 'Discussion', count: 156 },
  { name: 'Question', count: 89 },
  { name: 'Guide', count: 67 },
  { name: 'News', count: 45 },
  { name: 'Meme', count: 34 }
]);

onMounted(async () => {
  try {
    const response = await api.get(`/api/subreddits/${route.params.name}`);
    subreddit.value = response.data;
  } catch (error) {
    console.error('Error fetching subreddit details:', error);
  } finally {
    loading.value = false;
  }
});

const formatNumber = (num) => {
  return new Intl.NumberFormat().format(num);
};

const addToAudience = () => {
  // Implement add to audience functionality
};
</script> 