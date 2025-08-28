<template>
  <div class="fixed bottom-0 left-0 right-0 bg-gray-800 border-t border-gray-700 py-2 px-4 z-50 shadow-lg">
    <div class="max-w-7xl mx-auto">
      <div class="flex items-center justify-between mb-2">
        <div class="flex items-center gap-3">
          <h3 class="text-base font-medium text-white">Selected Subreddits ({{ selectedSubreddits.length }})</h3>
          <div class="flex items-center gap-2">
            <button 
              @click="$emit('save')"
              class="px-3 py-1 bg-blue-600 text-white text-xs rounded-md hover:bg-blue-700 transition-colors"
            >
              Save as Audience
            </button>
          </div>
        </div>
        <button 
          @click="$emit('clear')"
          class="text-xs text-gray-400 hover:text-white transition-colors"
        >
          Clear All
        </button>
      </div>
      
      <!-- Selected subreddits list -->
      <div class="flex flex-wrap gap-1.5">
        <div
          v-for="subredditName in selectedSubreddits"
          :key="subredditName"
          class="flex items-center bg-gray-700 rounded-full px-2 py-0.5"
        >
          <span class="text-xs text-white">r/{{ subredditName }}</span>
          <button
            @click="$emit('remove', subredditName)"
            class="ml-1 text-gray-400 hover:text-white"
          >
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

defineProps({
  selectedSubreddits: {
    type: Array,
    required: true
  }
});

defineEmits(['save', 'clear', 'remove']);
</script> 