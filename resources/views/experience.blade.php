<x-portfolio-layout :title="__('layout/header.experience_title')" :description="__('layout/header.experience_desc')" :styles="['resources/css/pages/experience.css']">

    @php $locale = app()->getLocale(); @endphp

    {{-- Hero --}}
    <x-portfolio.page-hero
        :eyebrow="\App\Models\Setting::text('experience_hero_suptitle', $locale)"
        :title="\App\Models\Setting::text('experience_hero_title', $locale)"
        :roles="\App\Models\Setting::list('experience_hero_roles', $locale)"
        :image="config('portfolio.hero_images.experience')"
        image-alt=""
    />

    <section id="experience" class="portfolio-section">
        <h2>{{ __('home/experience.title') }}</h2>

        {{-- Filter tabs --}}
        <div id="exp-filters">
            <div class="exp-filters-group">
                <button type="button" class="exp-filter" data-filter="work">{{ __('home/experience.title_work') }}</button>
                <button type="button" class="exp-filter" data-filter="life">{{ __('home/experience.title_life') }}</button>
            </div>
            @if ($badges->isNotEmpty())
                <div class="exp-filters-group">
                    @foreach ($badges as $badge)
                        <button
                            type="button"
                            class="exp-filter exp-filter--badge"
                            data-filter="badge:{{ $badge->slug }}"
                            style="--badge-color: {{ $badge->color }}"
                        >{{ $badge->getTranslation('name', $locale) }}</button>
                    @endforeach
                </div>
            @endif
            <div id="exp-search-wrap">
                <button type="button" id="exp-search-btn" aria-label="{{ __('home/experience.search_placeholder') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                </button>
                <input type="search" id="exp-search" placeholder="{{ __('home/experience.search_placeholder') }}" autocomplete="off">
            </div>
        </div>

        {{-- Masonry grid --}}
        <div id="exp-grid">
            <div id="exp-grid-line"></div>
            <div id="exp-col-left"></div>
            <div id="exp-col-right"></div>
        </div>

        {{-- Hidden card pool: source of truth for JS --}}
        <div id="exp-cards-pool" style="display: none">
            @foreach ($experiences as $exp)
                @php
                    $badgeSlugs = $exp->badges->pluck('slug')->toArray();
                @endphp
                <div
                    class="exp-card {{ $exp->is_special ? 'exp-card--special' : '' }}"
                    data-type="{{ $exp->type }}"
                    data-badges='@json($badgeSlugs)'
                >
                    @if ($exp->is_special)
                        {{-- Blurred copy of the border gradient; both pseudo-elements
                             are already taken by the timeline connector line + dot. --}}
                        <span class="exp-card-glow" aria-hidden="true"></span>
                    @endif

                    {{-- Top row: image, year, title, subtitle --}}
                    <div class="exp-card-header">
                        @if ($exp->image_path)
                            <img class="exp-card-img" src="{{ asset($exp->image_path) }}" alt="{{ $exp->getTranslation('title', $locale) }}">
                        @endif
                        <div class="exp-card-meta">
                            <div class="exp-card-tags">
                                @if ($exp->getTranslation('year', $locale))
                                    <span class="mini exp-card-year">{{ $exp->getTranslation('year', $locale) }}</span>
                                @endif
                                <span class="exp-card-type">{{ $exp->type }}</span>
                                @foreach ($exp->badges as $badge)
                                    <span class="exp-badge" style="--badge-color: {{ $badge->color }}">
                                        {{ $badge->getTranslation('name', $locale) }}
                                    </span>
                                @endforeach
                            </div>
                            <h4 class="exp-card-title">{{ $exp->getTranslation('title', $locale) }}</h4>
                            @if ($exp->getTranslation('subtitle', $locale))
                                <p class="exp-card-subtitle">{{ $exp->getTranslation('subtitle', $locale) }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Optional content (markdown) --}}
                    @php $content = $exp->getTranslation('content', $locale); @endphp
                    @if ($content)
                        <div class="exp-card-content">{!! Str::markdown($content) !!}</div>
                    @endif

                    {{-- Links row --}}
                    @if (!empty($exp->links))
                        <div class="exp-card-links">
                            @foreach ($exp->links as $link)
                                <a href="{{ $link['url'] }}" target="_blank" rel="noopener" class="exp-card-link">
                                    @if (!empty($link['img_url']))
                                        <img
                                            src="{{ $link['img_url'] }}"
                                            alt="{{ is_array($link['alt'] ?? null) ? ($link['alt'][$locale] ?? $link['alt']['en'] ?? '') : ($link['alt'] ?? '') }}"
                                        >
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    <script>
    (function () {
        const pool = document.getElementById('exp-cards-pool');
        const colLeft = document.getElementById('exp-col-left');
        const colRight = document.getElementById('exp-col-right');
        const gridEl = document.getElementById('exp-grid');
        const gridLine = document.getElementById('exp-grid-line');
        const searchInput = document.getElementById('exp-search');
        const allCards = Array.from(pool.querySelectorAll('.exp-card'));
        allCards.forEach(function (card, i) { card.dataset.idx = i; });
        const activeFilters = new Set();

        function matchesFilters(card) {
            const query = searchInput.value.trim().toLowerCase();
            if (query) {
                const title = (card.querySelector('.exp-card-title')?.textContent || '').toLowerCase();
                if (!title.includes(query)) { return false; }
            }
            if (activeFilters.size === 0) { return true; }

            // Faceted filtering: OR within a group (type / badge), AND across
            // groups. Selecting "Work" + a badge narrows to work cards that
            // also carry that badge, instead of unioning the two sets.
            const typeFilters = [];
            const badgeFilters = [];
            for (const f of activeFilters) {
                if (f.startsWith('badge:')) { badgeFilters.push(f.slice(6)); }
                else { typeFilters.push(f); }
            }

            if (typeFilters.length && !typeFilters.includes(card.dataset.type)) {
                return false;
            }
            if (badgeFilters.length) {
                const slugs = JSON.parse(card.dataset.badges || '[]');
                if (!badgeFilters.some(function (b) { return slugs.includes(b); })) {
                    return false;
                }
            }
            return true;
        }

        function updateGridLine() {
            const cards = [...colLeft.querySelectorAll('.exp-card'), ...colRight.querySelectorAll('.exp-card')];
            if (cards.length === 0) {
                gridLine.style.top = '0px';
                gridLine.style.height = '0px';
                return;
            }
            const gridTop = gridEl.getBoundingClientRect().top;
            let firstMid = Infinity;
            let lastMid = -Infinity;
            cards.forEach(function (card) {
                const r = card.getBoundingClientRect();
                const mid = r.top + r.height / 2 - gridTop;
                if (mid < firstMid) { firstMid = mid; }
                if (mid > lastMid) { lastMid = mid; }
            });
            gridLine.style.top = firstMid + 'px';
            gridLine.style.height = (lastMid - firstMid) + 'px';
        }

        function layoutMasonry() {
            colLeft.replaceChildren();
            colRight.replaceChildren();
            let leftH = 0;
            let rightH = 0;
            allCards.forEach(function (card) {
                const clone = card.cloneNode(true);
                clone.classList.toggle('is-dimmed', !matchesFilters(card));
                if (leftH <= rightH) {
                    colLeft.appendChild(clone);
                    leftH += clone.offsetHeight;
                } else {
                    colRight.appendChild(clone);
                    rightH += clone.offsetHeight;
                }
            });
            updateGridLine();
        }

        // Filter/search changes only re-toggle .is-dimmed on the already-placed
        // clones — no rebuild, so the dim transition animates and the grid never
        // reflows while filtering.
        function applyDim() {
            [...colLeft.children, ...colRight.children].forEach(function (clone) {
                clone.classList.toggle('is-dimmed', !matchesFilters(allCards[Number(clone.dataset.idx)]));
            });
        }

        document.querySelectorAll('.exp-filter').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const f = btn.dataset.filter;
                const isBadge = f.startsWith('badge:');

                if (isBadge) {
                    // Badge filters are multi-select — toggle independently.
                    if (activeFilters.has(f)) {
                        activeFilters.delete(f);
                        btn.classList.remove('active');
                    } else {
                        activeFilters.add(f);
                        btn.classList.add('active');
                    }
                } else {
                    // Type filters (work/life) are single-select.
                    const wasActive = activeFilters.has(f);
                    document.querySelectorAll('.exp-filter:not(.exp-filter--badge).active').forEach(function (b) {
                        activeFilters.delete(b.dataset.filter);
                        b.classList.remove('active');
                    });
                    if (!wasActive) {
                        activeFilters.add(f);
                        btn.classList.add('active');
                    }
                }
                applyDim();
            });
        });

        searchInput.addEventListener('input', applyDim);

        const searchWrap = document.getElementById('exp-search-wrap');
        const searchBtn = document.getElementById('exp-search-btn');

        searchBtn.addEventListener('click', function () {
            searchWrap.classList.toggle('open');
            if (searchWrap.classList.contains('open')) {
                searchInput.focus();
            } else {
                searchInput.value = '';
                applyDim();
            }
        });

        document.addEventListener('click', function (e) {
            if (!searchWrap.contains(e.target)) {
                searchWrap.classList.remove('open');
                searchInput.value = '';
                applyDim();
            }
        });

        let resizeTimer;
        window.addEventListener('resize', function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(layoutMasonry, 150);
        });

        layoutMasonry();
    })();
    </script>

</x-portfolio-layout>
