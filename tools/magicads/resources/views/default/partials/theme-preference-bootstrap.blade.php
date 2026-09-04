{{--
    Theme-preference bootstrap — runs inline in <head> before any paint so
    the `.dark` class on <html> is already correct when the first frame
    renders. Prevents a flash of incorrect theme (FOUC).

    Validates:
      - Requirement 2.6  (stored preference applied before first paint)
      - Requirement 15.1 (.dark on <html> is the single source of truth)
      - Requirement 15.2 (no stored preference → honor prefers-color-scheme)

    Property 2 asserts the full behavior matrix of
      localStorage.theme ∈ {null, 'light', 'dark', 'system', unknown}
      × prefersDark ∈ {true, false}.
--}}
<script>
    (function () {
        try {
            var stored = null;
            try {
                stored = window.localStorage.getItem('theme');
            } catch (_storageError) {
                stored = null;
            }

            var prefersDark = typeof window.matchMedia === 'function'
                && window.matchMedia('(prefers-color-scheme: dark)').matches;

            var shouldBeDark = stored === 'dark'
                || (stored !== 'light' && prefersDark);

            document.documentElement.classList.toggle('dark', shouldBeDark);
            document.documentElement.dataset.themePreference = stored || 'system';
        } catch (_e) {
            /* Storage or matchMedia unavailable — fall back to light. */
        }
    })();
</script>
