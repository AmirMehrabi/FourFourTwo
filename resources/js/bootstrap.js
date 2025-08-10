import axios from 'axios';
window.axios = axios;

// Always send cookies (helps with Safari/iOS quirks)
axios.defaults.withCredentials = true;

// Identify AJAX requests
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Use Laravel's default XSRF cookie/header naming
axios.defaults.xsrfCookieName = 'XSRF-TOKEN';
axios.defaults.xsrfHeaderName = 'X-XSRF-TOKEN';

// Ensure we have the XSRF-TOKEN cookie (try Sanctum route quietly if absent)
function haveXsrfCookie() {
    return document.cookie.split('; ').some(c => c.startsWith('XSRF-TOKEN='));
}
if (!haveXsrfCookie()) {
    axios.get('/sanctum/csrf-cookie').catch(() => {});
}

// Dynamically attach current CSRF token each request to X-CSRF-TOKEN header (mirrors cookie so tokensMatch passes)
// Do NOT set X-CSRF-TOKEN manually. Let axios send X-XSRF-TOKEN (from cookie) so Laravel can decrypt and match.
// If we set X-CSRF-TOKEN to the encrypted cookie value, middleware picks it first and cannot decrypt => 419.

// Recover gracefully from 419 (expired/invalid CSRF or session)
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
                if (typeof window !== 'undefined') window.location.reload();
            }
        }
        return Promise.reject(error);
    }
);
