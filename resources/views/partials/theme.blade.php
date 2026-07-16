{{--
    Single theme switch for the whole app (public + admin + Flux).

    The `theme` cookie holds 'dark' | 'light' | 'system'; dark is the
    default when no cookie is set. This resolves it to the `.dark`
    class on <html> before first paint, so there is no flash of the
    wrong theme. Keep in sync with applyTheme() in resources/js/app.js.
--}}
<script>
    (function () {
        var match = document.cookie.match(/(?:^|;\s*)theme=([^;]*)/);
        var preference = match ? decodeURIComponent(match[1]) : 'dark';
        var isDark = preference === 'dark'
            || (preference === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);

        document.documentElement.classList.toggle('dark', isDark);
    })();
</script>
