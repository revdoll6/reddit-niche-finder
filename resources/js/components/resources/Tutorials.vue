<template>
  <div class="p-6">
    <!-- Tutorials Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-white p-6 rounded-lg border border-gray-200">
        <h3 class="text-sm font-medium text-gray-500">Tutorials</h3>
        <p class="mt-2 text-3xl font-semibold text-gray-900">32</p>
        <div class="mt-2 text-sm text-gray-600">Available tutorials</div>
      </div>

      <div class="bg-white p-6 rounded-lg border border-gray-200">
        <h3 class="text-sm font-medium text-gray-500">Most Viewed</h3>
        <p class="mt-2 text-3xl font-semibold text-gray-900">Reddit API</p>
        <div class="mt-2 text-sm text-gray-600">Viewed 215 times</div>
      </div>

      <div class="bg-white p-6 rounded-lg border border-gray-200">
        <h3 class="text-sm font-medium text-gray-500">Last Added</h3>
        <p class="mt-2 text-3xl font-semibold text-gray-900">3 days ago</p>
        <div class="mt-2 text-sm text-gray-600">Analytics Dashboard</div>
      </div>
    </div>

    <!-- Tutorial Categories -->
    <div class="bg-white rounded-lg border border-gray-200 mb-8">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-medium text-gray-900">Categories</h2>
      </div>
      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <!-- Getting Started -->
          <div class="border border-gray-200 rounded-lg p-6">
            <div class="flex items-center mb-4">
              <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
              </div>
              <h3 class="text-lg font-medium text-gray-900">Getting Started</h3>
            </div>
            <p class="text-sm text-gray-600 mb-4">
              Beginner tutorials for those new to Reddit Niche Finder and subreddit analysis.
            </p>
            <div class="flex justify-end">
              <button class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                View Tutorials
              </button>
            </div>
          </div>

          <!-- API Integration -->
          <div class="border border-gray-200 rounded-lg p-6">
            <div class="flex items-center mb-4">
              <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                </svg>
              </div>
              <h3 class="text-lg font-medium text-gray-900">API Integration</h3>
            </div>
            <p class="text-sm text-gray-600 mb-4">
              Tutorials for working with the Reddit API and integrating with our platform.
            </p>
            <div class="flex justify-end">
              <button class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                View Tutorials
              </button>
            </div>
          </div>

          <!-- Advanced Analytics -->
          <div class="border border-gray-200 rounded-lg p-6">
            <div class="flex items-center mb-4">
              <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
              </div>
              <h3 class="text-lg font-medium text-gray-900">Advanced Analytics</h3>
            </div>
            <p class="text-sm text-gray-600 mb-4">
              In-depth tutorials for advanced analytics and data interpretation.
            </p>
            <div class="flex justify-end">
              <button class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                View Tutorials
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Featured Tutorials -->
    <div class="bg-white rounded-lg border border-gray-200">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-medium text-gray-900">Featured Tutorials</h2>
      </div>
      <div class="p-6">
        <div class="space-y-6">
          <!-- Tutorial 1 -->
          <div class="border border-gray-200 rounded-lg p-6">
            <div class="flex items-start">
              <div class="flex-shrink-0 w-24 h-16 bg-gray-200 rounded-md overflow-hidden mr-4">
                <img src="https://via.placeholder.com/150x100" alt="Tutorial thumbnail" class="w-full h-full object-cover">
              </div>
              <div class="flex-1">
                <h3 class="text-lg font-medium text-gray-900 mb-1">Getting Started with Reddit Niche Finder</h3>
                <div class="flex items-center text-sm text-gray-500 mb-2">
                  <span>Category: Getting Started</span>
                  <span class="mx-2">•</span>
                  <span>15 min</span>
                </div>
                <p class="text-sm text-gray-600 mb-3">
                  A comprehensive introduction to the Reddit Niche Finder platform, including setup, 
                  navigation, and basic functionality.
                </p>
                <div class="flex justify-end">
                  <button class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Watch Tutorial
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Tutorial 2 -->
          <div class="border border-gray-200 rounded-lg p-6">
            <div class="flex items-start">
              <div class="flex-shrink-0 w-24 h-16 bg-gray-200 rounded-md overflow-hidden mr-4">
                <img src="https://via.placeholder.com/150x100" alt="Tutorial thumbnail" class="w-full h-full object-cover">
              </div>
              <div class="flex-1">
                <h3 class="text-lg font-medium text-gray-900 mb-1">Working with the Reddit API</h3>
                <div class="flex items-center text-sm text-gray-500 mb-2">
                  <span>Category: API Integration</span>
                  <span class="mx-2">•</span>
                  <span>25 min</span>
                </div>
                <p class="text-sm text-gray-600 mb-3">
                  Learn how to set up and use the Reddit API with our platform, including authentication, 
                  rate limiting, and common endpoints.
                </p>
                <div class="flex justify-end">
                  <button class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Watch Tutorial
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Tutorial 3 -->
          <div class="border border-gray-200 rounded-lg p-6">
            <div class="flex items-start">
              <div class="flex-shrink-0 w-24 h-16 bg-gray-200 rounded-md overflow-hidden mr-4">
                <img src="https://via.placeholder.com/150x100" alt="Tutorial thumbnail" class="w-full h-full object-cover">
              </div>
              <div class="flex-1">
                <h3 class="text-lg font-medium text-gray-900 mb-1">Advanced Analytics Dashboard</h3>
                <div class="flex items-center text-sm text-gray-500 mb-2">
                  <span>Category: Advanced Analytics</span>
                  <span class="mx-2">•</span>
                  <span>20 min</span>
                </div>
                <p class="text-sm text-gray-600 mb-3">
                  Dive deep into our analytics dashboard to uncover insights about subreddit growth, 
                  engagement patterns, and content performance.
                </p>
                <div class="flex justify-end">
                  <button class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Watch Tutorial
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// Component logic will be implemented later
</script> 