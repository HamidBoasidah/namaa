import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// توجيه طلبات axios إلى نفس عنوان التطبيق (APP_URL) لتفادي ERR_NETWORK
const appUrl = document.head.querySelector('meta[name="app-url"]');
if (appUrl?.content) {
    window.axios.defaults.baseURL = appUrl.content.replace(/\/$/, '');
}

// إعداد CSRF token لـ axios (مطلوب فقط لطلبات axios، ليس Inertia)
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}
