/**
 * Landing-page client-side enhancements.
 *
 * Four independent helpers, each guarded by a presence check so the module
 * is safe to load globally. No external imports — keep this file small
 * (target < 5KB gzip).
 *
 *   1. Theme-preference controller  — [data-theme-toggle]
 *   2. Tablist keyboard handler     — [role="tablist"]
 *   3. Nav scroll-state toggler     — nav[data-nav-scroll-watch] or [data-landing-nav]
 *   4. Mobile nav toggle            — [data-mobile-nav-toggle]
 */

const THEME_STORAGE_KEY = 'theme';
const THEME_STATES = ['light', 'dark', 'system'];

// ---------------------------------------------------------------------------
// localStorage helpers — isolated so browsers that block storage never throw.
// ---------------------------------------------------------------------------
function readStoredTheme() {
    try {
        return window.localStorage.getItem(THEME_STORAGE_KEY);
    } catch (_e) {
        return null;
    }
}

function writeStoredTheme(value) {
    try {
        if (value === null) {
            window.localStorage.removeItem(THEME_STORAGE_KEY);
        } else {
            window.localStorage.setItem(THEME_STORAGE_KEY, value);
        }
    } catch (_e) {
        /* storage unavailable — silently fall through */
    }
}

function prefersDark() {
    return typeof window.matchMedia === 'function'
        && window.matchMedia('(prefers-color-scheme: dark)').matches;
}

function resolveEffectiveTheme(pref) {
    if (pref === 'dark') return 'dark';
    if (pref === 'light') return 'light';
    return prefersDark() ? 'dark' : 'light';
}

function normalizePreference(value) {
    return THEME_STATES.indexOf(value) === -1 ? 'system' : value;
}

// ---------------------------------------------------------------------------
// 1. Theme-preference controller
// ---------------------------------------------------------------------------
function initThemePreference() {
    const toggles = document.querySelectorAll('[data-theme-toggle]');
    if (toggles.length === 0) return;

    const root = document.documentElement;
    let mediaQuery = null;
    let mediaListener = null;

    function applyPreference(pref) {
        const effective = resolveEffectiveTheme(pref);
        root.classList.toggle('dark', effective === 'dark');
        root.dataset.themePreference = pref;

        toggles.forEach((btn) => {
            const label = btn.getAttribute('data-theme-label-' + pref)
                || 'Toggle theme (current: ' + pref + ')';
            btn.setAttribute('aria-label', label);
            btn.setAttribute('data-theme-state', pref);
        });

        // Subscribe to system changes only while in 'system' mode.
        if (pref === 'system' && typeof window.matchMedia === 'function') {
            if (!mediaQuery) {
                mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
                mediaListener = () => {
                    root.classList.toggle('dark', mediaQuery.matches);
                };
                if (typeof mediaQuery.addEventListener === 'function') {
                    mediaQuery.addEventListener('change', mediaListener);
                } else if (typeof mediaQuery.addListener === 'function') {
                    mediaQuery.addListener(mediaListener);
                }
            }
        } else if (mediaQuery && mediaListener) {
            if (typeof mediaQuery.removeEventListener === 'function') {
                mediaQuery.removeEventListener('change', mediaListener);
            } else if (typeof mediaQuery.removeListener === 'function') {
                mediaQuery.removeListener(mediaListener);
            }
            mediaQuery = null;
            mediaListener = null;
        }
    }

    function cyclePreference(current) {
        const idx = THEME_STATES.indexOf(current);
        return THEME_STATES[(idx + 1) % THEME_STATES.length];
    }

    const initial = normalizePreference(readStoredTheme() || root.dataset.themePreference);
    applyPreference(initial);

    toggles.forEach((btn) => {
        btn.addEventListener('click', () => {
            const current = normalizePreference(root.dataset.themePreference);
            const next = cyclePreference(current);
            writeStoredTheme(next);
            applyPreference(next);
        });
    });
}

// ---------------------------------------------------------------------------
// 2. Tablist keyboard handler — WAI-ARIA tabs pattern w/ roving tabindex
// ---------------------------------------------------------------------------
function initTablists() {
    const tablists = document.querySelectorAll('[role="tablist"]');
    if (tablists.length === 0) return;

    tablists.forEach((tablist) => {
        const tabs = Array.from(tablist.querySelectorAll('[role="tab"]'));
        if (tabs.length === 0) return;

        function panelFor(tab) {
            const id = tab.getAttribute('aria-controls');
            return id ? document.getElementById(id) : null;
        }

        function activate(index, { focus = true } = {}) {
            const bounded = ((index % tabs.length) + tabs.length) % tabs.length;
            tabs.forEach((tab, i) => {
                const isActive = i === bounded;
                tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
                tab.setAttribute('tabindex', isActive ? '0' : '-1');

                const panel = panelFor(tab);
                if (!panel) return;
                if (isActive) {
                    panel.removeAttribute('hidden');
                    panel.setAttribute('aria-hidden', 'false');
                } else {
                    panel.setAttribute('hidden', '');
                    panel.setAttribute('aria-hidden', 'true');
                }
            });

            if (focus) {
                tabs[bounded].focus();
            }
        }

        function currentIndex() {
            const active = tabs.findIndex(
                (t) => t.getAttribute('aria-selected') === 'true'
            );
            return active === -1 ? 0 : active;
        }

        // Initialise state from the DOM (first tab active if nothing set).
        activate(currentIndex(), { focus: false });

        tabs.forEach((tab, i) => {
            tab.addEventListener('click', () => activate(i));

            tab.addEventListener('keydown', (event) => {
                const key = event.key;
                let handled = false;

                if (key === 'ArrowRight' || key === 'ArrowDown') {
                    activate(currentIndex() + 1);
                    handled = true;
                } else if (key === 'ArrowLeft' || key === 'ArrowUp') {
                    activate(currentIndex() - 1);
                    handled = true;
                } else if (key === 'Home') {
                    activate(0);
                    handled = true;
                } else if (key === 'End') {
                    activate(tabs.length - 1);
                    handled = true;
                } else if (key === ' ' || key === 'Enter' || key === 'Spacebar') {
                    activate(i);
                    handled = true;
                }

                if (handled) {
                    event.preventDefault();
                    event.stopPropagation();
                }
            });
        });
    });
}

// ---------------------------------------------------------------------------
// 3. Nav scroll-state toggler
// ---------------------------------------------------------------------------
function initNavScrollWatcher() {
    let navs = document.querySelectorAll('nav[data-nav-scroll-watch]');
    if (navs.length === 0) {
        const fallback = document.querySelector('[data-landing-nav]');
        if (!fallback) return;
        navs = [fallback];
    }

    function update() {
        const scrolled = window.scrollY > 32;
        navs.forEach((nav) => {
            if (scrolled) {
                nav.setAttribute('data-scrolled', 'true');
            } else {
                nav.removeAttribute('data-scrolled');
            }
        });
    }

    update();
    window.addEventListener('scroll', update, { passive: true });
}

// ---------------------------------------------------------------------------
// 4. Mobile nav toggle
// ---------------------------------------------------------------------------
function initMobileNavToggle() {
    const toggles = document.querySelectorAll('[data-mobile-nav-toggle]');
    if (toggles.length === 0) return;

    toggles.forEach((btn) => {
        const targetId = btn.getAttribute('aria-controls');
        const target = targetId ? document.getElementById(targetId) : null;
        if (!target) return;

        btn.addEventListener('click', () => {
            const expanded = btn.getAttribute('aria-expanded') === 'true';
            const next = !expanded;
            btn.setAttribute('aria-expanded', next ? 'true' : 'false');
            if (next) {
                target.removeAttribute('hidden');
            } else {
                target.setAttribute('hidden', '');
            }
        });
    });
}

// ---------------------------------------------------------------------------
// Public API — IIFE-style orchestrator; no-op when DOM is not available.
// ---------------------------------------------------------------------------
export function initLanding() {
    if (typeof document === 'undefined') return;

    initThemePreference();
    initTablists();
    initNavScrollWatcher();
    initMobileNavToggle();
}

export default initLanding;
