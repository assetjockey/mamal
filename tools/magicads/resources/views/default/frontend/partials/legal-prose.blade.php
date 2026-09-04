{{--
    Prose styling for admin-managed legal page content (.pg-prose).
    Matches the editorial light theme used across the marketing pages so
    custom Privacy / Terms HTML renders consistently with the built-in copy.
--}}
<style>
    .pg-prose { color: rgba(0,0,0,0.70); font-size: 14px; line-height: 1.75; }
    .pg-prose > :first-child { margin-top: 0; }
    .pg-prose h1 { font-size: 28px; font-weight: 800; letter-spacing: -0.02em; color: #000; margin: 1.4em 0 0.5em; line-height: 1.15; }
    .pg-prose h2 { font-size: 20px; font-weight: 700; color: #000; margin: 1.6em 0 0.5em; line-height: 1.25; padding-top: 1.6em; border-top: 1px solid var(--l-hairline, rgba(0,0,0,0.08)); }
    .pg-prose h2:first-of-type { border-top: 0; padding-top: 0; }
    .pg-prose h3 { font-size: 15px; font-weight: 600; color: #000; margin: 1.4em 0 0.4em; }
    .pg-prose p  { margin: 0 0 1em; }
    .pg-prose ul, .pg-prose ol { margin: 0 0 1.2em; padding-left: 1.4em; }
    .pg-prose ul { list-style: disc; }
    .pg-prose ol { list-style: decimal; }
    .pg-prose li { margin-bottom: 0.4em; }
    .pg-prose a  { color: #4F46E5; text-decoration: underline; text-underline-offset: 3px; }
    .pg-prose a:hover { color: #312E81; }
    .pg-prose strong { color: #000; font-weight: 700; }
    .pg-prose blockquote { border-left: 3px solid #4F46E5; padding: 0.2em 0 0.2em 1.2em; color: #374151; font-style: italic; margin: 1.4em 0; }
    .pg-prose hr { border: 0; border-top: 1px solid rgba(0,0,0,0.08); margin: 2em 0; }
    .pg-prose code { background: rgba(79,70,229,0.08); color: #4338CA; padding: 0.15em 0.45em; border-radius: 4px; font-size: 0.92em; }
    .pg-prose table { width: 100%; border-collapse: collapse; margin: 1.4em 0; font-size: 13.5px; }
    .pg-prose th, .pg-prose td { border: 1px solid rgba(0,0,0,0.1); padding: 0.5em 0.75em; text-align: left; }
    .pg-prose th { background: rgba(0,0,0,0.03); font-weight: 600; color: #000; }
</style>
