import { createRouter, createWebHistory } from 'vue-router';
import Login from '../components/auth/Login.vue';
import Register from '../components/auth/Register.vue';
import DashboardLayout from '../components/dashboard/DashboardLayout.vue';
import DiscoveryLayout from '../components/discovery/DiscoveryLayout.vue';
import ResourcesLayout from '../components/resources/ResourcesLayout.vue';
import SettingsLayout from '../components/settings/SettingsLayout.vue';

const routes = [
    {
        path: '/',
        redirect: '/dashboard/saved-audiences'
    },
    {
        path: '/login',
        name: 'login',
        component: Login,
        meta: { requiresGuest: true }
    },
    {
        path: '/register',
        name: 'register',
        component: Register,
        meta: { requiresGuest: true }
    },
    // Dashboard Routes
    {
        path: '/dashboard',
        component: DashboardLayout,
        meta: { requiresAuth: true },
        children: [
            {
                path: 'saved-audiences',
                name: 'dashboard-audiences',
                component: () => import('../components/dashboard/SavedAudiences.vue')
            },
            {
                path: '',
                redirect: { name: 'dashboard-audiences' }
            }
        ]
    },
    // Discovery Routes
    {
        path: '/discovery',
        component: DiscoveryLayout,
        meta: { requiresAuth: true },
        children: [
            {
                path: 'explorer',
                name: 'discovery-explorer',
                component: () => import('../components/discovery/Explorer.vue')
            },
            {
                path: 'recommended',
                name: 'discovery-recommended',
                component: () => import('../components/discovery/Recommended.vue')
            },
            {
                path: 'subreddit/:name',
                name: 'subreddit-detail',
                component: () => import('../components/discovery/SubredditDetail.vue')
            },
            {
                path: 'audience/:ids',
                name: 'audience-detail',
                component: () => import('../components/discovery/AudienceDetail.vue')
            },
            {
                path: 'compare',
                name: 'comparison-tool',
                component: () => import('../components/discovery/ComparisonTool.vue')
            },
            {
                path: '',
                redirect: { name: 'discovery-explorer' }
            }
        ]
    },
    // Resources Routes
    {
        path: '/resources',
        component: ResourcesLayout,
        meta: { requiresAuth: true },
        children: [
            {
                path: 'practices',
                name: 'resources-practices',
                component: () => import('../components/resources/Practices.vue')
            },
            {
                path: 'guidelines',
                name: 'resources-guidelines',
                component: () => import('../components/resources/Guidelines.vue')
            },
            {
                path: 'tutorials',
                name: 'resources-tutorials',
                component: () => import('../components/resources/Tutorials.vue')
            },
            {
                path: '',
                redirect: { name: 'resources-practices' }
            }
        ]
    },
    // Settings Routes
    {
        path: '/settings',
        component: SettingsLayout,
        meta: { requiresAuth: true },
        children: [
            {
                path: 'profile',
                name: 'settings-profile',
                component: () => import('../components/settings/Profile.vue')
            },
            {
                path: 'preferences',
                name: 'settings-preferences',
                component: () => import('../components/settings/Preferences.vue')
            },
            {
                path: 'api',
                name: 'settings-api',
                component: () => import('../components/settings/Api.vue')
            },
            {
                path: '',
                redirect: { name: 'settings-profile' }
            }
        ]
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition;
        } else {
            return { top: 0 };
        }
    }
});

router.beforeEach(async (to, from, next) => {
    // Import the auth store here to avoid circular dependencies
    const { useAuthStore } = await import('../stores/auth');
    const authStore = useAuthStore();
    
    // Check if the route requires authentication
    if (to.meta.requiresAuth) {
        // Check if the user is authenticated
        const isAuthenticated = authStore.isAuthenticated;
        
        if (!isAuthenticated) {
            // If not authenticated, redirect to login
            next('/login');
            return;
        }
        
        // If authenticated, verify the token is still valid
        try {
            await authStore.checkAuth();
            next();
        } catch (error) {
            // If token is invalid, redirect to login
            next('/login');
        }
    } 
    // Check if the route requires guest access
    else if (to.meta.requiresGuest) {
        // If authenticated, redirect to dashboard
        if (authStore.isAuthenticated) {
            next('/dashboard/saved-audiences');
            return;
        }
        next();
    } 
    // For all other routes, just proceed
    else {
        next();
    }
});

export default router; 