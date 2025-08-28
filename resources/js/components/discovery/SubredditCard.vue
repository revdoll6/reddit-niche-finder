<template>
  <div class="subreddit-card bg-gray-800 border border-gray-700 rounded-lg shadow-md hover:border-blue-500 transition-all duration-200">
    <div v-if="!subreddit" class="p-3 flex items-center justify-center h-full">
      <p class="text-gray-400 text-sm">Error loading subreddit data</p>
    </div>
    <div class="p-3" v-else>
      <!-- Header -->
      <div class="flex items-start justify-between mb-3">
        <div class="flex items-center gap-2">
          <div class="subreddit-icon">
            <img v-if="subreddit.icon_img" :src="subreddit.icon_img" 
              class="w-10 h-10 rounded-full bg-gray-700"
              alt="Subreddit icon"
              @error="$event.target.src = 'https://www.redditstatic.com/desktop2x/img/favicon/apple-icon-57x57.png'"
            />
            <div v-else class="w-10 h-10 rounded-full bg-gray-700 flex items-center justify-center text-white font-bold text-sm">
              r/
            </div>
          </div>
          <div>
            <h3 class="text-base font-semibold text-white">r/{{ subreddit.display_name || 'unknown' }}</h3>
            <p class="text-xs text-gray-400">Created {{ formatDate(subreddit.created_utc) }}</p>
          </div>
        </div>
        <div class="flex items-center">
          <button 
            @click="$emit('toggle-select', subreddit)"
            class="p-1.5 rounded-lg transition-colors duration-200"
            :class="isSelected ? 'bg-blue-500 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600'"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path v-if="isSelected" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Description -->
      <p class="text-xs text-gray-300 mb-3 line-clamp-2">{{ subreddit.public_description || 'No description available' }}</p>

      <!-- Topics -->
      <div class="flex flex-wrap gap-1.5 mb-3">
        <span v-for="topic in (subreddit.topics || []).slice(0, 3)" :key="topic" 
          class="text-xs px-1.5 py-0.5 rounded-full bg-gray-700 text-gray-300">
          {{ topic }}
        </span>
        <span v-if="!(subreddit.topics || []).length" class="text-xs px-1.5 py-0.5 rounded-full bg-gray-700 text-gray-300">
          No topics
        </span>
      </div>
      
      <!-- Metrics Grid -->
      <div class="grid grid-cols-2 gap-3 mb-3">
        <!-- Members -->
        <div class="bg-gray-700 rounded-lg p-2">
          <div class="flex items-center justify-between mb-0.5">
            <span class="text-xs text-gray-400">Members</span>
            <svg class="w-3 h-3 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
              <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
            </svg>
          </div>
          <div class="text-sm font-semibold text-white">
            {{ formatNumber(subreddit.subscribers) }}
          </div>
        </div>
        
        <!-- Engagement Per Post -->
        <div class="bg-gray-700 rounded-lg p-2">
          <div class="flex items-center justify-between mb-0.5">
            <span class="text-xs text-gray-400">Engagement</span>
            <svg class="w-3 h-3 text-red-400" fill="currentColor" viewBox="0 0 20 20">
              <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z" />
            </svg>
          </div>
          <div class="text-sm font-semibold text-white">{{ formatEngagement(subreddit.engagement_rate) }}</div>
          <div class="text-xs text-gray-400 mt-0.5">upvotes + comments</div>
        </div>

        <!-- Active Post Engagement -->
        <div class="bg-gray-700 rounded-lg p-2">
          <div class="flex items-center justify-between mb-0.5">
            <span class="text-xs text-gray-400">Active Eng</span>
            <svg class="w-3 h-3 text-red-400" fill="currentColor" viewBox="0 0 20 20">
              <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z" />
              <path d="M15 7v2a4 4 0 01-4 4H9.828l-1.766 1.767c.28.149.599.233.938.233h2l3 3v-3h2a2 2 0 002-2V9a2 2 0 00-2-2h-1z" />
            </svg>
          </div>
          <div class="text-sm font-semibold text-white">
            <span v-if="subreddit.active_engagement === null || subreddit.active_engagement === undefined">{{ subreddit.engagement_rate ? formatEngagement(subreddit.engagement_rate * 1.2) : 'N/A' }}</span>
            <span v-else>{{ formatEngagement(subreddit.active_engagement) }}</span>
          </div>
          <div class="text-xs text-gray-400 mt-0.5">per active post</div>
        </div>

        <!-- Keyword Engagement -->
        <div class="bg-gray-700 rounded-lg p-2">
          <div class="flex items-center justify-between mb-0.5">
            <span class="text-xs text-gray-400">Keyword Eng</span>
            <svg class="w-3 h-3 text-purple-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="text-sm font-semibold text-white">
            <span v-if="subreddit.keyword_engagement === null || subreddit.keyword_engagement === undefined">N/A</span>
            <span v-else>{{ formatEngagement(subreddit.keyword_engagement) }}</span>
          </div>
          <div class="text-xs text-gray-400 mt-0.5">for relevant posts</div>
        </div>
        
        <!-- Posts per Day -->
        <div class="bg-gray-700 rounded-lg p-2">
          <div class="flex items-center justify-between mb-0.5">
            <span class="text-xs text-gray-400">Posts/Day</span>
            <svg class="w-3 h-3 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
              <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
            </svg>
          </div>
          <div class="text-sm font-semibold text-white">{{ subreddit.posts_per_day }}</div>
        </div>

        <!-- Opportunity Score -->
        <div :class="getOpportunityScoreClass(subreddit.opportunity_score)" class="rounded-lg p-2">
          <div class="flex items-center justify-between mb-0.5">
            <span class="text-xs text-gray-400">Opportunity</span>
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
              <path d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" />
            </svg>
          </div>
          <div class="text-sm font-semibold">{{ subreddit.opportunity_score }}</div>
        </div>
      </div>

      <!-- Relevancy Scores -->
      <div class="bg-gray-700 rounded-lg p-3 mb-3">
        <h4 class="text-xs font-medium text-gray-400 mb-2">Relevancy Scores</h4>
        
        <!-- Base and Content Relevancy -->
        <div class="grid grid-cols-2 gap-3 mb-2">
          <div>
            <div class="text-xs text-gray-400 mb-0.5">Base Relevancy</div>
            <div class="text-sm font-semibold" :class="getScoreClass(subreddit.base_relevancy_score)">
              {{ formatPercentage(subreddit.base_relevancy_score) }}
            </div>
          </div>
          <div>
            <div class="text-xs text-gray-400 mb-0.5">Content Relevancy</div>
            <div class="text-sm font-semibold" :class="getScoreClass(subreddit.content_relevancy_score)">
              {{ formatPercentage(subreddit.content_relevancy_score) }}
            </div>
          </div>
        </div>

        <!-- Detailed Relevancy Scores -->
        <div class="text-xs text-gray-400 mt-1">Detailed Relevancy Scores:</div>
        <div class="grid grid-cols-2 gap-1 mt-0.5">
          <div class="flex justify-between">
            <span class="text-xs text-gray-400">Exact Match:</span>
            <span class="text-xs text-white">{{ formatPercentage(subreddit.metadata_exact_match_score) }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-xs text-gray-400">N-Gram Match:</span>
            <span class="text-xs text-white">{{ formatPercentage(subreddit.metadata_ngram_match_score) }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-xs text-gray-400">Jaccard Score:</span>
            <span class="text-xs text-white">{{ formatPercentage(subreddit.metadata_jaccard_score) }}</span>
          </div>
        </div>
      </div>
      
      <!-- Actions -->
      <div class="mt-3 flex gap-2">
        <a 
          :href="'https://reddit.com/r/' + subreddit.display_name" 
          target="_blank"
          class="flex-1 py-1.5 px-3 bg-gray-700 text-gray-300 hover:bg-gray-600 rounded-lg text-xs font-medium text-center transition-colors"
        >
          Visit Subreddit
        </a>
        <button
          @click="$emit('view-details', subreddit)"
          class="flex-1 py-1.5 px-3 bg-blue-600 text-white hover:bg-blue-700 rounded-lg text-xs font-medium text-center transition-colors"
        >
          View Details
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    subreddit: {
      type: Object,
      required: true
    },
    isSelected: {
      type: Boolean,
      default: false
    }
  },
  methods: {
    formatNumber(num) {
      if (num === null || num === undefined || isNaN(num)) return 'N/A';
      if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M';
      } else if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K';
      }
      return Math.round(num).toString();
    },
    formatDate(timestamp) {
      if (!timestamp) return 'Unknown';
      try {
        return new Date(timestamp * 1000).toLocaleDateString();
      } catch (e) {
        return 'Invalid date';
      }
    },
    formatPercentage(value) {
      if (value === null || value === undefined || value === '') return 'N/A';
      
      // Ensure we have a valid number
      const numValue = parseFloat(value);
      if (isNaN(numValue)) return 'N/A';
      
      // For actual zero values, show 0.0%
      if (numValue === 0) return '0.0%';
      
      return numValue.toFixed(1) + '%';
    },
    formatEngagement(value) {
      if (value === null || value === undefined || isNaN(value)) return 'N/A';
      
      // Ensure we have a valid number
      const numValue = parseFloat(value);
      if (isNaN(numValue)) return 'N/A';
      
      return numValue.toFixed(1);
    },
    getScoreClass(score) {
      if (score === null || score === undefined || score === '') return 'text-gray-400';
      
      // Ensure we have a valid number
      const numScore = parseFloat(score);
      if (isNaN(numScore)) return 'text-gray-400';
      
      // For zero scores, show in gray
      if (numScore === 0) return 'text-gray-400';
      
      if (numScore >= 70) return 'text-green-400';
      if (numScore >= 40) return 'text-yellow-400';
      return 'text-red-400';
    },
    getOpportunityScoreClass(score) {
      if (score === null || score === undefined || isNaN(score)) return 'bg-gray-700 text-gray-400';
      
      // Ensure we have a valid number
      const numScore = parseFloat(score);
      if (isNaN(numScore)) return 'bg-gray-700 text-gray-400';
      
      if (numScore >= 80) return 'bg-green-900/50 text-green-400';
      if (numScore >= 60) return 'bg-blue-900/50 text-blue-400';
      if (numScore >= 40) return 'bg-yellow-900/50 text-yellow-400';
      return 'bg-red-900/50 text-red-400';
    }
  }
}
</script>

<style scoped>
.subreddit-card {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.subreddit-card:hover {
  transform: translateY(-2px);
}
</style> 