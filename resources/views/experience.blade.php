<x-portfolio-layout :title="__('layout/header.experience_title')" :description="__('layout/header.experience_desc')" :styles="['resources/css/pages/experience.css']">

    @php $locale = app()->getLocale(); @endphp

    {{-- Hero --}}
    <x-portfolio.dock-hero
        :eyebrow="\App\Models\Setting::text('experience_hero_suptitle', $locale)"
        :title="\App\Models\Setting::text('experience_hero_title', $locale)"
        :roles="\App\Models\Setting::list('experience_hero_roles', $locale)"
        :tags="__('pages/experience.hero_tags')"
        :wordmark="__('pages/experience.hero_wordmark')"
        :dock-image="config('portfolio.hero_images.experience_dock')"
        dock-image-alt=""
        :photo="config('portfolio.hero_images.experience')"
        photo-alt=""
        :caption="__('pages/experience.hero_photo_caption')"
    />

    {{-- No fade-up: the hero leaves this section's top edge inside the first
         viewport, so it must already be there when the page paints. --}}
    <section id="experience" class="portfolio-section portfolio-section--no-reveal">
        <h2>{{ __('home/experience.title') }}</h2>

        {{-- Filter bar --}}
        <div class="exp-filterbar" id="exp-filterbar">
            <div class="exp-filterbar-row">
                <div class="exp-scope" id="exp-scope">
                    <span class="exp-scope-thumb" id="exp-scope-thumb" aria-hidden="true"></span>
                    <button type="button" data-scope="all" aria-pressed="true">{{ __('home/experience.title_all') }}</button>
                    <button type="button" data-scope="work" aria-pressed="false">{{ __('home/experience.title_work') }}</button>
                    <button type="button" data-scope="life" aria-pressed="false">{{ __('home/experience.title_life') }}</button>
                </div>

                <label class="exp-search">
                    <svg viewBox="0 0 16 16" aria-hidden="true">
                        <circle cx="6.8" cy="6.8" r="4.6" />
                        <line x1="10.4" y1="10.4" x2="14" y2="14" />
                    </svg>
                    <input
                        type="search"
                        id="exp-search"
                        placeholder="{{ __('home/experience.search_placeholder') }}"
                        autocomplete="off"
                    >
                </label>

                <div
                    class="exp-count"
                    id="exp-count"
                    aria-live="polite"
                    data-one="{{ __('home/experience.count_one') }}"
                    data-few="{{ __('home/experience.count_few') }}"
                    data-many="{{ __('home/experience.count_many') }}"
                ></div>
            </div>

            @if ($badges->isNotEmpty())
                <div class="exp-filterbar-row">
                    <div class="exp-tags" id="exp-tags">
                        @foreach ($badges as $badge)
                            <button
                                type="button"
                                class="exp-tag"
                                aria-pressed="false"
                                data-tag="{{ $badge->slug }}"
                                style="--badge-color: {{ $badge->color }}"
                            >{{ $badge->getTranslation('name', $locale) }}</button>
                        @endforeach
                    </div>
                    <button type="button" class="exp-clear" id="exp-clear">{{ __('home/experience.clear_filters') }}</button>
                </div>
            @endif
        </div>

        {{-- Masonry grid --}}
        <div id="exp-grid">
            <div id="exp-grid-line"></div>
            <div id="exp-col-left"></div>
            <div id="exp-col-right"></div>
        </div>

        <p class="exp-empty" id="exp-empty" hidden>
            {{ __('home/experience.empty') }}
            <button type="button" class="exp-empty-reset" id="exp-reset">{{ __('home/experience.reset') }}</button>
        </p>

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

        const bar = document.getElementById('exp-filterbar');
        const scopeEl = document.getElementById('exp-scope');
        const thumb = document.getElementById('exp-scope-thumb');
        const tagRow = document.getElementById('exp-tags');          // null when there are no badges
        const clearBtn = document.getElementById('exp-clear');       // null when there are no badges
        const countEl = document.getElementById('exp-count');
        const emptyEl = document.getElementById('exp-empty');
        const resetBtn = document.getElementById('exp-reset');
        const searchInput = document.getElementById('exp-search');

        const allCards = Array.from(pool.querySelectorAll('.exp-card'));
        allCards.forEach(function (card, i) {
            card.dataset.idx = i;
            // Cached once: the search matches the card's whole visible text, and reading
            // textContent on every keystroke would be wasteful.
            card.dataset.searchText = card.textContent.replace(/\s+/g, ' ').trim().toLowerCase();
        });

        let scope = 'all';
        const activeTags = new Set();

        function matchesFilters(card) {
            if (scope !== 'all' && card.dataset.type !== scope) { return false; }

            if (activeTags.size) {
                const slugs = JSON.parse(card.dataset.badges || '[]');
                if (!slugs.some(function (slug) { return activeTags.has(slug); })) { return false; }
            }

            const query = searchInput.value.trim().toLowerCase();
            if (query && !card.dataset.searchText.includes(query)) { return false; }

            return true;
        }

        function moveThumb() {
            const pressed = scopeEl.querySelector('button[aria-pressed="true"]');
            if (!pressed) { return; }
            thumb.style.width = pressed.offsetWidth + 'px';
            thumb.style.transform = 'translateX(' + (pressed.offsetLeft - thumb.offsetLeft) + 'px)';
        }

        function countLabel(visible, total) {
            // Czech needs three plural forms (1 / 2-4 / 5+); English reuses one of them.
            const key = visible === 1 ? 'one' : (visible >= 2 && visible <= 4 ? 'few' : 'many');
            return countEl.dataset[key]
                .replace(':count', '<b>' + visible + '</b>')
                .replace(':total', total);
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

        // Scroll-reveal entrance for the cards themselves (see .exp-card in
        // experience.css). Owned locally rather than by app.js's shared
        // observer because resize rebuilds the columns from scratch below —
        // a one-shot querySelectorAll snapshot would go stale the moment
        // that happens. Cards already revealed before a rebuild carry that
        // state over to their replacement clone instead of being
        // re-observed: once a rebuild scrolls a card off-screen (e.g. the
        // user resizes after scrolling down), a fresh observe() would never
        // fire and the card would stay invisible forever.
        const cardObserver = ('IntersectionObserver' in window)
            ? new IntersectionObserver(function (entries) {
                  entries.forEach(function (entry) {
                      if (entry.isIntersecting) {
                          entry.target.classList.add('is-visible');
                          cardObserver.unobserve(entry.target);
                      }
                  });
              }, { threshold: 0.15 })
            : null;

        function layoutMasonry() {
            const revealedIdx = new Set(
                [...colLeft.children, ...colRight.children]
                    .filter(function (c) { return c.classList.contains('is-visible'); })
                    .map(function (c) { return c.dataset.idx; })
            );

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
            [colLeft, colRight].forEach(function (col) {
                [...col.children].forEach(function (card, i) {
                    card.style.setProperty('--i', i);
                    if (revealedIdx.has(card.dataset.idx)) {
                        card.classList.add('is-visible');
                    } else if (cardObserver) {
                        cardObserver.observe(card);
                    } else {
                        card.classList.add('is-visible');
                    }
                });
            });
            updateGridLine();
        }

        // Filter/search changes only re-toggle .is-dimmed on the already-placed
        // clones — no rebuild, so the dim transition animates and the grid never
        // reflows while filtering.
        function applyDim() {
            let visible = 0;
            [...colLeft.children, ...colRight.children].forEach(function (clone) {
                const matches = matchesFilters(allCards[Number(clone.dataset.idx)]);
                clone.classList.toggle('is-dimmed', !matches);
                if (matches) { visible++; }
            });

            countEl.innerHTML = countLabel(visible, allCards.length);
            emptyEl.hidden = visible !== 0;
            bar.classList.toggle(
                'has-filters',
                scope !== 'all' || activeTags.size > 0 || searchInput.value.trim() !== ''
            );
        }

        scopeEl.addEventListener('click', function (e) {
            const btn = e.target.closest('button[data-scope]');
            if (!btn) { return; }
            scopeEl.querySelectorAll('button[data-scope]').forEach(function (b) {
                b.setAttribute('aria-pressed', String(b === btn));
            });
            scope = btn.dataset.scope;
            moveThumb();
            applyDim();
        });

        if (tagRow) {
            tagRow.addEventListener('click', function (e) {
                const btn = e.target.closest('.exp-tag');
                if (!btn) { return; }
                const on = btn.getAttribute('aria-pressed') !== 'true';
                btn.setAttribute('aria-pressed', String(on));
                if (on) { activeTags.add(btn.dataset.tag); } else { activeTags.delete(btn.dataset.tag); }
                applyDim();
            });
        }

        searchInput.addEventListener('input', applyDim);

        function clearFilters() {
            scope = 'all';
            activeTags.clear();
            searchInput.value = '';
            scopeEl.querySelectorAll('button[data-scope]').forEach(function (b) {
                b.setAttribute('aria-pressed', String(b.dataset.scope === 'all'));
            });
            if (tagRow) {
                tagRow.querySelectorAll('.exp-tag').forEach(function (t) { t.setAttribute('aria-pressed', 'false'); });
            }
            moveThumb();
            applyDim();
        }

        if (clearBtn) { clearBtn.addEventListener('click', clearFilters); }
        resetBtn.addEventListener('click', clearFilters);

        let resizeTimer;
        window.addEventListener('resize', function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function () {
                layoutMasonry();
                moveThumb();
            }, 150);
        });

        layoutMasonry();
        moveThumb();
        applyDim();

        // Label widths change once the webfont swaps in, and again on resize.
        if (document.fonts) { document.fonts.ready.then(moveThumb); }
    })();
    </script>

</x-portfolio-layout>
