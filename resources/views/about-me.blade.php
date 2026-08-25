<x-portfolio-layout :title="__('layout/header.about_title')" :description="__('layout/header.about_desc')" :styles="['resources/css/pages/about-me.css']">

    @php
        $locale = app()->getLocale();
        $available = \App\Models\Setting::text('about_available', $locale);
        $ctaTitle = \App\Models\Setting::text('about_cta_title', $locale);
        $ctaSub = \App\Models\Setting::text('about_cta_sub', $locale);

        /* The hero leaves the first section's top edge inside the first
           viewport, so whichever section renders first must already be painted
           — it never fades up. Which one that is depends on the data. */
        $firstSection = collect([
            'stats' => $stats->isNotEmpty(),
            'path' => $pathNodes->isNotEmpty(),
            'tools' => $tools->isNotEmpty() || $toolChips->isNotEmpty(),
            'interests' => $interests->isNotEmpty(),
            'showups' => $showUps->isNotEmpty(),
            'reviews' => $reviews->isNotEmpty(),
            'contact' => $ctaTitle !== '',
        ])->filter()->keys()->first();
    @endphp

    {{-- Hero --}}
    <x-portfolio.dock-hero
        :eyebrow="\App\Models\Setting::text('about_hero_suptitle', $locale)"
        :title="\App\Models\Setting::text('about_hero_title', $locale)"
        :roles="\App\Models\Setting::list('about_hero_roles', $locale)"
        :tags="__('pages/about-me.hero_tags')"
        :wordmark="__('pages/about-me.hero_wordmark')"
        :photo="config('portfolio.hero_images.about')"
        photo-alt=""
        photo-position="50% 25%"
    />

    {{-- 1 · Stats bento — the page's single ghost wordmark --}}
    @if ($stats->isNotEmpty())
        <section id="about-me-stats" @class(['portfolio-section', 'portfolio-section--no-reveal' => $firstSection === 'stats'])>
            <x-portfolio.section-head
                :ghost="__('pages/about-me.stats_head_ghost')"
                :eyebrow="__('pages/about-me.stats_head_eyebrow')"
                :title="\App\Models\Setting::text('stats_title', $locale)"
                :note="__('pages/about-me.stats_head_note')"
            />
            <div class="about-me-bento">
                @foreach ($stats as $stat)
                    <div @class([
                        'about-me-tile',
                        'about-me-tile--hero' => $stat->tier === 'hero',
                        'about-me-tile--soft' => $stat->tier === 'soft',
                    ])>
                        <span class="about-me-tile-value" @if ($stat->value_id) id="{{ $stat->value_id }}" @endif>{{ $stat->displayValue($locale) }}</span>
                        <span class="about-me-tile-label">{{ $stat->getTranslation('text', $locale) }}</span>
                        @if ($stat->tier === 'hero' && $stat->getTranslation('note', $locale) !== '')
                            <p>{{ $stat->getTranslation('note', $locale) }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- 2 · The short version --}}
    @if ($pathNodes->isNotEmpty())
        <section id="about-me-path" @class(['portfolio-section', 'portfolio-section--no-reveal' => $firstSection === 'path'])>
            <x-portfolio.section-head
                :eyebrow="__('pages/about-me.path_head_eyebrow')"
                :title="__('pages/about-me.path_head_title')"
                :note="__('pages/about-me.path_head_note')"
            />
            <div class="about-me-path">
                @foreach ($pathNodes as $node)
                    <div @class(['about-me-node', 'about-me-node--now' => $node->is_now])>
                        <div class="about-me-node-year">{{ $node->getTranslation('year', $locale) }}</div>
                        <div class="about-me-node-body">
                            <h3>{{ $node->getTranslation('title', $locale) }}</h3>
                            <p>{{ $node->getTranslation('body', $locale) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- 3 · Toolbox — the home page's atom, grouped into columns --}}
    @if ($tools->isNotEmpty() || $toolChips->isNotEmpty())
        <section id="about-me-tools" @class(['portfolio-section', 'portfolio-section--no-reveal' => $firstSection === 'tools'])>
            <x-portfolio.section-head
                :eyebrow="__('pages/about-me.tools_head_eyebrow')"
                :title="__('pages/about-me.tools_head_title')"
                :note="__('pages/about-me.tools_head_note')"
            />
            <x-portfolio.toolbox
                :tools="$tools"
                variant="cols"
                :chips="$toolChips"
                :chips-label="__('pages/toolbox.learning_label')"
            />
        </section>
    @endif

    {{-- 4 · Off the screen --}}
    @if ($interests->isNotEmpty())
        <section id="about-me-interests" @class(['portfolio-section', 'portfolio-section--no-reveal' => $firstSection === 'interests'])>
            <x-portfolio.section-head
                :eyebrow="__('pages/about-me.interests_head_eyebrow')"
                :title="__('pages/about-me.interests_head_title')"
                :note="__('pages/about-me.interests_head_note')"
            />
            <div class="about-me-beyond">
                @foreach ($interests as $interest)
                    <div class="about-me-beyond-cell">
                        <x-portfolio.image-slot
                            :path="$interest->image_path"
                            :caption="__('pages/about-me.interests_slot', ['title' => $interest->getTranslation('title', $locale)])"
                            :alt="$interest->getTranslation('title', $locale)"
                            height="230px"
                        />
                        <div class="about-me-beyond-text">
                            <h3>{!! $interest->highlightedTitle($locale) !!}</h3>
                            <p>{{ $interest->getTranslation('body', $locale) }}</p>
                            <p class="about-me-beyond-meta">{{ $interest->getTranslation('meta', $locale) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- 5 · Things I show up for --}}
    @if ($showUps->isNotEmpty())
        <section id="about-me-showups" @class(['portfolio-section', 'portfolio-section--no-reveal' => $firstSection === 'showups'])>
            <x-portfolio.section-head
                :eyebrow="__('pages/about-me.showups_head_eyebrow')"
                :title="__('pages/about-me.showups_head_title')"
                :note="__('pages/about-me.showups_head_note', ['url' => route('experience')])"
            />
            <div class="about-me-showups">
                @foreach ($showUps as $showUp)
                    @php $badge = $showUp->badges->first(); @endphp
                    <div class="about-me-show" @if ($badge?->color) style="--show-color: {{ $badge->color }}" @endif>
                        <div class="about-me-show-top">
                            @if ($badge)
                                <span class="about-me-show-kind">{{ $badge->getTranslation('name', $locale) }}</span>
                            @endif
                            <span class="about-me-show-year">{{ $showUp->getTranslation('year', $locale) }}</span>
                        </div>
                        <h3>{{ $showUp->getTranslation('title', $locale) }}</h3>
                        <p>{{ $showUp->getTranslation('subtitle', $locale) }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- 6 · References — the home page's carousel, same markup and JS --}}
    @if ($reviews->isNotEmpty())
        <section id="about-me-references" @class(['portfolio-section', 'portfolio-section--no-reveal' => $firstSection === 'reviews'])>
            <x-portfolio.section-head
                :eyebrow="__('pages/about-me.references_head_eyebrow')"
                :title="__('pages/about-me.references_head_title')"
                :note="__('pages/about-me.references_head_note')"
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
    @endif

    {{-- 7 · Contact — the page's end punctuation, the only tinted surface --}}
    @if ($ctaTitle !== '')
        <section id="about-me-contact" @class(['portfolio-section', 'portfolio-section--no-reveal' => $firstSection === 'contact'])>
            <div class="about-me-cta">
                <div>
                    @if ($available !== '')
                        <p class="about-me-cta-available">
                            <x-portfolio.availability-dot />{{ $available }}
                        </p>
                    @endif
                    <h2>{!! $ctaTitle !!}</h2>
                    @if ($ctaSub !== '')
                        <p class="about-me-cta-sub">{{ $ctaSub }}</p>
                    @endif
                </div>
                <div class="about-me-cta-actions">
                    <div class="about-me-cta-row">
                        <x-portfolio.pill-button :href="config('portfolio.social.email')">{{ __('pages/about-me.cta_email') }}</x-portfolio.pill-button>
                        @if (config('portfolio.cv_path'))
                            <x-portfolio.pill-button :href="asset(config('portfolio.cv_path'))" variant="ghost" :download="true">{{ __('pages/about-me.cta_cv') }}</x-portfolio.pill-button>
                        @endif
                    </div>
                    <div class="about-me-cta-row">
                        <x-portfolio.pill-button :href="config('portfolio.social.github')" variant="ghost">GitHub</x-portfolio.pill-button>
                        <x-portfolio.pill-button :href="config('portfolio.social.linkedin')" variant="ghost">LinkedIn</x-portfolio.pill-button>
                    </div>
                    <p class="about-me-cta-fine">{{ __('pages/about-me.cta_fine') }}</p>
                </div>
            </div>
        </section>
    @endif

</x-portfolio-layout>
