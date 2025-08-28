<template>
  <div class="p-6">
    <div class="bg-white rounded-lg shadow-md p-6">
      <h1 class="text-2xl font-bold mb-6">Recommended Communities</h1>
      
      <!-- Interests Section -->
      <div class="mb-8">
        <h2 class="text-lg font-semibold mb-4">Your Interests</h2>
        <div class="flex flex-wrap gap-2 mb-4">
          <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
            Marketing
          </span>
          <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
            Startups
          </span>
          <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
            Technology
          </span>
          <button class="px-3 py-1 border border-gray-300 rounded-full text-sm hover:bg-gray-100">
            + Add Interest
          </button>
        </div>
      </div>
      
      <!-- Recommendations Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Placeholder Cards -->
        <div class="border border-gray-200 rounded-lg p-4">
          <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold">r/placeholder</h3>
            <span class="text-sm text-gray-500">50k members</span>
          </div>
          <p class="text-sm text-gray-600 mb-4">
            This is a placeholder description for a recommended subreddit.
          </p>
          <div class="flex justify-between items-center">
            <span class="text-sm text-gray-500">95% match</span>
            <button class="text-blue-600 hover:text-blue-800">
              Follow
            </button>
          </div>
        </div>
        
        <!-- Add more placeholder cards as needed -->
      </div>
      
      <!-- Load More Button -->
      <div class="flex justify-center mt-8">
        <button class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
          Load More
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
// Component logic will be implemented later
</script> 