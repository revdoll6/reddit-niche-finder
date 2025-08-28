<template>
  <div class="nav-item-container">
    <div 
      class="nav-item flex items-center px-4 py-3 cursor-pointer hover:bg-gray-800 transition-colors"
      @click="toggleExpanded"
    >
      <span class="icon mr-3">{{ icon }}</span>
      <span class="label flex-grow">{{ label }}</span>
      <span 
        class="chevron transform transition-transform"
        :class="{ 'rotate-180': isExpanded }"
      >
        ▼
      </span>
    </div>

    <div 
      v-if="subItems && subItems.length"
      class="sub-items overflow-hidden transition-all"
      :class="{ 'h-0': !isExpanded }"
    >
      <router-link
        v-for="item in subItems"
        :key="item.route"
        :to="item.route"
        class="sub-item block px-12 py-2 hover:bg-gray-800 transition-colors"
        :class="{ 'bg-blue-600': isActive(item.route) }"
      >
        {{ item.label }}
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRoute } from 'vue-router';

const props = defineProps({
  icon: {
    type: String,
    required: true
  },
  label: {
    type: String,
    required: true
  },
  subItems: {
    type: Array,
    default: () => []
  }
});

const route = useRoute();
const isExpanded = ref(false);

const toggleExpanded = () => {
  isExpanded.value = !isExpanded.value;
};

const isActive = (path) => {
  return route.path === path;
};
</script>

<style scoped>
.sub-items {
  background-color: rgba(0, 0, 0, 0.2);
}

.chevron {
  font-size: 0.75rem;
}

.sub-items:not(.h-0) {
  padding: 0.5rem 0;
}
</style> 