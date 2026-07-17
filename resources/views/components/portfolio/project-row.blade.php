@props(['project', 'locale', 'reverse' => false])

<div class="projects-row {{ $reverse ? 'projects-row--reverse' : '' }}">
    @if ($project->img_url)
        <img src="{{ asset($project->img_url) }}" alt="{{ $project->getTranslation('header', $locale) }}">
    @else
        <div class="projects-row-img-placeholder"></div>
    @endif

    <div class="projects-row-space"></div>

    <div class="projects-row-text">
        <h3>{{ $project->getTranslation('header', $locale) }}</h3>

        @if ($project->getTranslation('description', $locale))
            <p>{{ $project->getTranslation('description', $locale) }}</p>
        @endif

        @if ($project->badges->isNotEmpty() || $project->links->isNotEmpty())
            <div class="projects-row-badges">
                @foreach ($project->badges as $badge)
                    <span class="projects-badge" style="--badge-color: {{ $badge->color }}">{{ $badge->getTranslation('name', $locale) }}</span>
                @endforeach
                @foreach ($project->links as $link)
                    <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" class="projects-row-link">
                        @if ($link->img_url)
                            <img src="{{ $link->img_url }}" alt="{{ is_array($link->alt) ? ($link->alt[$locale] ?? $link->alt['en'] ?? '') : '' }}">
                        @endif
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
