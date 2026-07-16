@props(['experience', 'locale'])

@php
    $link = is_array($experience->links) ? ($experience->links[0]['url'] ?? null) : null;
@endphp
<a @if ($link) href="{{ $link }}" target="_blank" rel="noopener noreferrer" @endif class="work-bot-content-row">
    @if ($experience->image_path)
        <img src="{{ asset($experience->image_path) }}" alt="{{ $experience->getTranslation('title', $locale) }}">
    @endif
    <div class="work-bot-content-row-text">
        <p class="mini">{{ $experience->getTranslation('year', $locale) }}</p>
        <h4>{{ $experience->getTranslation('title', $locale) }}</h4>
        <p>{{ $experience->getTranslation('subtitle', $locale) }}</p>
    </div>
</a>
