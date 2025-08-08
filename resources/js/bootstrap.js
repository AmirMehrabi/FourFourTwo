import axios from 'axios';
window.axios = axios;

// Always send cookies (helps with Safari/iOS quirks)
window.axios.defaults.withCredentials = true;

// Identify AJAX requests
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Prefer Laravel's XSRF cookie/header convention; axios will auto-attach the header if the cookie exists
window.axios.defaults.xsrfCookieName = 'XSRF-TOKEN';
window.axios.defaults.xsrfHeaderName = 'X-XSRF-TOKEN';

// Fallback to meta tag (works without Sanctum too)
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

// Recover gracefully from 419 (expired/invalid CSRF or session) particularly on iOS/Safari
window.axios.interceptors.response.use(
    (response) => response,
    async (error) => {
        const status = error?.response?.status;
        const config = error?.config || {};

        if (status === 419 && !config._retry419) {
            try {
                config._retry419 = true;
                // Try to refresh CSRF cookie if Sanctum is available (no-op if route missing)
                try {
                    await window.axios.get('/sanctum/csrf-cookie');
                } catch (_) {}
                // Retry original request once
                return window.axios(config);
            } catch (_) {
                // Fall through to hard reload
            }
            if (typeof window !== 'undefined') {
                window.location.reload();
            }
        }
        return Promise.reject(error);
    }
);
