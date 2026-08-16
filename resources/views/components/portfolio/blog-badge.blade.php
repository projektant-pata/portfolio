@props(['badge', 'locale'])

{{-- Link-capable twin of `.exp-badge`: same pill, but it filters the listing. --}}
<a
    href="{{ route('blog', ['badge' => $badge->slug]) }}"
    class="blog-badge"
    style="--badge-color: {{ $badge->color }}"
>{{ $badge->getTranslation('name', $locale) }}</a>
