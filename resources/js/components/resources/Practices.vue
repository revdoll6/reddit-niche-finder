<template>
  <div class="p-6">
    <!-- Best Practices Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-white p-6 rounded-lg border border-gray-200">
        <h3 class="text-sm font-medium text-gray-500">Best Practices</h3>
        <p class="mt-2 text-3xl font-semibold text-gray-900">24</p>
        <div class="mt-2 text-sm text-gray-600">Available resources</div>
      </div>

      <div class="bg-white p-6 rounded-lg border border-gray-200">
        <h3 class="text-sm font-medium text-gray-500">Most Viewed</h3>
        <p class="mt-2 text-3xl font-semibold text-gray-900">Content Strategy</p>
        <div class="mt-2 text-sm text-gray-600">Viewed 128 times</div>
      </div>

      <div class="bg-white p-6 rounded-lg border border-gray-200">
        <h3 class="text-sm font-medium text-gray-500">Last Updated</h3>
        <p class="mt-2 text-3xl font-semibold text-gray-900">2 days ago</p>
        <div class="mt-2 text-sm text-gray-600">Community Engagement</div>
      </div>
    </div>

    <!-- Categories -->
    <div class="bg-white rounded-lg border border-gray-200 mb-8">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-medium text-gray-900">Categories</h2>
      </div>
      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <!-- Content Strategy -->
          <div class="border border-gray-200 rounded-lg p-6">
            <div class="flex items-center mb-4">
              <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
              </div>
              <h3 class="text-lg font-medium text-gray-900">Content Strategy</h3>
            </div>
            <p class="text-sm text-gray-600 mb-4">
              Learn how to create effective content strategies for Reddit communities.
            </p>
            <div class="flex justify-end">
              <button class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                View Resources
              </button>
            </div>
          </div>

          <!-- Community Engagement -->
          <div class="border border-gray-200 rounded-lg p-6">
            <div class="flex items-center mb-4">
              <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
              </div>
              <h3 class="text-lg font-medium text-gray-900">Community Engagement</h3>
            </div>
            <p class="text-sm text-gray-600 mb-4">
              Best practices for engaging with Reddit communities and building relationships.
            </p>
            <div class="flex justify-end">
              <button class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                View Resources
              </button>
            </div>
          </div>

          <!-- Growth Tactics -->
          <div class="border border-gray-200 rounded-lg p-6">
            <div class="flex items-center mb-4">
              <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
              </div>
              <h3 class="text-lg font-medium text-gray-900">Growth Tactics</h3>
            </div>
            <p class="text-sm text-gray-600 mb-4">
              Proven strategies for growing your subreddit and increasing engagement.
            </p>
            <div class="flex justify-end">
              <button class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                View Resources
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Featured Best Practices -->
    <div class="bg-white rounded-lg border border-gray-200">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-medium text-gray-900">Featured Best Practices</h2>
      </div>
      <div class="p-6">
        <div class="space-y-6">
          <!-- Practice 1 -->
          <div class="border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Effective Subreddit Moderation</h3>
            <div class="flex items-center text-sm text-gray-500 mb-4">
              <span>Category: Community Management</span>
              <span class="mx-2">•</span>
              <span>10 min read</span>
            </div>
            <p class="text-sm text-gray-600 mb-4">
              Learn the best practices for moderating a subreddit effectively, including setting up rules, 
              automoderator configuration, and handling community conflicts.
            </p>
            <div class="flex justify-end">
              <button class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Read More
              </button>
            </div>
          </div>

          <!-- Practice 2 -->
          <div class="border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Creating Engaging Weekly Threads</h3>
            <div class="flex items-center text-sm text-gray-500 mb-4">
              <span>Category: Content Strategy</span>
              <span class="mx-2">•</span>
              <span>8 min read</span>
            </div>
            <p class="text-sm text-gray-600 mb-4">
              Discover how to create and maintain engaging weekly threads that keep your community active 
              and encourage regular participation from members.
            </p>
            <div class="flex justify-end">
              <button class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Read More
              </button>
            </div>
          </div>

          <!-- Practice 3 -->
          <div class="border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Optimizing Post Titles for Engagement</h3>
            <div class="flex items-center text-sm text-gray-500 mb-4">
              <span>Category: Growth Tactics</span>
              <span class="mx-2">•</span>
              <span>6 min read</span>
            </div>
            <p class="text-sm text-gray-600 mb-4">
              Learn how to craft post titles that capture attention, drive engagement, and increase 
              visibility in Reddit's algorithm.
            </p>
            <div class="flex justify-end">
              <button class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Read More
              </button>
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