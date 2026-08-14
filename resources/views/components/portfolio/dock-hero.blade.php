@props([
    'title',
    'eyebrow' => '',
    'roles' => [],
    'tags' => [],
    'wordmark' => '',
    'dockLabel' => '',
    'dockImage' => '',
    'dockImageAlt' => '',
    'photo' => '',
    'photoAlt' => '',
    'caption' => '',
])

{{--
    Bordered three-column opener: a labelled dock column, the copy column
    carrying the outlined wordmark, and a full-bleed photo. Built generic so
    other public pages can adopt it; Experience is the first caller.

    No `portfolio-section` class on purpose — that class starts at opacity 0
    and waits for the scroll observer, which is wrong above the fold.
--}}
<section class="dock-hero">
    <div class="dock-hero-dock">
        @if ($dockLabel !== '')
            <p class="dock-hero-dock-label">{{ $dockLabel }}</p>
        @endif
        @if ($dockImage !== '')
            <img src="{{ asset($dockImage) }}" alt="{{ $dockImageAlt }}">
        @endif
    </div>

    <div class="dock-hero-copy">
        @if ($wordmark !== '')
            <p class="dock-hero-ghost" aria-hidden="true">{{ $wordmark }}</p>
        @endif

        @if ($eyebrow !== '')
            <p class="dock-hero-eyebrow">{{ $eyebrow }}</p>
        @endif

        <h1 class="dock-hero-title">{!! $title !!}</h1>

        @if (count($roles) > 1)
            <p class="dock-hero-roles"><span id="hero-rotator" data-roles='@json($roles)' aria-live="polite">{!! $roles[0] !!}</span><span class="hero-caret" aria-hidden="true"></span></p>
        @endif

        @if ($tags !== [])
            <ul class="dock-hero-tags">
                @foreach ($tags as $tag)
                    <li class="dock-hero-tag">{{ $tag }}</li>
                @endforeach
            </ul>
        @endif
    </div>

    <figure class="dock-hero-photo">
        @if ($photo !== '')
            <img src="{{ asset($photo) }}" alt="{{ $photoAlt }}">
        @endif
        @if ($caption !== '')
            <figcaption class="dock-hero-cap">{!! $caption !!}</figcaption>
        @endif
    </figure>
</section>
