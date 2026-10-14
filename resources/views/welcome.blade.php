<x-portfolio-layout :title="__('layout/header.home_title')" :description="__('layout/header.home_desc')" :styles="['resources/css/pages/index.css']">

    @php $locale = app()->getLocale(); @endphp
    @php $heroRoles = \App\Models\Setting::list('hero_roles', $locale); @endphp

    {{-- Hero — the only one with `full`: home shows no peek of the next section. --}}
    <x-portfolio.dock-hero
        full
        :eyebrow="\App\Models\Setting::text('hero_suptitle', $locale)"
        :title="\App\Models\Setting::text('hero_title', $locale)"
        :roles="$heroRoles"
        :tags="__('home/hero.hero_tags')"
        :wordmark="__('home/hero.hero_wordmark')"
        :photo="config('portfolio.hero_images.home')"
        photo-alt=""
        photo-position="50% 30%"
        :caption="__('home/hero.hero_photo_caption')"
    />

    {{-- Stats --}}
    <section id="stats" class="portfolio-section">
        <x-portfolio.section-head
            :ghost="__('home/stats.head_ghost')"
            :eyebrow="__('home/stats.head_eyebrow')"
            :title="__('home/stats.head_title')"
            :note="__('home/stats.head_note', ['url' => route('about-me')])"
        />
        <article class="stats-cards">
            @foreach ($stats as $stat)
                <x-portfolio.stats-card :value="$stat->displayValue($locale)" :text="$stat->getTranslation('text', $locale)" :value-id="$stat->value_id" />
            @endforeach
        </article>
    </section>

    {{--
        Work & Life — the uncontained timeline. No frame and no card: the two
        full-width hairlines (under the head, above the foot) and the rail down
        the track are the whole structure. Both scopes render into the same
        track and the toggle flips `hidden`, so switching costs no request.
    --}}
    @php
        $scopes = ['work' => $workExperiences, 'life' => $lifeExperiences];
        $counts = collect($scopes)->map(fn ($entries, $scope) => __(
            'home/experience.wl_count_'.$scope,
            ['count' => $entries->count(), 'span' => \App\Models\Experience::timelineSpan($entries, $locale)],
        ));
    @endphp
    <section id="work-life" class="portfolio-section wl">
        <x-portfolio.section-head
            :ghost="__('home/experience.head_ghost')"
            :eyebrow="__('home/experience.head_eyebrow')"
            :title="__('home/experience.head_title')"
            :note="__('home/experience.head_note', ['url' => route('experience')])"
        />

        <div class="wl-head">
            <div class="wl-seg" id="wl-seg" role="group" aria-label="{{ __('home/experience.wl_scope_label') }}">
                @foreach ($scopes as $scope => $entries)
                    <button
                        type="button"
                        data-scope="{{ $scope }}"
                        data-count="{{ $counts[$scope] }}"
                        aria-pressed="{{ $scope === 'work' ? 'true' : 'false' }}"
                    >{{ __('home/experience.title_'.$scope) }}</button>
                @endforeach
            </div>
        </div>

        <div class="wl-track">
            @foreach ($scopes as $scope => $entries)
                @forelse ($entries as $experience)
                    @php
                        $subtitle = $experience->getTranslation('subtitle', $locale);
                        $content = $experience->getTranslation('content', $locale);
                    @endphp
                    <article class="wl-e" data-scope="{{ $scope }}"{!! $scope === 'work' ? '' : ' hidden' !!}>
                        <div class="wl-when">{{ $experience->getTranslation('year', $locale) }}</div>
                        <div class="wl-body">
                            <div class="wl-title">
                                <h3>{{ $experience->getTranslation('title', $locale) }}</h3>
                                @if ($subtitle !== '')
                                    <span class="wl-res">{{ $subtitle }}</span>
                                @endif
                            </div>
                            @if ($content !== '')
                                {{-- A teaser, not the record: the full, formatted
                                     entry lives on /experience. --}}
                                <p>{{ Str::of(strip_tags(Str::markdown($content)))->squish()->limit(180) }}</p>
                            @endif
                            @if ($experience->badges->isNotEmpty())
                                <div class="wl-tags">
                                    @foreach ($experience->badges as $badge)
                                        <span class="wl-tag" style="--bc: {{ $badge->color }}">{{ $badge->getTranslation('name', $locale) }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </article>
                @empty
                    <p class="wl-empty" data-scope="{{ $scope }}"{!! $scope === 'work' ? '' : ' hidden' !!}>{{ __('home/experience.wl_empty') }}</p>
                @endforelse
            @endforeach
        </div>

        <div class="wl-plate">
            @foreach (['work', 'life'] as $scope)
                @php $photo = config('portfolio.section_images.work_life_'.$scope); @endphp
                @if ($photo)
                    <figure class="wl-shot" data-scope="{{ $scope }}"{!! $scope === 'work' ? '' : ' hidden' !!}>
                        <img src="{{ asset($photo) }}" alt="{{ __('home/experience.wl_photo_alt') }}">
                        <figcaption class="wl-cap">
                            <b>{{ __('home/experience.wl_photo_title') }}</b>{{ __('home/experience.wl_photo_caption') }}
                        </figcaption>
                    </figure>
                @else
                    {{-- No crop for this scope yet: the striped plate holds the
                         column open rather than collapsing the grid. --}}
                    <figure class="wl-shot wl-shot--empty" data-scope="{{ $scope }}"{!! $scope === 'work' ? '' : ' hidden' !!}>
                        <figcaption class="wl-slot">{!! __('home/experience.wl_life_slot') !!}</figcaption>
                    </figure>
                @endif
            @endforeach
        </div>

        <div class="wl-foot">
            <span class="wl-count" id="wl-count">{!! $counts['work'] !!}</span>
            <a class="wl-link" href="{{ route('experience') }}">{{ __('home/experience.wl_link') }}</a>
        </div>
    </section>

    {{-- Projects --}}
    <section id="projects" class="portfolio-section">
        <x-portfolio.section-head
            :ghost="__('home/projects.head_ghost')"
            :eyebrow="__('home/projects.head_eyebrow')"
            :title="__('home/projects.head_title')"
            :note="__('home/projects.head_note', ['url' => route('projects')])"
        />
        <div class="proj-list proj-list--compact">
            @foreach ($featuredProjects as $project)
                <x-portfolio.project-row :project="$project" :locale="$locale" :expandable="false" />
            @endforeach
        </div>
    </section>

    {{-- Tools --}}
    <section id="tools" class="portfolio-section">
        <x-portfolio.section-head
            :ghost="__('home/tools.head_ghost')"
            :eyebrow="__('home/tools.head_eyebrow')"
            :title="__('home/tools.head_title')"
            :note="__('home/tools.head_note', ['url' => route('about-me').'#about-me-tools'])"
        />
        <x-portfolio.toolbox
            :tools="$tools"
            variant="list"
        />
    </section>

    {{-- Reviews --}}
    <section id="reviews" class="portfolio-section">
        <x-portfolio.section-head
            :ghost="__('home/reviews.head_ghost')"
            :eyebrow="__('home/reviews.head_eyebrow')"
            :title="__('home/reviews.head_title')"
        />
        <div class="reviews-carousel" data-reviews-carousel>
            <div class="reviews-carousel-viewport">
                <article class="reviews-row" role="list">
                    @foreach ($reviews as $review)
                        <div class="reviews-row-card" role="listitem">
                            <div class="reviews-row-card-mark" aria-hidden="true">&ldquo;</div>
                            <p>{!! $review->highlightedText($locale) !!}</p>
                            <div class="reviews-row-card-who">
                                <div class="reviews-row-card-avatar" aria-hidden="true">{{ $review->initials() }}</div>
                                <div class="reviews-row-card-text">
                                    <p class="reviews-row-card-name">{{ $review->name }}</p>
                                    <p class="mini">{{ $review->getTranslation('position', $locale) }}</p>
                                </div>
                                @if ($review->source)
                                    <span class="reviews-row-card-badge" style="--badge-color: {{ $review->source_color ?? 'var(--c-primary)' }}">{{ $review->source }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </article>
            </div>
            <div class="reviews-carousel-dots"></div>
        </div>
    </section>

</x-portfolio-layout>
