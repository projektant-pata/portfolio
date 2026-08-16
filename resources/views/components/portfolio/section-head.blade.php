@props([
    'ghost' => '',
    'eyebrow',
    'title',
    'note' => '',
    'variant' => 'default',
])

{{--
    The opener shared by every page section: a decorative outlined ghost
    wordmark, the gold-rule eyebrow, the section's real h2, and an optional
    note in the right column.

    The ghost is aria-hidden — the h2 carries the accessible name. Before this
    component the outlined word *was* the heading, so a screen reader heard
    `Mu Stats` and no context. Two or three ghosts per page at most, never on
    consecutive sections; past that it stops being emphasis and becomes
    wallpaper.

    `title` and `note` render unescaped so the gold <em> and the note's link
    live in the copy, not in code. Both come from resources/lang — no user
    input reaches them.

    A `div`, not a `p`, for the ghost on purpose: `.portfolio-page p` sets its
    own size and weight and would beat the single-class ghost rule.
--}}
<div @class([
    'sechead',
    'sechead--noghost' => $ghost === '',
    'sechead--'.$variant => $variant !== 'default',
])>
    @if ($ghost !== '')
        <div class="sechead-ghost" aria-hidden="true">{{ $ghost }}</div>
    @endif

    <div class="sechead-row">
        <div>
            <p class="sechead-eyebrow">{{ $eyebrow }}</p>
            <h2>{!! $title !!}</h2>
        </div>

        @if ($note !== '')
            <p class="sechead-note">{!! $note !!}</p>
        @endif
    </div>
</div>
