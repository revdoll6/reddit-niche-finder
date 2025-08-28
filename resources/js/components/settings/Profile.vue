<template>
  <div class="p-6">
    <div class="max-w-3xl mx-auto">
      <h2 class="text-xl font-semibold text-gray-900 mb-6">Profile Settings</h2>
      
      <!-- Profile Form -->
      <div class="bg-white rounded-lg border border-gray-200 p-6 mb-8">
        <div class="space-y-6">
          <!-- Profile Picture -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Profile Picture</label>
            <div class="flex items-center">
              <div class="w-20 h-20 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden mr-4">
                <svg class="w-12 h-12 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                </svg>
              </div>
              <div>
                <button class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 mb-2 block">
                  Upload New Picture
                </button>
                <p class="text-xs text-gray-500">JPG, PNG or GIF. 1MB max.</p>
              </div>
            </div>
          </div>
          
          <!-- Name -->
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
            <input
              id="name"
              type="text"
              v-model="profileForm.name"
              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              @blur="updateName"
            >
            <p v-if="nameUpdateStatus" class="mt-1 text-sm" :class="nameUpdateStatus === 'success' ? 'text-green-600' : 'text-red-600'">
              {{ nameUpdateMessage }}
            </p>
          </div>
          
          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
            <input
              id="email"
              type="email"
              v-model="profileForm.email"
              disabled
              class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-500"
            >
            <p class="mt-1 text-xs text-gray-500">Email cannot be changed. Contact support if needed.</p>
          </div>
          
          <!-- Bio -->
          <div>
            <label for="bio" class="block text-sm font-medium text-gray-700 mb-2">Bio</label>
            <textarea
              id="bio"
              rows="4"
              v-model="profileForm.bio"
              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              placeholder="Tell us about yourself"
            ></textarea>
          </div>
          
          <div class="flex justify-end">
            <button 
              class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
              @click="saveProfile"
              :disabled="isSaving"
            >
              {{ isSaving ? 'Saving...' : 'Save Changes' }}
            </button>
          </div>
        </div>
      </div>
      
      <!-- Change Password -->
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Change Password</h3>
        <div class="space-y-4">
          <div>
            <label for="current-password" class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
            <input
              id="current-password"
              type="password"
              v-model="passwordForm.current_password"
              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
            >
          </div>
          
          <div>
            <label for="new-password" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
            <input
              id="new-password"
              type="password"
              v-model="passwordForm.password"
              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
            >
          </div>
          
          <div>
            <label for="confirm-password" class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
            <input
              id="confirm-password"
              type="password"
              v-model="passwordForm.password_confirmation"
              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
            >
          </div>
          
          <div v-if="passwordUpdateStatus" class="p-3 rounded-md" :class="passwordUpdateStatus === 'success' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'">
            {{ passwordUpdateMessage }}
          </div>
          
          <div class="flex justify-end">
            <button 
              class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
              @click="updatePassword"
              :disabled="isUpdatingPassword"
            >
              {{ isUpdatingPassword ? 'Updating...' : 'Update Password' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useAuthStore } from '../../stores/auth';

const authStore = useAuthStore();

// Profile form
const profileForm = reactive({
  name: '',
  email: '',
  bio: ''
});

// Password form
const passwordForm = reactive({
  current_password: '',
  password: '',
  password_confirmation: ''
});

// Status flags
const isSaving = ref(false);
const isUpdatingPassword = ref(false);
const nameUpdateStatus = ref('');
const nameUpdateMessage = ref('');
const passwordUpdateStatus = ref('');
const passwordUpdateMessage = ref('');

// Load user data
onMounted(() => {
  if (authStore.user) {
    profileForm.name = authStore.user.name || '';
    profileForm.email = authStore.user.email || '';
    // Bio would be loaded here if we had it in the database
  }
});

// Update name when user leaves the field
const updateName = async () => {
  if (!profileForm.name) {
    nameUpdateStatus.value = 'error';
    nameUpdateMessage.value = 'Name cannot be empty';
    return;
  }
  
  if (profileForm.name === authStore.user.name) {
    // No change, do nothing
    nameUpdateStatus.value = '';
    nameUpdateMessage.value = '';
    return;
  }
  
  try {
    nameUpdateStatus.value = '';
    nameUpdateMessage.value = 'Saving...';
    
    // Get CSRF token first
    await authStore.getCsrfToken();
    
    const response = await fetch('/api/profile/update-name', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${authStore.token}`,
        'X-XSRF-TOKEN': authStore.getCookie('XSRF-TOKEN')
      },
      credentials: 'include',
      body: JSON.stringify({
        name: profileForm.name
      })
    });
    
    const data = await response.json();
    
    if (response.ok) {
      // Update the user in the auth store
      authStore.setUser({
        ...authStore.user,
        name: profileForm.name
      });
      
      nameUpdateStatus.value = 'success';
      nameUpdateMessage.value = 'Name updated successfully';
      
      // Clear the message after 3 seconds
      setTimeout(() => {
        nameUpdateStatus.value = '';
        nameUpdateMessage.value = '';
      }, 3000);
    } else {
      nameUpdateStatus.value = 'error';
      nameUpdateMessage.value = data.message || 'Failed to update name';
    }
  } catch (error) {
    console.error('Update name error:', error);
    nameUpdateStatus.value = 'error';
    nameUpdateMessage.value = error.message || 'An error occurred';
  }
};

// Save profile changes
const saveProfile = async () => {
  if (!profileForm.name) {
    nameUpdateStatus.value = 'error';
    nameUpdateMessage.value = 'Name cannot be empty';
    return;
  }
  
  try {
    isSaving.value = true;
    
    // Get CSRF token first
    await authStore.getCsrfToken();
    
    const response = await fetch('/api/profile/update', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${authStore.token}`,
        'X-XSRF-TOKEN': authStore.getCookie('XSRF-TOKEN')
      },
      credentials: 'include',
      body: JSON.stringify({
        name: profileForm.name,
        bio: profileForm.bio
      })
    });
    
    const data = await response.json();
    
    if (response.ok) {
      // Update the user in the auth store
      authStore.setUser({
        ...authStore.user,
        name: profileForm.name
      });
      
      nameUpdateStatus.value = 'success';
      nameUpdateMessage.value = 'Profile updated successfully';
      
      // Clear the message after 3 seconds
      setTimeout(() => {
        nameUpdateStatus.value = '';
        nameUpdateMessage.value = '';
      }, 3000);
    } else {
      nameUpdateStatus.value = 'error';
      nameUpdateMessage.value = data.message || 'Failed to update profile';
    }
  } catch (error) {
    console.error('Save profile error:', error);
    nameUpdateStatus.value = 'error';
    nameUpdateMessage.value = error.message || 'An error occurred';
  } finally {
    isSaving.value = false;
  }
};

// Update password
const updatePassword = async () => {
  // Validate password fields
  if (!passwordForm.current_password) {
    passwordUpdateStatus.value = 'error';
    passwordUpdateMessage.value = 'Current password is required';
    return;
  }
  
  if (!passwordForm.password) {
    passwordUpdateStatus.value = 'error';
    passwordUpdateMessage.value = 'New password is required';
    return;
  }
  
  if (passwordForm.password.length < 8) {
    passwordUpdateStatus.value = 'error';
    passwordUpdateMessage.value = 'Password must be at least 8 characters';
    return;
  }
  
  if (passwordForm.password !== passwordForm.password_confirmation) {
    passwordUpdateStatus.value = 'error';
    passwordUpdateMessage.value = 'Passwords do not match';
    return;
  }
  
  try {
    isUpdatingPassword.value = true;
    passwordUpdateStatus.value = '';
    passwordUpdateMessage.value = '';
    
    // Get CSRF token first
    await authStore.getCsrfToken();
    
    const response = await fetch('/api/profile/update-password', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${authStore.token}`,
        'X-XSRF-TOKEN': authStore.getCookie('XSRF-TOKEN')
      },
      credentials: 'include',
      body: JSON.stringify(passwordForm)
    });
    
    const data = await response.json();
    
    if (response.ok) {
      passwordUpdateStatus.value = 'success';
      passwordUpdateMessage.value = 'Password updated successfully';
      
      // Clear the form
      passwordForm.current_password = '';
      passwordForm.password = '';
      passwordForm.password_confirmation = '';
      
      // Clear the message after 3 seconds
      setTimeout(() => {
        passwordUpdateStatus.value = '';
        passwordUpdateMessage.value = '';
      }, 3000);
    } else {
      passwordUpdateStatus.value = 'error';
      passwordUpdateMessage.value = data.message || 'Failed to update password';
    }
  } catch (error) {
    console.error('Update password error:', error);
    passwordUpdateStatus.value = 'error';
    passwordUpdateMessage.value = error.message || 'An error occurred';
  } finally {
    isUpdatingPassword.value = false;
  }
};
</script> 