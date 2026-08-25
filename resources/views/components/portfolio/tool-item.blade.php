@props(['tool'])

{{--
    One toolbox row: a 6px brand dot, the tool's name and — pushed to the far
    right — how often it is opened. The brand colour rides in a custom property
    so ten third-party palettes never touch the gold/near-black system beyond
    those six pixels.
--}}
<span class="tk-item" @if ($tool->color) style="--bc: {{ $tool->color }}" @endif>
    {{ $tool->name }}
    @if ($tool->frequency)
        <small>{{ __('pages/toolbox.frequency.'.$tool->frequency) }}</small>
    @endif
</span>
