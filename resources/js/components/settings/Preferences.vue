<template>
  <div class="p-6">
    <div class="max-w-3xl mx-auto">
      <h2 class="text-xl font-semibold text-gray-900 mb-6">Preferences</h2>
      
      <!-- Notification Settings -->
      <div class="bg-white rounded-lg border border-gray-200 p-6 mb-8">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Notification Settings</h3>
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <div>
              <h4 class="text-sm font-medium text-gray-900">Email Notifications</h4>
              <p class="text-sm text-gray-500">Receive email updates about your tracked subreddits</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" checked class="sr-only peer">
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
            </label>
          </div>
          
          <div class="flex items-center justify-between">
            <div>
              <h4 class="text-sm font-medium text-gray-900">Weekly Reports</h4>
              <p class="text-sm text-gray-500">Receive weekly summary reports of your tracked communities</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" checked class="sr-only peer">
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
            </label>
          </div>
          
          <div class="flex items-center justify-between">
            <div>
              <h4 class="text-sm font-medium text-gray-900">Alert Notifications</h4>
              <p class="text-sm text-gray-500">Receive alerts when significant changes occur in your communities</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" checked class="sr-only peer">
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
            </label>
          </div>
        </div>
      </div>
      
      <!-- Display Settings -->
      <div class="bg-white rounded-lg border border-gray-200 p-6 mb-8">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Display Settings</h3>
        <div class="space-y-4">
          <div>
            <label for="theme" class="block text-sm font-medium text-gray-700 mb-2">Theme</label>
            <select id="theme" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
              <option value="light">Light</option>
              <option value="dark">Dark</option>
              <option value="system">System Default</option>
            </select>
          </div>
          
          <div>
            <label for="timezone" class="block text-sm font-medium text-gray-700 mb-2">Timezone</label>
            <select id="timezone" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
              <option value="utc">UTC</option>
              <option value="est" selected>Eastern Time (EST)</option>
              <option value="cst">Central Time (CST)</option>
              <option value="mst">Mountain Time (MST)</option>
              <option value="pst">Pacific Time (PST)</option>
            </select>
          </div>
          
          <div>
            <label for="date-format" class="block text-sm font-medium text-gray-700 mb-2">Date Format</label>
            <select id="date-format" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
              <option value="mdy">MM/DD/YYYY</option>
              <option value="dmy">DD/MM/YYYY</option>
              <option value="ymd">YYYY-MM-DD</option>
            </select>
          </div>
        </div>
      </div>
      
      <!-- Data Settings -->
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Data Settings</h3>
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <div>
              <h4 class="text-sm font-medium text-gray-900">Data Collection</h4>
              <p class="text-sm text-gray-500">Allow us to collect anonymous usage data to improve the service</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" checked class="sr-only peer">
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
            </label>
          </div>
          
          <div>
            <button class="px-4 py-2 bg-red-100 text-red-700 rounded-md hover:bg-red-200">
              Export My Data
            </button>
          </div>
          
          <div>
            <button class="px-4 py-2 bg-red-100 text-red-700 rounded-md hover:bg-red-200">
              Delete My Account
            </button>
          </div>
        </div>
      </div>
      
      <div class="mt-8 flex justify-end">
        <button class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
          Save Preferences
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
// Component logic will be implemented later
</script> 