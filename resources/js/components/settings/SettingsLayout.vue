<template>
  <div class="min-h-screen bg-gray-900">
    <div class="p-6">
      <div class="bg-gray-800 rounded-lg shadow-lg border border-gray-700">
        <!-- Settings Navigation -->
        <div class="border-b border-gray-700">
          <nav class="flex -mb-px">
            <router-link
              v-for="tab in tabs"
              :key="tab.route"
              :to="tab.route"
              class="px-6 py-4 text-sm font-medium border-b-2 whitespace-nowrap"
              :class="[
                isActive(tab.route)
                  ? 'border-blue-500 text-blue-500'
                  : 'border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-600'
              ]"
            >
              {{ tab.name }}
            </router-link>
          </nav>
        </div>

        <!-- Content Area -->
        <router-view></router-view>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRoute } from 'vue-router';

const route = useRoute();

const tabs = [
  { name: 'Profile', route: '/settings/profile' },
  { name: 'Preferences', route: '/settings/preferences' },
  { name: 'API Configuration', route: '/settings/api' }
];

const isActive = (path) => {
  return route.path === path;
};
</script> 