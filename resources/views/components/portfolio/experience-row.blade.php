@props(['experience', 'locale'])

@php
    $link = is_array($experience->links) ? ($experience->links[0]['url'] ?? null) : null;
@endphp
<a @if ($link) href="{{ $link }}" target="_blank" rel="noopener noreferrer" @endif class="work-bot-content-row">
    @if ($experience->image_path)
        <img src="{{ asset($experience->image_path) }}" alt="{{ $experience->getTranslation('title', $locale) }}">
    @endif
    <div class="work-bot-content-row-text">
        <div class="work-bot-content-row-meta">
            <p class="mini">{{ $experience->getTranslation('year', $locale) }}</p>
            @if ($experience->badges->isNotEmpty())
                <div class="work-bot-content-row-badges">
                    @foreach ($experience->badges as $badge)
                        <span class="work-bot-badge" style="--badge-color: {{ $badge->color }}">{{ $badge->getTranslation('name', $locale) }}</span>
                    @endforeach
                </div>
            @endif
        </div>
        <h4>{{ $experience->getTranslation('title', $locale) }}</h4>
        <p>{{ $experience->getTranslation('subtitle', $locale) }}</p>
    </div>
</a>
