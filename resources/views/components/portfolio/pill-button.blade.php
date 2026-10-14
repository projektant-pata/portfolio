@props([
    'href' => null,
    'variant' => 'gold',
    'size' => null,
    'download' => false,
    'type' => 'button',
])

{{--
    The public pill. Renders an `<a>` when it navigates and a `<button>` when
    it acts on the page (copy, submit, fill a field) — a link with no
    destination is not a control a keyboard can reach sensibly.

    `.btn-gold` / `.btn-gold-subtle` carry the same semantics but live in the
    admin-scoped dashboard stylesheet and target Flux buttons, which never
    load on a public page. Unifying the two is separate, later work.
--}}
@php
    $classes = 'pill-button pill-button--'.$variant.($size === 'sm' ? ' pill-button--sm' : '');
@endphp

@if ($href !== null)
    <a
        href="{{ $href }}"
        {{ $attributes->class($classes) }}
        @if ($download) download @endif
    >{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif
