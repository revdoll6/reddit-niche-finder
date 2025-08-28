import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;
window.axios.defaults.baseURL = import.meta.env.VITE_APP_URL || 'http://127.0.0.1:8000';

// We'll handle authentication in the auth store now
// No need for interceptors here anymore

// Helper functions for cookies are now in the auth store
// These are kept for backward compatibility but should be removed in the future
window.getCsrfToken = async () => {
    console.warn('Deprecated: Use authStore.getCsrfToken() instead');
    try {
        await axios.get('/sanctum/csrf-cookie');
    } catch (error) {
        console.error('Error getting CSRF token:', error);
        throw error;
    }
};

window.getCookie = (name) => {
    console.warn('Deprecated: Use authStore.getCookie() instead');
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) {
        return decodeURIComponent(parts.pop().split(';').shift());
    }
    return null;
};
