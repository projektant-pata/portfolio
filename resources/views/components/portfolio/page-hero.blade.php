@props([
    'title',
    'eyebrow' => '',
    'roles' => [],
    'image' => '',
    'imageAlt' => '',
    'full' => false,
])

{{--
    Shared opener for every public page. `--full` (home only) fills the
    viewport; everything else stops one --hero-peek short of the fold so
    the next section's watermark invites the scroll.

    The rotator keeps the `hero-rotator` id the JS binds to — one hero per
    page keeps that unique.
--}}
<section class="hero-page {{ $full ? 'hero-page--full' : '' }} portfolio-section">
    <article class="hero-page-text">
        @if ($eyebrow !== '')
            <p class="hero-suptitle">{{ $eyebrow }}</p>
        @endif
        <h1>{!! $title !!}</h1>
        @if (count($roles) > 1)
            <h4 class="underh1">
                <span id="hero-rotator" data-roles='@json($roles)' aria-live="polite">{!! $roles[0] !!}</span><span class="hero-caret" aria-hidden="true"></span>
            </h4>
        @endif
    </article>
    @if ($image !== '')
        <article class="hero-page-image">
            <img src="{{ asset($image) }}" alt="{{ $imageAlt }}">
        </article>
    @endif
</section>
