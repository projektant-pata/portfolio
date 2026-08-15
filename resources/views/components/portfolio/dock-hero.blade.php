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
    'photoPosition' => '52% 22%',
    'caption' => '',
    'full' => false,
])

{{--
    Three-column opener shared by every public page: a labelled dock column,
    the copy column carrying the outlined wordmark, and a full-bleed photo.

    The section is one screen tall and only centres `.dock-hero-inner`, which
    is the band that actually holds the three columns.

    `full` (home only) drops the peek so nothing of the next section shows
    below the fold. `photoPosition` is the per-page crop lever: the default is
    tuned to the Experience photograph, the other pages dial in their own.

    No `portfolio-section` class on purpose — that class starts at opacity 0
    and waits for the scroll observer, which is wrong above the fold.
--}}
<section @class(['dock-hero', 'dock-hero--full' => $full])>
    <div class="dock-hero-inner">
        @if ($dockLabel !== '' || $dockImage !== '')
            <div class="dock-hero-dock">
                @if ($dockLabel !== '')
                    <p class="dock-hero-dock-label">{{ $dockLabel }}</p>
                @endif
                @if ($dockImage !== '')
                    <img src="{{ asset($dockImage) }}" alt="{{ $dockImageAlt }}">
                @endif
            </div>
        @endif

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

        <figure class="dock-hero-photo" style="--dock-hero-photo-pos: {{ $photoPosition }}">
            @if ($photo !== '')
                <img src="{{ asset($photo) }}" alt="{{ $photoAlt }}">
            @endif
            @if ($caption !== '')
                <figcaption class="dock-hero-cap">{!! $caption !!}</figcaption>
            @endif
        </figure>
    </div>
</section>
