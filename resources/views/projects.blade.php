<x-portfolio-layout :title="__('layout/header.projects_title')" :description="__('layout/header.projects_desc')" :styles="['resources/css/pages/projects.css']">

    @php $locale = app()->getLocale(); @endphp
    {{-- Hero --}}
    <x-portfolio.dock-hero
        :eyebrow="\App\Models\Setting::text('projects_hero_suptitle', $locale)"
        :title="\App\Models\Setting::text('projects_hero_title', $locale)"
        :roles="\App\Models\Setting::list('projects_hero_roles', $locale)"
        :tags="__('pages/projects.hero_tags')"
        :wordmark="__('pages/projects.hero_wordmark')"
        :photo="config('portfolio.hero_images.projects')"
        photo-alt=""
        photo-position="50% 30%"
    />

    {{-- No fade-up: the hero leaves this section's top edge inside the first
         viewport, so it must already be there when the page paints. --}}
    <section id="projects" class="portfolio-section portfolio-section--no-reveal">
        <div class="proj-filters" id="proj-filters">
            <div class="proj-fgroup" role="group" aria-labelledby="proj-flabel-kind">
                <span class="proj-flabel" id="proj-flabel-kind">{{ __('pages/projects.filter_kind') }}</span>
                <div class="proj-seg" id="proj-seg">
                    <span class="proj-seg-thumb" id="proj-seg-thumb" aria-hidden="true"></span>
                    <button type="button" data-kind-filter="all" aria-pressed="true">{{ __('pages/projects.kind_all') }}</button>
                    <button type="button" data-kind-filter="personal" aria-pressed="false">{{ __('pages/projects.kind_personal') }}</button>
                    <button type="button" data-kind-filter="client" aria-pressed="false">{{ __('pages/projects.kind_client') }}</button>
                    <button type="button" data-kind-filter="school" aria-pressed="false">{{ __('pages/projects.kind_school') }}</button>
                </div>
            </div>

            @if ($stackBadges->isNotEmpty())
                <div class="proj-fgroup" role="group" aria-labelledby="proj-flabel-stack" id="proj-stack">
                    <span class="proj-flabel" id="proj-flabel-stack">{{ __('pages/projects.filter_stack') }}</span>
                    @foreach ($stackBadges as $badge)
                        <button
                            type="button"
                            class="proj-fchip"
                            aria-pressed="false"
                            data-stack-filter="{{ $badge->slug }}"
                            style="--bc: {{ $badge->color }}"
                        >{{ $badge->getTranslation('name', $locale) }}</button>
                    @endforeach
                </div>
            @endif

            <button type="button" class="proj-fclear" id="proj-clear" hidden>{{ __('pages/projects.clear_filters') }}</button>

            <div
                class="proj-fcount"
                id="proj-count"
                aria-live="polite"
                data-one="{{ __('pages/projects.count_one') }}"
                data-few="{{ __('pages/projects.count_few') }}"
                data-many="{{ __('pages/projects.count_many') }}"
            >{!! __('pages/projects.count_many', ['count' => '<b>'.$projects->flatten(1)->count().'</b>', 'total' => $projects->flatten(1)->count()]) !!}</div>
        </div>

        <div class="proj-list">
            @forelse ($projects as $year => $yearProjects)
                <section class="proj-group">
                    <x-portfolio.project-year-head :year="$year" :count="$yearProjects->count()" />

                    @foreach ($yearProjects as $project)
                        <x-portfolio.project-row :project="$project" :locale="$locale" />
                    @endforeach
                </section>
            @empty
                <p class="proj-none">{{ __('pages/projects.empty_list') }}</p>
            @endforelse
        </div>

        <div class="proj-empty" id="proj-empty" hidden>
            <h3 class="proj-etitle">{{ __('pages/projects.empty_title') }}</h3>
            <p class="proj-ebody" id="proj-empty-body" data-template="{{ __('pages/projects.empty_body') }}"></p>
            <button type="button" class="proj-fclear" id="proj-empty-clear">{{ __('pages/projects.clear_filters') }}</button>
        </div>
    </section>

    <script>
    (function () {
        const list = document.querySelector('.proj-list');
        if (!list) { return; }

        /* ── details toggle ── */
        list.addEventListener('click', function (e) {
            const btn = e.target.closest('.proj-toggle');
            if (!btn) { return; }

            const row = btn.closest('.proj-item');
            const open = btn.getAttribute('aria-expanded') !== 'true';

            row.classList.toggle('is-open', open);
            btn.setAttribute('aria-expanded', String(open));
            btn.textContent = open ? btn.dataset.labelClose : btn.dataset.labelOpen;
        });

        /* ── filters ── */
        const segEl = document.getElementById('proj-seg');
        const thumb = document.getElementById('proj-seg-thumb');
        const stackEl = document.getElementById('proj-stack');       // null when no badges
        const countEl = document.getElementById('proj-count');
        const clearBtn = document.getElementById('proj-clear');
        const emptyEl = document.getElementById('proj-empty');
        const emptyBody = document.getElementById('proj-empty-body');
        const emptyClear = document.getElementById('proj-empty-clear');

        const rows = Array.from(list.querySelectorAll('.proj-item'));
        const groups = Array.from(list.querySelectorAll('.proj-group'));
        const total = rows.length;

        // No projects at all: `.proj-none` (server-rendered) already covers
        // this. Skip filter wiring so `.proj-empty` — meant for "filters
        // excluded everything" — never shows on top of it. Nothing below
        // this line has work to do with zero rows: the toggle listener
        // above is a no-op with no `.proj-toggle` in the DOM, and every
        // remaining listener/call here only matters once there is at least
        // one row to filter or count.
        if (total === 0) { return; }

        let kind = 'all';
        const activeStack = new Set();

        function matches(row) {
            if (kind !== 'all' && row.dataset.kind !== kind) { return false; }

            if (activeStack.size) {
                const slugs = JSON.parse(row.dataset.stack || '[]');
                // union: a row matches if it carries ANY pressed badge
                if (!slugs.some(function (slug) { return activeStack.has(slug); })) { return false; }
            }

            return true;
        }

        function moveThumb() {
            const pressed = segEl.querySelector('button[aria-pressed="true"]');
            if (!pressed) { return; }
            thumb.style.width = pressed.offsetWidth + 'px';
            thumb.style.transform = 'translateX(' + (pressed.offsetLeft - thumb.offsetLeft) + 'px)';
        }

        function plural(n) {
            // Czech needs three forms (1 / 2-4 / 5+); English reuses one of them.
            return n === 1 ? 'one' : (n >= 2 && n <= 4 ? 'few' : 'many');
        }

        function activeLabels() {
            const labels = [];
            if (kind !== 'all') {
                labels.push(segEl.querySelector('button[aria-pressed="true"]').textContent.trim());
            }
            if (stackEl) {
                stackEl.querySelectorAll('.proj-fchip[aria-pressed="true"]').forEach(function (chip) {
                    labels.push(chip.textContent.trim());
                });
            }
            return labels;
        }

        function syncUrl() {
            const params = new URLSearchParams(window.location.search);

            if (kind === 'all') { params.delete('kind'); } else { params.set('kind', kind); }
            if (activeStack.size === 0) {
                params.delete('stack');
            } else {
                params.set('stack', Array.from(activeStack).join(','));
            }

            const query = params.toString();
            history.replaceState(null, '', query ? '?' + query : window.location.pathname);
        }

        function apply() {
            let visible = 0;

            rows.forEach(function (row) {
                const show = matches(row);
                row.hidden = !show;
                row.classList.remove('is-first-visible');
                if (show) { visible++; }
            });

            groups.forEach(function (group) {
                const shown = Array.from(group.querySelectorAll('.proj-item')).filter(function (r) { return !r.hidden; });

                group.hidden = shown.length === 0;

                if (shown.length) {
                    // The head already separates the group — whichever row now
                    // leads it must not draw a second rule.
                    shown[0].classList.add('is-first-visible');

                    const countNode = group.querySelector('.proj-ycount');
                    countNode.textContent = countNode.dataset[plural(shown.length)]
                        .replace(':count', shown.length);
                }
            });

            countEl.innerHTML = countEl.dataset[plural(visible)]
                .replace(':count', '<b>' + visible + '</b>')
                .replace(':total', total);

            const labels = activeLabels();
            clearBtn.hidden = labels.length === 0;
            emptyEl.hidden = visible !== 0;
            emptyBody.textContent = emptyBody.dataset.template.replace(':filters', labels.join(' + '));

            syncUrl();
        }

        segEl.addEventListener('click', function (e) {
            const btn = e.target.closest('button[data-kind-filter]');
            if (!btn) { return; }

            segEl.querySelectorAll('button[data-kind-filter]').forEach(function (b) {
                b.setAttribute('aria-pressed', String(b === btn));
            });
            kind = btn.dataset.kindFilter;
            moveThumb();
            apply();
        });

        if (stackEl) {
            stackEl.addEventListener('click', function (e) {
                const btn = e.target.closest('.proj-fchip');
                if (!btn) { return; }

                const on = btn.getAttribute('aria-pressed') !== 'true';
                btn.setAttribute('aria-pressed', String(on));
                if (on) { activeStack.add(btn.dataset.stackFilter); } else { activeStack.delete(btn.dataset.stackFilter); }
                apply();
            });
        }

        function clearFilters() {
            kind = 'all';
            activeStack.clear();
            segEl.querySelectorAll('button[data-kind-filter]').forEach(function (b) {
                b.setAttribute('aria-pressed', String(b.dataset.kindFilter === 'all'));
            });
            if (stackEl) {
                stackEl.querySelectorAll('.proj-fchip').forEach(function (c) { c.setAttribute('aria-pressed', 'false'); });
            }
            moveThumb();
            apply();
        }

        clearBtn.addEventListener('click', clearFilters);
        emptyClear.addEventListener('click', clearFilters);

        // Restore state from ?kind=&stack= so a filtered list can be shared and
        // the back button behaves.
        (function readUrl() {
            const params = new URLSearchParams(window.location.search);
            const urlKind = params.get('kind');
            const urlStack = (params.get('stack') || '').split(',').filter(Boolean);

            const kindBtn = urlKind && segEl.querySelector('button[data-kind-filter="' + CSS.escape(urlKind) + '"]');
            if (kindBtn) {
                segEl.querySelectorAll('button[data-kind-filter]').forEach(function (b) {
                    b.setAttribute('aria-pressed', String(b === kindBtn));
                });
                kind = urlKind;
            }

            if (stackEl) {
                urlStack.forEach(function (slug) {
                    const chip = stackEl.querySelector('.proj-fchip[data-stack-filter="' + CSS.escape(slug) + '"]');
                    if (chip) {
                        chip.setAttribute('aria-pressed', 'true');
                        activeStack.add(slug);
                    }
                });
            }
        })();

        let resizeTimer;
        window.addEventListener('resize', function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(moveThumb, 150);
        });

        moveThumb();
        apply();

        // Label widths change once the webfont swaps in.
        if (document.fonts) { document.fonts.ready.then(moveThumb); }
    })();
    </script>

</x-portfolio-layout>
