import axios from 'axios';
window.axios = axios;




// Always send cookies (helps with Safari/iOS quirks)
axios.defaults.withCredentials = true;

// Identify AJAX requests
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';



// Dynamically attach current CSRF token each request to X-CSRF-TOKEN header (mirrors cookie so tokensMatch passes)
// Do NOT set X-CSRF-TOKEN manually. Let axios send X-XSRF-TOKEN (from cookie) so Laravel can decrypt and match.
// If we set X-CSRF-TOKEN to the encrypted cookie value, middleware picks it first and cannot decrypt => 419.

// Recover gracefully from 419 (expired/invalid CSRF or session)

