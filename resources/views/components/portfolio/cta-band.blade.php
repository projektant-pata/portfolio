@props([
    'title' => null,
    'sub' => null,
    'available' => null,
    'fine' => null,
])

@php
    $locale = app()->getLocale();

    /* Route name → copy. `blog.show` reads `blog_show`; anything without an
       entry of its own falls back to `default`. */
    $key = str_replace('.', '_', request()->route()?->getName() ?? 'default');
    $copy = __('layout/cta.pages.'.$key);

    if (! is_array($copy)) {
        $copy = __('layout/cta.pages.default');
    }

    $bandTitle = $title ?? $copy['title'];
    $bandSub = $sub ?? $copy['sub'];

    /* The site keeps one availability flag, set on /about-me and reused
       wherever the band lands. */
    $bandAvailable = $available ?? \App\Models\Setting::text('about_available', $locale);
    $bandFine = $fine ?? __('layout/cta.fine');
@endphp

{{--
    The closing band: the page's last surface and the only tinted one. It
    started as /about-me's seventh section and now runs under every public
    page from the layout, with per-route copy from resources/lang/*/layout/cta.php.

    `title` renders unescaped so the gold <em> lives in the copy. On /about-me
    the title and sub come from Settings instead, which is why both are props.
--}}
<div class="cta-band">
    <div>
        @if ($bandAvailable !== '')
            <p class="cta-band-available">
                <x-portfolio.availability-dot />{{ $bandAvailable }}
            </p>
        @endif
        <h2>{!! $bandTitle !!}</h2>
        @if ($bandSub !== '')
            <p class="cta-band-sub">{{ $bandSub }}</p>
        @endif
    </div>
    <div class="cta-band-actions">
        <div class="cta-band-row">
            <x-portfolio.pill-button :href="config('portfolio.social.email')">{{ __('layout/cta.email') }}</x-portfolio.pill-button>
            @if (config('portfolio.cv_path'))
                <x-portfolio.pill-button :href="asset(config('portfolio.cv_path'))" variant="ghost" :download="true">{{ __('layout/cta.cv') }}</x-portfolio.pill-button>
            @endif
        </div>
        <div class="cta-band-row">
            <x-portfolio.pill-button :href="config('portfolio.social.github')" variant="ghost">GitHub</x-portfolio.pill-button>
            <x-portfolio.pill-button :href="config('portfolio.social.linkedin')" variant="ghost">LinkedIn</x-portfolio.pill-button>
        </div>
        @if ($bandFine !== '')
            <p class="cta-band-fine">{{ $bandFine }}</p>
        @endif
    </div>
</div>
