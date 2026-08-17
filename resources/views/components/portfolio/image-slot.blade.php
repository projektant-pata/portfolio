@props([
    'path' => '',
    'caption' => '',
    'alt' => '',
    'height' => null,
])

{{--
    Either the real picture or the striped placeholder plate that names the
    asset and its target size. Every artwork slot on a public page goes
    through here, so dropping a file in is a data change, never a template
    edit. `height` is a CSS length applied through --image-slot-h so the
    caller decides the crop box without a wrapper element.
--}}
@if ($path !== '' && $path !== null)
    <img
        class="image-slot-img"
        src="{{ asset($path) }}"
        alt="{{ $alt }}"
        loading="lazy"
        @if ($height) style="--image-slot-h: {{ $height }}" @endif
    >
@else
    <figure class="image-slot" aria-hidden="true" @if ($height) style="--image-slot-h: {{ $height }}" @endif>
        <span>{{ $caption }}</span>
    </figure>
@endif
