@props(['project', 'locale'])

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
    </div>
</article>
