<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

{{-- Apply saved theme before first paint to prevent dark→light flash --}}
<script>
    (function () {
        function getCookie(name) {
            var cname = name + '=';
            var cookies = decodeURIComponent(document.cookie).split(';');
            for (var i = 0; i < cookies.length; i++) {
                var c = cookies[i].trim();
                if (c.indexOf(cname) === 0) return c.substring(cname.length);
            }
            return '';
        }
        if (getCookie('theme') === 'light') {
            document.documentElement.classList.add('light-theme');
        }
    })();
</script>

@vite(['resources/css/app.css', 'resources/css/pages/dashboard.css', 'resources/js/app.js'])
@fluxAppearance
