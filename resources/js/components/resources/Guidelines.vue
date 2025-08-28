<template>
  <div class="p-6">
    <!-- Guidelines Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-white p-6 rounded-lg border border-gray-200">
        <h3 class="text-sm font-medium text-gray-500">Guidelines</h3>
        <p class="mt-2 text-3xl font-semibold text-gray-900">18</p>
        <div class="mt-2 text-sm text-gray-600">Available guidelines</div>
      </div>

      <div class="bg-white p-6 rounded-lg border border-gray-200">
        <h3 class="text-sm font-medium text-gray-500">Most Referenced</h3>
        <p class="mt-2 text-3xl font-semibold text-gray-900">Content Policy</p>
        <div class="mt-2 text-sm text-gray-600">Referenced 95 times</div>
      </div>

      <div class="bg-white p-6 rounded-lg border border-gray-200">
        <h3 class="text-sm font-medium text-gray-500">Last Updated</h3>
        <p class="mt-2 text-3xl font-semibold text-gray-900">1 week ago</p>
        <div class="mt-2 text-sm text-gray-600">Promotion Guidelines</div>
      </div>
    </div>

    <!-- Guidelines Categories -->
    <div class="bg-white rounded-lg border border-gray-200 mb-8">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-medium text-gray-900">Categories</h2>
      </div>
      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <!-- Content Policy -->
          <div class="border border-gray-200 rounded-lg p-6">
            <div class="flex items-center mb-4">
              <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-3">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
              </div>
              <h3 class="text-lg font-medium text-gray-900">Content Policy</h3>
            </div>
            <p class="text-sm text-gray-600 mb-4">
              Guidelines for acceptable content on Reddit and how to ensure compliance.
            </p>
            <div class="flex justify-end">
              <button class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                View Guidelines
              </button>
            </div>
          </div>

          <!-- Promotion Guidelines -->
          <div class="border border-gray-200 rounded-lg p-6">
            <div class="flex items-center mb-4">
              <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center mr-3">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
                </svg>
              </div>
              <h3 class="text-lg font-medium text-gray-900">Promotion Guidelines</h3>
            </div>
            <p class="text-sm text-gray-600 mb-4">
              Rules for promoting content, products, or services on Reddit without being flagged as spam.
            </p>
            <div class="flex justify-end">
              <button class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                View Guidelines
              </button>
            </div>
          </div>

          <!-- Community Rules -->
          <div class="border border-gray-200 rounded-lg p-6">
            <div class="flex items-center mb-4">
              <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
              </div>
              <h3 class="text-lg font-medium text-gray-900">Community Rules</h3>
            </div>
            <p class="text-sm text-gray-600 mb-4">
              Guidelines for creating effective community rules that maintain order and foster engagement.
            </p>
            <div class="flex justify-end">
              <button class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                View Guidelines
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Featured Guidelines -->
    <div class="bg-white rounded-lg border border-gray-200">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-medium text-gray-900">Featured Guidelines</h2>
      </div>
      <div class="p-6">
        <div class="space-y-6">
          <!-- Guideline 1 -->
          <div class="border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Reddit Content Policy Compliance</h3>
            <div class="flex items-center text-sm text-gray-500 mb-4">
              <span>Category: Content Policy</span>
              <span class="mx-2">•</span>
              <span>Last updated: 2 months ago</span>
            </div>
            <p class="text-sm text-gray-600 mb-4">
              A comprehensive guide to understanding and complying with Reddit's content policy, 
              including prohibited content, restricted content, and enforcement actions.
            </p>
            <div class="flex justify-end">
              <button class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Read More
              </button>
            </div>
          </div>

          <!-- Guideline 2 -->
          <div class="border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Self-Promotion Without Spamming</h3>
            <div class="flex items-center text-sm text-gray-500 mb-4">
              <span>Category: Promotion Guidelines</span>
              <span class="mx-2">•</span>
              <span>Last updated: 1 week ago</span>
            </div>
            <p class="text-sm text-gray-600 mb-4">
              Guidelines for promoting your content or business on Reddit while maintaining a healthy 
              balance of engagement and avoiding being flagged as spam.
            </p>
            <div class="flex justify-end">
              <button class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Read More
              </button>
            </div>
          </div>

          <!-- Guideline 3 -->
          <div class="border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Creating Effective Subreddit Rules</h3>
            <div class="flex items-center text-sm text-gray-500 mb-4">
              <span>Category: Community Rules</span>
              <span class="mx-2">•</span>
              <span>Last updated: 3 weeks ago</span>
            </div>
            <p class="text-sm text-gray-600 mb-4">
              A guide to creating clear, enforceable, and effective rules for your subreddit that 
              help maintain a positive community atmosphere.
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