@props([
    'href',
    'variant' => 'gold',
    'download' => false,
])

{{--
    The public pill link. `.btn-gold` / `.btn-gold-subtle` carry the same
    semantics but live in the admin-scoped dashboard stylesheet and target
    Flux buttons, which never load on a public page. Unifying the two is
    separate, later work.
--}}
<a
    href="{{ $href }}"
    class="pill-button pill-button--{{ $variant }}"
    @if ($download) download @endif
>{{ $slot }}</a>
