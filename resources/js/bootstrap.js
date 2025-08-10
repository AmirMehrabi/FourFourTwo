import axios from 'axios';
window.axios = axios;

// Always send cookies (helps with Safari/iOS quirks)
axios.defaults.withCredentials = true;

// Identify AJAX requests
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Use Laravel's default XSRF cookie/header auto mechanism
axios.defaults.xsrfCookieName = 'XSRF-TOKEN';
axios.defaults.xsrfHeaderName = 'X-XSRF-TOKEN';

// IMPORTANT: Do NOT also force a static X-CSRF-TOKEN header from initial HTML.
// That can become stale after session rotation and cause 419 errors.
// If you previously set it, removing it lets axios always reflect current cookie value.

// If the XSRF-TOKEN cookie is missing (first load or after expiration), try to obtain it (Sanctum route); ignore failures.
function ensureXsrfCookie() {
    if (!document.cookie.split('; ').some(c => c.startsWith('XSRF-TOKEN='))) {
        axios.get('/sanctum/csrf-cookie').catch(() => {});
    }
}
ensureXsrfCookie();

// 419 auto-recovery: fetch fresh CSRF cookie once, then retry the original request.
axios.interceptors.response.use(
    r => r,
    async error => {
        const status = error?.response?.status;
        const config = error?.config || {};
        if (status === 419 && !config._retriedAfter419) {
            config._retriedAfter419 = true;
            try {
                await axios.get('/sanctum/csrf-cookie');
                return axios(config);
            } catch (_) {
                // Hard reload as last resort
                if (typeof window !== 'undefined') window.location.reload();
            }
        }
        return Promise.reject(error);
    }
);
