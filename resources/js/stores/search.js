import { defineStore } from 'pinia';
import api from '../services/api';
import { useAuthStore } from './auth';

// Helper function to safely parse localStorage items
const safeGetItem = (key, defaultValue) => {
  try {
    const item = localStorage.getItem(key);
    if (!item) return defaultValue;
    return JSON.parse(item);
  } catch (e) {
    console.warn(`Error parsing localStorage item ${key}:`, e);
    return defaultValue;
  }
};

// Helper function to safely parse numbers
const safeParseNumber = (value, defaultValue = 0) => {
  if (value === null || value === undefined) return defaultValue;
  const parsed = parseFloat(value);
  return isNaN(parsed) ? defaultValue : parsed;
};

// Helper function to normalize subreddit data to ensure valid numbers
const normalizeSubredditData = (subreddit) => {
  if (!subreddit || typeof subreddit !== 'object') return null;
  
  // First ensure all numeric values are properly parsed
  const normalized = {
    ...subreddit,
    subscribers: safeParseNumber(subreddit.subscribers),
    active_user_count: safeParseNumber(subreddit.active_user_count),
    posts_per_day: safeParseNumber(subreddit.posts_per_day),
    engagement_rate: safeParseNumber(subreddit.engagement_rate),
    active_engagement: safeParseNumber(subreddit.active_engagement, null),
    keyword_engagement: safeParseNumber(subreddit.keyword_engagement, null),
    growth_rate: safeParseNumber(subreddit.growth_rate),
    opportunity_score: safeParseNumber(subreddit.opportunity_score),
    base_relevancy_score: safeParseNumber(subreddit.base_relevancy_score),
    content_relevancy_score: safeParseNumber(subreddit.content_relevancy_score),
    metadata_exact_match_score: safeParseNumber(subreddit.metadata_exact_match_score),
    metadata_ngram_match_score: safeParseNumber(subreddit.metadata_ngram_match_score),
    metadata_jaccard_score: safeParseNumber(subreddit.metadata_jaccard_score)
  };
  
  // Then estimate any missing engagement metrics
  return estimateMissingEngagementMetrics(normalized);
};

// Helper function to check if a timestamp is fresh (less than 120 minutes old)
const isTimestampFresh = (timestamp) => {
  if (!timestamp) return false;
  
  try {
    // Ensure timestamp is a valid number
    const parsedTime = parseInt(timestamp, 10);
    if (isNaN(parsedTime)) {
      console.warn('Invalid timestamp format:', timestamp);
      return false;
    }
    
    const now = Date.now();
    const twoHoursInMs = 2 * 60 * 60 * 1000; // 2 hours (120 minutes)
    const isFresh = (now - parsedTime) < twoHoursInMs;
    
    if (!isFresh) {
      console.log(`Search state expired - timestamp: ${new Date(parsedTime).toLocaleString()}, now: ${new Date(now).toLocaleString()}`);
    }
    
    return isFresh;
  } catch (error) {
    console.error('Error validating timestamp freshness:', error);
    return false;
  }
};

// Add a function to estimate missing engagement metrics
const estimateMissingEngagementMetrics = (subreddit) => {
  if (!subreddit) return subreddit;
  
  // Ensure we have a valid object
  const result = { ...subreddit };
  
  // If we have engagement_rate but missing active_engagement, estimate it
  if (result.engagement_rate > 0 && 
      (result.active_engagement === null || 
       result.active_engagement === undefined || 
       result.active_engagement === 0)) {
    // Active engagement is typically higher than average engagement
    result.active_engagement = result.engagement_rate * 1.2;
    console.log(`Estimated active_engagement for ${result.display_name}: ${result.active_engagement}`);
  }
  
  // If we have engagement_rate but missing keyword_engagement, estimate it
  if (result.engagement_rate > 0 && 
      (result.keyword_engagement === null || 
       result.keyword_engagement === undefined || 
       result.keyword_engagement === 0)) {
    // Keyword engagement is typically slightly higher than average
    result.keyword_engagement = result.engagement_rate * 1.05;
    console.log(`Estimated keyword_engagement for ${result.display_name}: ${result.keyword_engagement}`);
  }
  
  return result;
};

export const useSearchStore = defineStore('search', {
  state: () => {
    // Check if there's a logged-in user first
    const authStore = useAuthStore();
    if (!authStore.isAuthenticated) {
      return getDefaultState();
    }
    
    try {
      // Check if we have valid stored state
      const storedState = localStorage.getItem('search-store-state');
      if (storedState) {
        const state = JSON.parse(storedState);
        const timestamp = state.lastSearchTimestamp;
        
        // Check if the data is still fresh
        if (isTimestampFresh(timestamp)) {
          console.log('Restoring search state from storage');
          return {
            ...state,
            results: state.results.map(normalizeSubredditData).filter(Boolean),
    loading: false,
            error: null
          };
        }
      }
    } catch (error) {
      console.warn('Error restoring search state:', error);
    }
    
    return getDefaultState();
  },

  getters: {
    filteredResults: (state) => {
      console.log('Computing filtered results in store with filters:', state.filters);
      if (!state.results || !Array.isArray(state.results)) {
        console.warn('Results array is invalid:', state.results);
        return [];
      }

      // Make a copy of results to avoid modifying the original
      let results = [...state.results];
      const originalCount = results.length;
      
      try {
        // Apply minimum members filter
        if (state.filters.minMembers) {
          const minMembers = parseInt(state.filters.minMembers);
          console.log(`Applying minMembers filter: ${minMembers}`);
          
          results = results.filter(sr => {
            const subscribers = safeParseNumber(sr.subscribers, 0);
            return subscribers >= minMembers;
          });
          
          console.log(`After minMembers filter: ${results.length} of ${originalCount} remaining`);
        }
        
        // Apply category filter
        if (state.filters.category) {
          console.log(`Applying category filter: ${state.filters.category}`);
          
          results = results.filter(sr => {
            const topics = Array.isArray(sr.topics) ? sr.topics : [];
            return topics.includes(state.filters.category.toLowerCase());
          });
          
          console.log(`After category filter: ${results.length} of ${originalCount} remaining`);
        }
        
        // Apply activity level filter
        if (state.filters.activityLevel) {
          console.log(`Applying activityLevel filter: ${state.filters.activityLevel}`);
          
          results = results.filter(sr => {
            const postsPerDay = safeParseNumber(sr.posts_per_day, 0);
            const level = getActivityLevel(postsPerDay);
            return level === state.filters.activityLevel;
          });
          
          console.log(`After activityLevel filter: ${results.length} of ${originalCount} remaining`);
        }
        
        // Apply content type filters
        const activeContentTypes = Object.entries(state.filters.contentTypes)
          .filter(([, isActive]) => isActive)
          .map(([type]) => type);
        
        if (activeContentTypes.length > 0) {
          console.log(`Applying contentTypes filter: ${activeContentTypes.join(', ')}`);
          
          results = results.filter(sr => {
            const types = Array.isArray(sr.content_types) ? sr.content_types : [];
            return activeContentTypes.some(type => types.includes(type));
          });
          
          console.log(`After contentTypes filter: ${results.length} of ${originalCount} remaining`);
        }
        
        // Apply moderation level filter
        if (state.filters.moderationLevel) {
          console.log(`Applying moderationLevel filter: ${state.filters.moderationLevel}`);
          
          results = results.filter(sr => sr.moderation_level === state.filters.moderationLevel);
          
          console.log(`After moderationLevel filter: ${results.length} of ${originalCount} remaining`);
        }
        
        return results;
      } catch (error) {
        console.error('Error in filteredResults getter:', error);
        return [];
      }
    },

    sortedResults: (state) => {
      console.log(`Computing sorted results in store with sort: ${state.sortBy}`);
      if (!Array.isArray(state.filteredResults)) {
        console.warn('FilteredResults is not an array:', state.filteredResults);
        return [];
      }

      try {
        const results = [...state.filteredResults];
        const sortField = state.sortBy || 'opportunity_score';
        
        console.log(`Sorting by field: ${sortField}`);
        
        // Sort according to specified field
        switch (sortField) {
          case 'subscribers':
            return results.sort((a, b) => safeParseNumber(b.subscribers) - safeParseNumber(a.subscribers));
          case 'active_user_count':
            return results.sort((a, b) => safeParseNumber(b.active_user_count) - safeParseNumber(a.active_user_count));
          case 'created_utc':
            return results.sort((a, b) => safeParseNumber(b.created_utc) - safeParseNumber(a.created_utc));
          case 'growth_rate':
            return results.sort((a, b) => safeParseNumber(b.growth_rate) - safeParseNumber(a.growth_rate));
          case 'engagement_rate':
            return results.sort((a, b) => safeParseNumber(b.engagement_rate) - safeParseNumber(a.engagement_rate));
          case 'opportunity_score':
          default:
            return results.sort((a, b) => safeParseNumber(b.opportunity_score) - safeParseNumber(a.opportunity_score));
        }
      } catch (error) {
        console.error('Error in sortedResults getter:', error);
        return [];
      }
    }
  },

  actions: {
    async searchSubreddits(query) {
      if (!query) return;

      this.loading = true;
      this.error = null;
      const authStore = useAuthStore();
      
      try {
        // First try to get results from session
        const sessionResponse = await api.get('/api/reddit/subreddits/search/retrieve', {
          params: { query },
          headers: {
            'Authorization': `Bearer ${authStore.token}`,
            'X-XSRF-TOKEN': authStore.getCookie('XSRF-TOKEN')
          }
        });

        if (sessionResponse.data.status === 'success') {
          console.log('Using results from session');
          const sessionData = sessionResponse.data.data;
          this.updateSearchState(sessionData);
          return this.results;
        }

        // If no session data, perform a new search
        const response = await api.get('/api/reddit/subreddits/search', {
          params: { query },
          headers: {
            'Authorization': `Bearer ${authStore.token}`,
            'X-XSRF-TOKEN': authStore.getCookie('XSRF-TOKEN')
          }
        });
        
        if (response.data && response.data.status === 'success') {
          console.log(`Search successful (source: ${response.data.source})`);
          const searchData = response.data.data;
          this.updateSearchState(searchData);
          
          // Store results in session for future use
          await this.storeResultsInSession(searchData);

          return this.results;
        } else {
          throw new Error('Invalid API response format');
        }
      } catch (error) {
        console.error('Search error:', error);
        this.error = error.message || 'An error occurred while searching';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async storeResultsInSession(data) {
      const authStore = useAuthStore();
      try {
        await api.post('/api/reddit/subreddits/search/store', {
          query: data.query,
          results: data.results,
          total_results: data.total_results,
          timestamp: data.timestamp || Date.now()
        }, {
          headers: {
            'Authorization': `Bearer ${authStore.token}`,
            'X-XSRF-TOKEN': authStore.getCookie('XSRF-TOKEN')
          }
        });
        console.log('Search results stored in session');
      } catch (error) {
        console.error('Error storing results in session:', error);
      }
    },
    
    updateSearchState(data) {
      if (!data) {
        console.warn('Attempted to update search state with null data');
        return;
      }

      // Validate required fields
      if (!data.query) {
        console.warn('Missing query in search data');
        return;
      }
      
      // Ensure results is always an array
      let results = Array.isArray(data.results) ? data.results : [];
      
      // Normalize the data
      results = results.map(normalizeSubredditData).filter(Boolean);
      
      // Preserve existing filters and sort if not provided in data
      const filtersToUse = data.filters || this.filters;
      const sortByToUse = data.sortBy || this.sortBy;
      
      // Update the state
      const newState = {
        query: data.query,
        results: results,
        totalResults: typeof data.total_results === 'number' ? data.total_results : results.length,
        lastSearchTimestamp: data.timestamp || Date.now(),
        sortBy: sortByToUse,
        filters: filtersToUse
      };

      console.log('Updating search state with:', {
        query: newState.query,
        resultCount: newState.results.length,
        sortBy: newState.sortBy,
        filters: newState.filters
      });

      // Update the store
      Object.assign(this, newState);
      
      try {
        // Store the entire state
        localStorage.setItem('search-store-state', JSON.stringify(newState));
        console.log('Successfully saved search state to localStorage');
      } catch (error) {
        console.warn('Failed to save search state:', error);
        
        // Try saving with reduced results
        try {
          const reducedState = {
            ...newState,
            results: results.slice(0, 50).map(r => ({
              id: r.id,
              name: r.name,
              display_name: r.display_name,
              subscribers: r.subscribers,
              active_user_count: r.active_user_count,
              created_utc: r.created_utc,
              opportunity_score: r.opportunity_score,
              topics: r.topics || [],
              content_types: r.content_types || [],
              moderation_level: r.moderation_level,
              posts_per_day: r.posts_per_day,
              engagement_rate: r.engagement_rate,
              growth_rate: r.growth_rate
            }))
          };
          localStorage.setItem('search-store-state', JSON.stringify(reducedState));
          console.log('Saved reduced search state to localStorage');
        } catch (retryError) {
          console.error('Failed to save even reduced state:', retryError);
        }
      }
    },

    clearResults() {
      Object.assign(this, getDefaultState());
      localStorage.removeItem('search-store-state');
    },

    setSortBy(sort) {
      console.log(`Setting sort criteria to: ${sort}`);
      this.sortBy = sort;
      
      // Update entire state
      const currentState = {
        query: this.query,
        results: this.results,
        totalResults: this.totalResults,
        lastSearchTimestamp: this.lastSearchTimestamp,
        sortBy: sort,
        filters: this.filters
      };
      
      try {
        localStorage.setItem('search-store-state', JSON.stringify(currentState));
        console.log('Successfully persisted sort criteria to localStorage');
      } catch (error) {
        console.error('Failed to persist sort criteria:', error);
      }
    },

    setFilters(filters) {
      console.log('Setting filters to:', filters);
      this.filters = filters;
      
      // Update entire state
      const currentState = {
        query: this.query,
        results: this.results,
        totalResults: this.totalResults,
        lastSearchTimestamp: this.lastSearchTimestamp,
        sortBy: this.sortBy,
        filters: filters
      };
      
      try {
        localStorage.setItem('search-store-state', JSON.stringify(currentState));
        console.log('Successfully persisted filters to localStorage');
      } catch (error) {
        console.error('Failed to persist filters:', error);
      }
    }
  }
}); 

// Helper function to get default filters
function getDefaultFilters() {
  return {
    minMembers: 0,
    category: '',
    activityLevel: '',
    contentTypes: {
      text: false,
      images: false,
      videos: false,
      links: false
    },
    moderationLevel: ''
  };
}

// Helper function to get default state
function getDefaultState() {
  return {
    query: '',
    results: [],
    totalResults: 0,
    loading: false,
    error: null,
    lastSearchTimestamp: null,
    sortBy: 'opportunity_score',
    filters: getDefaultFilters()
  };
}

// Helper function to determine activity level
function getActivityLevel(postsPerDay) {
  if (postsPerDay >= 50) return 'high';
  if (postsPerDay >= 10) return 'moderate';
  return 'low';
} 