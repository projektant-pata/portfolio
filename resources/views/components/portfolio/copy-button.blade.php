@props([
    'value',
    'label' => null,
    'done' => null,
    'variant' => 'ghost',
    'size' => 'sm',
])

{{--
    Copy-to-clipboard pill. Its own component rather than Alpine attributes on
    <x-portfolio.pill-button>: Blade does not compile @js inside a component
    tag's attributes, so the JSON-encoded strings have to live in a plain
    element like this one.

    The label swaps for 1.6s and swaps back — no toast, because the button is
    already where the eye is.
--}}
@php
    $label ??= __('pages/contact.copy');
    $done ??= __('pages/contact.copied');
@endphp

<button
    type="button"
    {{ $attributes->class('pill-button pill-button--'.$variant.($size === 'sm' ? ' pill-button--sm' : '')) }}
    x-data="{ copied: false }"
    x-on:click="navigator.clipboard?.writeText(@js($value))?.catch(() => {}); copied = true; setTimeout(() => copied = false, 1600)"
    x-text="copied ? @js($done) : @js($label)"
>{{ $label }}</button>
