/**
 * Apply the remembered theme before first paint. Light is the default.
 * Kept as a static same-origin file so Content-Security-Policy can allow
 * it with script-src 'self' and never needs 'unsafe-inline'.
 */
(function () {
    try {
        if (window.localStorage.getItem('iomp-theme') === 'dark') {
            document.documentElement.classList.add('dark');
        }
    } catch (e) {}
})();
