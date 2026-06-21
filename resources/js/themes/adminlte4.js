import '@popperjs/core';
import 'bootstrap';
import 'admin-lte/dist/js/adminlte.min.js';

const loginUrl = document.querySelector('meta[name="login-url"]')?.content;

if (loginUrl) {
    const originalFetch = window.fetch.bind(window);

    window.fetch = async (...args) => {
        const response = await originalFetch(...args);

        if (response.status === 401) {
            const data = await response.clone().json().catch(() => null);

            if (data?.redirect) {
                window.location.href = data.redirect;
            } else {
                window.location.href = loginUrl;
            }
        }

        return response;
    };
}
