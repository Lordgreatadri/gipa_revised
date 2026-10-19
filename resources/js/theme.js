/**
 * Theme handling. Light is the default; dark is opt-in and remembered in
 * localStorage. public/js/theme-boot.js applies the stored value before
 * first paint to avoid a flash; this module owns the toggle afterwards.
 */
const STORAGE_KEY = 'iomp-theme';

export function currentTheme() {
    try {
        return window.localStorage.getItem(STORAGE_KEY) === 'dark' ? 'dark' : 'light';
    } catch {
        return 'light';
    }
}

export function applyTheme(theme) {
    document.documentElement.classList.toggle('dark', theme === 'dark');
    try {
        window.localStorage.setItem(STORAGE_KEY, theme);
    } catch {
        // Storage unavailable (private mode); the toggle still works for this page.
    }
}

export function registerThemeStore(Alpine) {
    Alpine.store('theme', {
        value: currentTheme(),
        get isDark() {
            return this.value === 'dark';
        },
        toggle() {
            this.set(this.isDark ? 'light' : 'dark');
        },
        set(theme) {
            this.value = theme;
            applyTheme(theme);
        },
    });
}
