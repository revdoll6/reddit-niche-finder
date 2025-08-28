<template>
  <nav class="sidebar bg-gray-900 text-white w-72 min-h-screen fixed left-0 top-0 overflow-y-auto">
    <div class="sidebar-header p-4 border-b border-gray-800">
      <h1 class="text-xl font-bold">Reddit Niche Finder</h1>
    </div>
    
    <div class="sidebar-content overflow-y-auto h-[calc(100vh-4rem)]">
      <!-- Main Navigation -->
      <div class="p-4 space-y-2">
        <router-link
          to="/dashboard/saved-audiences"
          class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors"
          :class="[$route.path.startsWith('/dashboard') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800']"
        >
          <div class="flex items-center">
            <span class="mr-2">📊</span>
            <span>Dashboard</span>
          </div>
        </router-link>

        <router-link
          to="/discovery/explorer"
          class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors"
          :class="[$route.path.startsWith('/discovery') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800']"
        >
          <div class="flex items-center">
            <span class="mr-2">🎯</span>
            <span>Subreddit Discovery</span>
          </div>
        </router-link>

        <router-link
          to="/resources/practices"
          class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors"
          :class="[$route.path.startsWith('/resources') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800']"
        >
          <div class="flex items-center">
            <span class="mr-2">📚</span>
            <span>Resources</span>
          </div>
        </router-link>

        <router-link
          to="/settings/profile"
          class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors"
          :class="[$route.path.startsWith('/settings') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800']"
        >
          <div class="flex items-center">
            <span class="mr-2">⚙️</span>
            <span>Settings</span>
          </div>
        </router-link>

        <!-- Logout Button -->
        <div class="border-t border-gray-800 mt-4 pt-4">
          <button
            @click="handleLogout"
            class="flex items-center w-full px-3 py-2 text-sm rounded-lg text-gray-300 hover:bg-red-600 hover:text-white transition-colors"
          >
            <div class="flex items-center">
              <span class="mr-2">🚪</span>
              <span>Logout</span>
            </div>
          </button>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const handleLogout = async () => {
  try {
    console.log('Logout button clicked');
    await authStore.logout();
    console.log('Logout completed, redirecting to login');
    router.push('/login');
  } catch (error) {
    console.error('Logout error:', error);
    // Even if there's an error, clear auth and redirect to login
    authStore.clearAuth();
    router.push('/login');
  }
};
</script>

<style scoped>
.sidebar {
  z-index: 50;
}

.sidebar-content {
  scrollbar-width: thin;
  scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
}

.sidebar-content::-webkit-scrollbar {
  width: 6px;
}

.sidebar-content::-webkit-scrollbar-track {
  background: transparent;
}

.sidebar-content::-webkit-scrollbar-thumb {
  background-color: rgba(156, 163, 175, 0.5);
  border-radius: 3px;
}
</style> 