import axios from 'axios';
window.axios = axios;

// Always send cookies (helps with Safari/iOS quirks)
axios.defaults.withCredentials = true;

// Identify AJAX requests
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Use Laravel's XSRF cookie/header convention
axios.defaults.xsrfCookieName = 'XSRF-TOKEN';
axios.defaults.xsrfHeaderName = 'X-XSRF-TOKEN';

// Ensure XSRF-TOKEN cookie exists on page load
async function initializeXsrfCookie() {
    const hasXsrfCookie = document.cookie.split('; ').some(c => c.startsWith('XSRF-TOKEN='));
    if (!hasXsrfCookie) {
        try {
            // Try to get CSRF cookie via Sanctum route
            await axios.get('/sanctum/csrf-cookie');
        } catch (error) {
            // If Sanctum route doesn't exist, try a simple GET request to trigger CSRF cookie
            try {
                await axios.get(window.location.pathname, { 
                    headers: { 'Accept': 'application/json' } 
                });
            } catch (fallbackError) {
                // If all else fails, that's fine - Laravel will set XSRF cookie on first protected request
                console.warn('Could not initialize CSRF cookie:', fallbackError.message);
            }
        }
    }
}

// Initialize on page load
initializeXsrfCookie();

// 419 recovery interceptor
axios.interceptors.response.use(
    response => response,
    async error => {
        const status = error?.response?.status;
        const config = error?.config || {};
        
        // Handle CSRF token mismatch (419)
        if (status === 419 && !config._retried419) {
            config._retried419 = true;
            try {
                // Try to refresh CSRF cookie
                await axios.get('/sanctum/csrf-cookie').catch(() => {});
                // Small delay to ensure cookie is set
                await new Promise(resolve => setTimeout(resolve, 100));
                // Retry the original request
                return axios(config);
            } catch (retryError) {
                // Hard reload as last resort
                if (typeof window !== 'undefined') {
                    window.location.reload();
                }
            }
        }
        
        return Promise.reject(error);
    }
);

