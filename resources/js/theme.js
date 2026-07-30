/*
| Theme switching.
|
| The *initial* theme is applied by an inline script in the <head> (see
| layouts/app.blade.php) so it lands before first paint — importing it here
| would run after the CSS, producing a visible light-to-dark flash.
|
| This module only handles switching after boot.
*/

const STORAGE_KEY = 'theme';

/** @returns {'light'|'dark'|'system'} */
export function storedPreference() {
    const value = localStorage.getItem(STORAGE_KEY);

    return value === 'light' || value === 'dark' ? value : 'system';
}

/** Resolves 'system' to the OS setting; passes explicit choices through. */
export function resolveTheme(preference) {
    if (preference === 'system') {
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    return preference;
}

export function applyTheme(preference) {
    const root = document.documentElement;

    // Suppress transitions for the duration of the swap, otherwise every
    // themed element animates its colour at once and the change looks laggy.
    root.classList.add('no-transitions');

    root.classList.toggle('dark', resolveTheme(preference) === 'dark');

    if (preference === 'system') {
        localStorage.removeItem(STORAGE_KEY);
    } else {
        localStorage.setItem(STORAGE_KEY, preference);
    }

    // Force a reflow, then re-enable transitions on the next frame.
    window.requestAnimationFrame(() => {
        root.offsetHeight;
        root.classList.remove('no-transitions');
    });
}

// Follow the OS in real time, but only while the user is on 'system'.
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
    if (storedPreference() === 'system') {
        applyTheme('system');
    }
});

document.addEventListener('alpine:init', () => {
    Alpine.store('theme', {
        preference: storedPreference(),

        get active() {
            return resolveTheme(this.preference);
        },

        set(preference) {
            this.preference = preference;
            applyTheme(preference);
        },

        toggle() {
            this.set(this.active === 'dark' ? 'light' : 'dark');
        },
    });
});
