@props(['title' => config('app.name'), 'description' => '', 'styles' => []])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title === 'projektant-pata' ? $title : $title.' | projektant-pata' }}</title>
    @if($description)
        <meta name="description" content="{{ $description }}">
    @endif

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,200;14..32,400;14..32,600;14..32,700&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">

    @include('partials.theme')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @if($styles)
        @vite($styles)
    @endif

    <script>const locale = "{{ App::getLocale() }}";</script>
</head>
<body class="portfolio-page">

    <div id="scroll-progress"></div>

    {{--
        .portfolio-wrapper   — centered, max-width capped at --layout-max
        .portfolio-sidebar   — sticky left column, holds phone nav
        .portfolio-col       — right column: main content
        The footer lives OUTSIDE the wrapper so it spans the full viewport
        width as a full-bleed band; its inner content re-aligns to the page.
    --}}
    <div class="portfolio-wrapper">

        <aside class="portfolio-sidebar">
            <x-mobile-nav />
        </aside>

        <div class="portfolio-col">
            <main class="portfolio-main">
                {{ $slot }}
            </main>
        </div>

    </div>

    <x-portfolio-footer />

    <button id="scroll-top" type="button" aria-label="{{ __('layout/footer.scroll_top') }}" hidden>↑</button>

</body>
</html>
