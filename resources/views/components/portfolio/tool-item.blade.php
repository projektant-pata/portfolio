@props(['tool'])

{{--
    One toolbox atom: a brand dot and the tool's name. Nothing rides along —
    the state word is a column of its own in the about-me rows, and the home
    cells deliberately carry no state at all.

    The brand colour rides in a custom property so seventeen third-party
    palettes never touch the gold/near-black system beyond those six pixels.
--}}
<span class="tk-item" @if ($tool->color) style="--bc: {{ $tool->color }}" @endif>{{ $tool->name }}</span>
