@props(['project', 'locale', 'expandable' => true])

@php
    $kindLabel = __('pages/projects.kind_'.$project->kind);
    $railKind = $project->kind === 'client' && $project->client
        ? $kindLabel.' · '.$project->client
        : $kindLabel;
    $stack = $project->badges->pluck('slug')->values();
@endphp

<article class="proj-item" data-kind="{{ $project->kind }}" data-stack='@json($stack)'>
    <div class="proj-rail">
        <p class="proj-kind">{{ $railKind }}</p>
        @if ($project->status)
            <p class="proj-status" data-state="{{ $project->status }}">{{ __('pages/projects.status_'.$project->status) }}</p>
        @endif
    </div>

    <div class="proj-shot">
        @if ($project->img_url)
            <img src="{{ asset($project->img_url) }}" alt="{{ $project->getTranslation('header', $locale) }}" loading="lazy">
        @endif
    </div>

    <div class="proj-body">
        <h3 class="proj-name">{{ $project->getTranslation('header', $locale) }}</h3>

        @if ($project->getTranslation('description', $locale))
            <p class="proj-desc">{{ $project->getTranslation('description', $locale) }}</p>
        @endif

        @if ($project->badges->isNotEmpty())
            <div class="proj-chips">
                @foreach ($project->badges as $badge)
                    <span class="proj-chip" style="--bc: {{ $badge->color }}">{{ $badge->getTranslation('name', $locale) }}</span>
                @endforeach
            </div>
        @endif

        @if ($expandable)
            <div class="proj-more" id="proj-more-{{ $project->slug }}">
                <dl class="proj-facts">
                    @if ($project->getTranslation('role', $locale))
                        <div class="proj-fact">
                            <dt>{{ __('pages/projects.fact_role') }}</dt>
                            <dd>{{ $project->getTranslation('role', $locale) }}</dd>
                        </div>
                    @endif

                    <div class="proj-fact">
                        @if ($project->kind === 'client' && $project->client)
                            <dt>{{ __('pages/projects.fact_client') }}</dt>
                            <dd>{{ $project->client }}</dd>
                        @else
                            <dt>{{ __('pages/projects.fact_kind') }}</dt>
                            <dd>{{ $kindLabel }}</dd>
                        @endif
                    </div>

                    @if ($project->badges->isNotEmpty())
                        <div class="proj-fact">
                            <dt>{{ __('pages/projects.fact_stack') }}</dt>
                            <dd>{{ $project->badges->map(fn ($b) => $b->getTranslation('name', $locale))->implode(', ') }}</dd>
                        </div>
                    @endif
                </dl>

                <div class="proj-links">
                    @forelse ($project->links as $link)
                        <a class="proj-link" href="{{ $link->url }}" target="_blank" rel="noopener noreferrer">
                            {{ __('pages/projects.link_'.$link->kind) }}
                        </a>
                    @empty
                        <span class="proj-link proj-link--none">{{ __('pages/projects.link_none') }}</span>
                    @endforelse
                </div>
            </div>
        @endif
    </div>

    @if ($expandable)
        <div class="proj-act">
            <button
                type="button"
                class="proj-toggle"
                aria-expanded="false"
                aria-controls="proj-more-{{ $project->slug }}"
                data-label-open="{{ __('pages/projects.details') }}"
                data-label-close="{{ __('pages/projects.close') }}"
            >{{ __('pages/projects.details') }}</button>
        </div>
    @endif
</article>
