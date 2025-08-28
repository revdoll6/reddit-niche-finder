<template>
  <div class="audience-status-modal">
    <div class="modal-content bg-gray-800 text-white rounded-lg shadow-xl p-6 max-w-lg w-full">
      <h2 class="text-xl font-semibold mb-4">Audience Created Successfully</h2>
      
      <!-- Processing Screen (shown initially) -->
      <div v-if="!hasStatus" class="text-center">
        <div class="mb-4">
          <svg class="animate-spin h-10 w-10 mx-auto mb-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <p class="font-medium">Your audience has been saved!</p>
          <p class="text-sm text-gray-400 mt-2">
            We're now fetching posts for each subreddit in the background.
            You can continue using the app - this may take some time.
          </p>
        </div>
        
        <div class="mt-6">
          <button 
            @click="checkStatus" 
            class="mr-3 px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-600 transition"
          >
            Check Status
          </button>
          <button 
            @click="$emit('close')" 
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition"
          >
            Continue
          </button>
        </div>
      </div>
      
      <!-- Status Screen (shown after fetching status) -->
      <div v-else class="max-h-96 overflow-y-auto">
        <h3 class="text-lg font-medium mb-3">Post Fetching Status</h3>
        
        <div v-if="Object.keys(fetchStatus).length === 0" class="mb-4 text-center text-gray-400">
          No status information available yet. The jobs may still be initializing.
        </div>
        
        <div v-else>
          <div v-for="(status, subredditName) in fetchStatus" :key="subredditName" class="mb-2 p-2 border border-gray-700 rounded">
            <div class="flex items-center justify-between">
              <span class="font-medium">r/{{ subredditName }}</span>
              <span 
                :class="{
                  'bg-yellow-900/50 text-yellow-500': status.status === 'pending',
                  'bg-blue-900/50 text-blue-500': status.status === 'in_progress',
                  'bg-green-900/50 text-green-500': status.status === 'completed',
                  'bg-red-900/50 text-red-500': status.status === 'failed'
                }"
                class="px-2 py-0.5 text-xs rounded-full"
              >
                {{ formatStatus(status.status) }}
              </span>
            </div>
            <div v-if="status.status === 'completed'" class="text-xs text-gray-400 mt-1">
              Fetched at: {{ formatDate(status.fetched_at) }}
            </div>
          </div>
          
          <div class="mt-4 text-xs text-gray-400">
            <p>Note: Post fetching continues in the background and may take several minutes to complete.</p>
            <p>You can check the status again later from the dashboard.</p>
          </div>
        </div>
        
        <div class="mt-6 flex justify-between">
          <button 
            @click="checkStatus" 
            class="px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-600 transition"
          >
            Refresh Status
          </button>
          <button 
            @click="$emit('close')" 
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, onBeforeUnmount } from 'vue';
import api from '../../services/api';

const props = defineProps({
  audienceId: {
    type: [Number, String],
    required: true
  }
});

const emit = defineEmits(['close']);

const fetchStatus = ref({});
const hasStatus = ref(false);
const statusCheckInterval = ref(null);
const isComponentMounted = ref(true);

const checkStatus = async () => {
  // Don't make API calls if component is unmounting
  if (!isComponentMounted.value) return;
  
  try {
    const response = await api.get(`/api/audiences/${props.audienceId}/fetch-status`);
    
    // Guard against updates after unmount
    if (!isComponentMounted.value) return;
    
    fetchStatus.value = response.data.data;
    hasStatus.value = true;
    
    // Check if all statuses are completed or failed
    const allCompleted = Object.values(fetchStatus.value).every(
      status => status.status === 'completed' || status.status === 'failed'
    );
    
    // If all completed, stop the automatic refresh
    if (allCompleted && statusCheckInterval.value) {
      clearInterval(statusCheckInterval.value);
      statusCheckInterval.value = null;
    }
  } catch (error) {
    console.error('Error checking fetch status:', error);
    
    // Add basic error handling to prevent UI disruption
    if (error.response && error.response.status === 404) {
      console.log('Status endpoint not found or audience deleted');
      if (statusCheckInterval.value) {
        clearInterval(statusCheckInterval.value);
        statusCheckInterval.value = null;
      }
    }
  }
};

const formatStatus = (status) => {
  return status.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase());
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  
  try {
    return new Date(dateString).toLocaleString();
  } catch (e) {
    return dateString;
  }
};

// Function to clear all intervals
const clearAllIntervals = () => {
  if (statusCheckInterval.value) {
    clearInterval(statusCheckInterval.value);
    statusCheckInterval.value = null;
  }
};

onMounted(() => {
  isComponentMounted.value = true;
  
  // Initial status check after 2 seconds
  setTimeout(() => {
    if (isComponentMounted.value) {
      checkStatus();
      
      // Set up automatic refresh every 10 seconds
      statusCheckInterval.value = setInterval(() => {
        if (isComponentMounted.value) {
          checkStatus();
        } else {
          clearAllIntervals();
        }
      }, 10000);
    }
  }, 2000);
});

// Use both lifecycle hooks for better cleanup
onBeforeUnmount(() => {
  isComponentMounted.value = false;
  clearAllIntervals();
});

onUnmounted(() => {
  isComponentMounted.value = false;
  clearAllIntervals();
});
</script>

<style scoped>
.audience-status-modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: rgba(0, 0, 0, 0.7);
  z-index: 50;
}
</style> 