<template>
  <div class="min-h-screen bg-gray-900">
    <!-- Content Area -->
    <div class="p-6">
      <div class="bg-gray-800 rounded-lg shadow-lg border border-gray-700">
        <!-- Main Content -->
        <router-view v-slot="{ Component }">
          <transition name="fade" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRoute } from 'vue-router';
const route = useRoute();
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style> 