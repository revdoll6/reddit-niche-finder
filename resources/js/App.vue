<template>
  <div class="flex min-h-screen bg-gray-100">
    <!-- Sidebar -->
    <Sidebar v-if="showSidebar" />

    <!-- Main Content -->
    <main :class="{ 'ml-72': showSidebar }" class="flex-1 transition-all">
      <router-view></router-view>
    </main>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from './stores/auth';
import Sidebar from './components/layout/Sidebar.vue';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const isAuthChecked = ref(false);

// Hide sidebar on login and register pages
const showSidebar = computed(() => {
  return !['login', 'register'].includes(route.name) && isAuthChecked.value;
});

// Check authentication on mount
onMounted(async () => {
  console.log('App mounted, checking authentication...');
  try {
    // Check if we have a token in localStorage
    if (authStore.token) {
      console.log('Token found in store, validating...');
      const isValid = await authStore.checkAuth();
      console.log('Token validation result:', isValid);
      
      if (!isValid && !['login', 'register'].includes(route.name)) {
        console.log('Token invalid, redirecting to login');
        router.push('/login');
      }
    } else {
      console.log('No token found in store');
      if (route.meta.requiresAuth) {
        console.log('Current route requires auth, redirecting to login');
        router.push('/login');
      }
    }
  } catch (error) {
    console.error('Authentication check failed:', error);
    if (route.meta.requiresAuth) {
      router.push('/login');
    }
  } finally {
    isAuthChecked.value = true;
  }
});
</script> 