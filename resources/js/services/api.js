import axios from 'axios';
import { useAuthStore } from '../stores/auth';

// Create a custom axios instance
const api = axios.create({
  baseURL: import.meta.env.VITE_APP_URL || 'http://127.0.0.1:8000',
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  withCredentials: true
});

// Add a request interceptor to add auth token and CSRF token
api.interceptors.request.use(
  async (config) => {
    try {
      // Get auth store instance
      const authStore = useAuthStore();
      
      // Check if we need to refresh CSRF token
      if (!document.cookie.includes('XSRF-TOKEN')) {
        console.log('No CSRF token found, fetching new one');
        await authStore.getCsrfToken();
      }
      
      // Get token from auth store
      const token = authStore.token;
      
      // Add auth token if available
      if (token) {
        config.headers['Authorization'] = `Bearer ${token}`;
        console.log('Adding auth token to request');
      } else {
        console.log('No auth token available');
      }
      
      // Get CSRF token from cookie
      const getCookie = (name) => {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) {
          return decodeURIComponent(parts.pop().split(';').shift());
        }
        return '';
      };
      
      // Add CSRF token if available
      const csrfToken = getCookie('XSRF-TOKEN');
      if (csrfToken) {
        config.headers['X-XSRF-TOKEN'] = csrfToken;
        console.log('Adding CSRF token to request');
      } else {
        console.warn('No CSRF token available after refresh attempt');
      }
      
      return config;
    } catch (error) {
      console.error('Error in request interceptor:', error);
      return config;
    }
  },
  (error) => {
    console.error('Request interceptor error:', error);
    return Promise.reject(error);
  }
);

// Add a response interceptor to handle auth errors
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    // Log the error for debugging
    console.error('API response error:', error.response?.status, error.response?.data);
    
    // Handle 401 Unauthorized errors
    if (error.response && error.response.status === 401) {
      console.log('Unauthorized response detected');
      const authStore = useAuthStore();
      
      // Try to refresh auth status
      try {
        const isAuthenticated = await authStore.checkAuth();
        if (!isAuthenticated) {
          console.log('Auth check failed, clearing auth data');
          authStore.clearAuth();
          window.location.href = '/login';
        }
      } catch (authError) {
        console.error('Auth check error:', authError);
        authStore.clearAuth();
        window.location.href = '/login';
      }
    }
    
    return Promise.reject(error);
  }
);

export default api; 