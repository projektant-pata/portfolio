<x-portfolio-layout :title="__('layout/header.projects_title')" :description="__('layout/header.projects_desc')">

    @php $locale = app()->getLocale(); @endphp
    <section id="projects" class="portfolio-section" style="padding-top: var(--sp-section)">
        @forelse ($projects as $year => $yearProjects)
            <article class="projects-year-group">
                <h2 class="projects-year-label">{{ $year }}</h2>

                @foreach ($yearProjects as $index => $project)
                    @php
                        $isEven = $index % 2 === 0;
                    @endphp
                    <div class="projects-row {{ $isEven ? '' : 'projects-row--reverse' }}">
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
                                        <a href="{{ $link->url }}" target="_blank" rel="noopener" class="projects-row-link">
                                            @if ($link->img_url)
                                                <img src="{{ $link->img_url }}" alt="{{ is_array($link->alt) ? ($link->alt[$locale] ?? $link->alt['en'] ?? '') : '' }}">
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </article>
        @empty
            <p style="color: var(--c-muted); text-align: center; padding: 4rem 0;">
                {{ app()->getLocale() === 'cs' ? 'Žádné projekty zatím nejsou.' : 'No projects yet.' }}
            </p>
        @endforelse
    </section>

</x-portfolio-layout>
