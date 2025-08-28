import { defineStore } from 'pinia';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('user')) || null,
    token: localStorage.getItem('token') || null,
    loading: false,
    error: null,
  }),
  
  getters: {
    isAuthenticated: (state) => !!state.token,
    getUser: (state) => state.user,
  },
  
  actions: {
    async getCsrfToken() {
      try {
        await fetch('/sanctum/csrf-cookie', {
          credentials: 'include'
        });
      } catch (error) {
        console.error('Failed to get CSRF token:', error);
        throw error;
      }
    },
    
    async login(credentials) {
      this.loading = true;
      this.error = null;
      
      try {
        await this.getCsrfToken();
        
        const response = await fetch('/api/login', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-XSRF-TOKEN': this.getCookie('XSRF-TOKEN'),
          },
          credentials: 'include',
          body: JSON.stringify(credentials),
        });
        
        const data = await response.json();
        
        if (!response.ok) {
          throw new Error(data.message || 'Login failed');
        }
        
        this.setUser(data.user);
        this.setToken(data.token);
        
        return data;
      } catch (error) {
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },
    
    async register(userData) {
      this.loading = true;
      this.error = null;
      
      try {
        await this.getCsrfToken();
        
        const response = await fetch('/api/register', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-XSRF-TOKEN': this.getCookie('XSRF-TOKEN'),
          },
          credentials: 'include',
          body: JSON.stringify(userData),
        });
        
        const data = await response.json();
        
        if (!response.ok) {
          throw new Error(data.message || 'Registration failed');
        }
        
        this.setUser(data.user);
        this.setToken(data.token);
        
        return data;
      } catch (error) {
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },
    
    async logout() {
      this.loading = true;
      this.error = null;
      
      try {
        if (!this.token) {
          console.log('No token found, clearing auth data locally');
          this.clearAuth();
          return true;
        }
        
        await this.getCsrfToken();
        
        console.log('Attempting to logout with token');
        const response = await fetch('/api/logout', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': `Bearer ${this.token}`,
            'X-XSRF-TOKEN': this.getCookie('XSRF-TOKEN'),
          },
          credentials: 'include',
        });
        
        this.clearAuth();
        
        if (!response.ok) {
          console.warn('Logout API call failed, but auth data was cleared locally');
          if (response.status !== 401) {
            const data = await response.json();
            console.error('Logout error details:', data);
          }
        } else {
          console.log('Logout successful on server');
        }
        
        return true;
      } catch (error) {
        console.error('Logout error:', error);
        this.clearAuth();
        return true;
      } finally {
        this.loading = false;
      }
    },
    
    async checkAuth() {
      if (!this.token) return false;
      
      this.loading = true;
      this.error = null;
      
      try {
        await this.getCsrfToken();
        
        const response = await fetch('/api/user', {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': `Bearer ${this.token}`,
            'X-XSRF-TOKEN': this.getCookie('XSRF-TOKEN'),
          },
          credentials: 'include',
        });
        
        if (!response.ok) {
          console.error('Auth check failed:', response.status, response.statusText);
          this.clearAuth();
          return false;
        }
        
        const data = await response.json();
        this.setUser(data);
        
        return true;
      } catch (error) {
        console.error('Auth check error:', error);
        this.error = error.message;
        this.clearAuth();
        return false;
      } finally {
        this.loading = false;
      }
    },
    
    setUser(user) {
      this.user = user;
      localStorage.setItem('user', JSON.stringify(user));
    },
    
    setToken(token) {
      this.token = token;
      localStorage.setItem('token', token);
    },
    
    clearAuth() {
      this.user = null;
      this.token = null;
      
      // Clear all user-related data from localStorage
      localStorage.removeItem('user');
      localStorage.removeItem('token');
      
      // Clear search-related local storage items (be explicit about all items)
      localStorage.removeItem('lastSearchQuery');
      localStorage.removeItem('lastSearchResults');
      localStorage.removeItem('lastSearchTotal');
      localStorage.removeItem('lastSearchTimestamp');
      
      // Clear persisted Pinia stores
      for (const key in localStorage) {
        if (key.includes('-store') || key.includes('search')) {
          localStorage.removeItem(key);
        }
      }
      
      // Clear sessionStorage items as well
      sessionStorage.clear();
      
      // Make a more reliable server-side logout request
      try {
        fetch('/api/logout', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-XSRF-TOKEN': this.getCookie('XSRF-TOKEN'),
          },
          credentials: 'include',
        }).then(response => {
          console.log('Server session cleared successfully');
        }).catch(error => {
          console.error('Error clearing server session:', error);
        });
      } catch (error) {
        console.error('Error during logout request:', error);
      }
      
      // Clear cookies by setting them to expire
      document.cookie.split(";").forEach(cookie => {
        const name = cookie.split("=")[0].trim();
        if (name.includes('search') || name.includes('session') || name.includes('XSRF')) {
          document.cookie = `${name}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;`;
        }
      });
    },
    
    getCookie(name) {
      const value = `; ${document.cookie}`;
      const parts = value.split(`; ${name}=`);
      if (parts.length === 2) {
        return decodeURIComponent(parts.pop().split(';').shift());
      }
      return '';
    },
  },
}); 