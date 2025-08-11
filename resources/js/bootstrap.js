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
            // Try to get CSRF cookie via a simple GET request to a route that will set the cookie
            await axios.get('/debug-csrf', { 
                headers: { 'Accept': 'application/json' } 
            });
        } catch (error) {
            // If that fails, try sanctum route as fallback
            try {
                await axios.get('/sanctum/csrf-cookie');
            } catch (sanctumError) {
                // If all else fails, that's fine - Laravel will set XSRF cookie on first protected request
                console.warn('Could not initialize CSRF cookie, will be set on first request');
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
                // Clear existing XSRF-TOKEN cookie
                document.cookie = 'XSRF-TOKEN=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
                
                // Try to refresh CSRF cookie
                await axios.get('/debug-csrf', { 
                    headers: { 'Accept': 'application/json' } 
                }).catch(async () => {
                    // Fallback to sanctum route
                    await axios.get('/sanctum/csrf-cookie').catch(() => {});
                });
                
                // Small delay to ensure cookie is set
                await new Promise(resolve => setTimeout(resolve, 200));
                
                // Retry the original request
                return axios(config);
            } catch (retryError) {
                console.error('CSRF token refresh failed:', retryError);
                // Hard reload as last resort
                if (typeof window !== 'undefined') {
                    window.location.reload();
                }
            }
        }
        
        return Promise.reject(error);
    }
);

