# Plan — upgrade.md backlog + Embla reviews carousel

Execution plan for the open items in `upgrade.md`, written so it can be run task by
task. Each task is independent and shippable on its own. Where a root cause is not
yet verified the task says so and starts with a reproduce step.

Already done in earlier commits, **do not redo**:

- footer card on desktop / band on mobile (`01d09b1`)
- hero rotator text stored in DB with `<span>` gold accents (`35c04f2`)
- tools cards hover lift removed (`f3c041d`)

Key files:

- `resources/views/welcome.blade.php` — reviews section ~L114–134
- `resources/css/pages/index.css` — reviews ~L236–370, responsive ~L400–430
- `resources/js/app.js` — `initReviewsCarousel()` L449–580, `initScrollReveal()` L253
- `resources/views/experience.blade.php` — markup + inline filter/masonry script
- `resources/css/pages/experience.css` — filters L5–100, special card L195–225
- `app/Http/Controllers/ExperienceController.php`
- `app/Models/Setting.php` — settings cache

Reminders: PHP changes → `docker exec portfolio-app-1 vendor/bin/pint --dirty --format agent`.
Tests → `docker exec portfolio-app-1 php artisan test --compact --filter=…`.
Vite builds run **on the host** (`npm run build` / `npm run dev`), not in the container.

---

## 1. Reviews carousel → Embla (`upgrade.md`: "oprav ten carousel, pouzi knihovnu")

**Current:** hand-rolled paging carousel in `initReviewsCarousel()` (`app.js` L449–580)
— `translate` on the track by `index * viewport.clientWidth`, manual per-view
breakpoint maths (`getPerView()`), own autoplay/swipe/resize handling. Page-based, so
the last page can show a half-empty row; the swipe handler is pointer-delta only (no
drag follow); `clientWidth` maths drifts against the `gap`.

**Target:** [Embla Carousel](https://www.embla-carousel.com/) — already installed:
`embla-carousel@^8.6.0` + `embla-carousel-autoplay@^8.6.0` are in `package.json`
dependencies (host `node_modules` already populated). Slide-based looping, real drag,
correct gap handling, `slidesToScroll: 1`.

### 1a. Markup — `resources/views/welcome.blade.php`

Keep class names so existing CSS/reveal code keeps matching. Only change: drop
`aria-hidden` from the dots wrapper (it contains focusable buttons — hiding it from
AT while keeping it tabbable is an a11y bug), and give the arrows `type="button"`
(already present) without `data-dir` (Embla owns direction now).

```blade
<div class="reviews-carousel" data-reviews-carousel>
    <button type="button" class="reviews-carousel-arrow reviews-carousel-arrow-prev" aria-label="{{ __('home/reviews.prev') }}">‹</button>
    <div class="reviews-carousel-viewport">
        <article class="reviews-row" role="list">
            @foreach ($reviews as $review)
                <div class="reviews-row-card" role="listitem">
                    …unchanged…
                </div>
            @endforeach
        </article>
    </div>
    <button type="button" class="reviews-carousel-arrow reviews-carousel-arrow-next" aria-label="{{ __('home/reviews.next') }}">›</button>
    <div class="reviews-carousel-dots"></div>
</div>
```

Remove `tabindex="0"` on the wrapper — Embla's arrows/dots are already focusable and
the roving-tabindex keyboard handler is going away.

### 1b. JS — `resources/js/app.js`

Add at the top of the file (it has no imports today; Vite handles ESM fine):

```js
import EmblaCarousel from 'embla-carousel';
import Autoplay from 'embla-carousel-autoplay';
```

Replace the whole of `initReviewsCarousel()` (L449–580) with:

```js
function initReviewsCarousel() {
    const carousel = document.querySelector('[data-reviews-carousel]');
    const viewport = carousel?.querySelector('.reviews-carousel-viewport');
    const prevBtn = carousel?.querySelector('.reviews-carousel-arrow-prev');
    const nextBtn = carousel?.querySelector('.reviews-carousel-arrow-next');
    const dotsWrap = carousel?.querySelector('.reviews-carousel-dots');

    if (!carousel || !viewport || !prevBtn || !nextBtn || !dotsWrap) {
        return;
    }

    const plugins = prefersReducedMotion()
        ? []
        : [Autoplay({ delay: 6000, stopOnInteraction: false, stopOnMouseEnter: true })];

    const embla = EmblaCarousel(viewport, {
        loop: true,
        align: 'start',
        slidesToScroll: 1,
        containScroll: 'trimSnaps',
        duration: prefersReducedMotion() ? 0 : 25,
        watchDrag: !prefersReducedMotion(),
    }, plugins);

    // Arrows
    prevBtn.addEventListener('click', () => embla.scrollPrev());
    nextBtn.addEventListener('click', () => embla.scrollNext());

    // Dots — one per snap point, rebuilt on reInit (breakpoint changes alter
    // how many cards fit, so the snap count changes with viewport width).
    const buildDots = () => {
        dotsWrap.replaceChildren();
        embla.scrollSnapList().forEach((_, i) => {
            const dot = document.createElement('button');
            dot.type = 'button';
            dot.className = 'reviews-carousel-dot';
            dot.setAttribute('aria-label', `${i + 1}`);
            dot.addEventListener('click', () => embla.scrollTo(i));
            dotsWrap.appendChild(dot);
        });
    };

    const syncUi = () => {
        const selected = embla.selectedScrollSnap();
        [...dotsWrap.children].forEach((dot, i) => {
            if (i === selected) {
                dot.setAttribute('aria-current', 'true');
            } else {
                dot.removeAttribute('aria-current');
            }
        });
        // With loop: true prev/next are never disabled; `is-static` hides the
        // controls when everything already fits (single snap point).
        carousel.classList.toggle('is-static', embla.scrollSnapList().length <= 1);
    };

    embla.on('init', () => { buildDots(); syncUi(); });
    embla.on('reInit', () => { buildDots(); syncUi(); });
    embla.on('select', syncUi);

    buildDots();
    syncUi();
}
```

Notes:

- `prefersReducedMotion()` already exists at `app.js:140` — reuse it, do not redefine.
- Delete the old `AUTOPLAY_MS` / `SWIPE_THRESHOLD` / `getPerView()` / `render()` /
  `recalc()` / resize-debounce / `visibilitychange` code — Embla covers all of it
  (`Autoplay` pauses on `document.hidden` itself).
- `initReviewsCarousel()` stays called from the `DOMContentLoaded` block (`app.js:628`).
  Also call `embla.reInit()` on `livewire:navigated`? Not needed — the home page is a
  full render; leave it.

### 1c. CSS — `resources/css/pages/index.css`

Embla requires: viewport `overflow: hidden`, container `display: flex`, slides with a
fixed flex basis and **no `gap`** (Embla measures slide widths; use padding-left on
the slides + a negative margin on the container, the library's documented pattern).

Replace the `.reviews-carousel-viewport` / `.reviews-row` / `.reviews-row-card` sizing
block (~L242–265) with the following. The gutter is a transparent left border on the
slide (`background-clip: padding-box` keeps the surface off it) — no extra DOM, and
Embla still measures the slide correctly:

```css
.reviews-carousel-viewport {
    overflow: hidden;
}

.reviews-row {
    display: flex;
    /* Embla gap pattern: negative container margin + per-slide left gutter.
       A real `gap` would break Embla's slide-width measurement. */
    margin-left: -1.25rem;
    touch-action: pan-y pinch-zoom;
}

.reviews-row-card {
    flex: 0 0 33.333%;
    min-width: 0;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: var(--sp-card-pad);
    border: var(--border-w) solid var(--c-primary-lt);
    border-left: 1.25rem solid transparent;   /* gutter */
    background-color: var(--c-surface);
    background-clip: padding-box;
    border-radius: var(--r-card-sm);
}
```

If the transparent-border gutter fights the card border visually (left edge missing),
fall back to wrapping the card body in `.reviews-row-card > div` and moving
border/background there — verify in the browser before committing.

Responsive: replace the `flex-basis` overrides at L406 and L425 with `flex: 0 0 50%`
(≤992px) and `flex: 0 0 100%` (≤576px). Delete `.reviews-row { transition: none; }`
under `prefers-reduced-motion` (no CSS transition drives the track any more) and drop
the stale comment on `.reviews-row` about "JS sets an inline translate".

### 1d. Scroll-reveal interaction

`initScrollReveal()` (L253–301) sets `--i` stagger on `.reviews-row` children and adds
`is-visible` to all cards at once via `reviewsObserver`. Embla writes `transform` on
the container, not the slides, so the per-card `translate` entrance still works. **Keep
this code as is** — but re-check after wiring Embla that cards are not stuck at
`opacity: 0` (if Embla initialises before the observer fires it is still fine; the
observer targets `.reviews-carousel`, which is unchanged).

### 1e. Test

`tests/Browser/` — add or extend a home-page test asserting the carousel renders and
has no console errors (pest-plugin-browser, runs in the container):

```php
it('renders the reviews carousel without JS errors', function () {
    // seed reviews, then:
    visit('/')->assertNoJavascriptErrors()->assertSee('…review author…');
});
```

Also verify the Vite build passes on the host: `npm run build`.

---

## 2. Review cards: no hover effect (`upgrade.md` sibling of "tools no hover efect")

`resources/css/pages/index.css` L351–354:

```css
.reviews-row-card:hover {
    translate: 0 -6px;
    box-shadow: 0 12px 24px -10px color-mix(…);
}
```

Delete that rule. Then trim the shared transition at L335–343 so review cards no
longer animate `scale`/`box-shadow`:

- keep `.tools-row-card, .reviews-row-card { opacity / translate }` entrance transition,
- drop `scale` and `box-shadow` from the `transition` list if nothing else uses them
  (grep `.tools-row-card:hover` first — the tools hover was already removed in
  `f3c041d`, so both are likely dead).

Cursor should stay default (no `cursor: pointer` is set today — confirm).

---

## 3. Cache off during development (`upgrade.md`: "reset like all cache - and dont save it for now")

`app/Models/Setting.php:35` uses `Cache::rememberForever('settings.all', …)`, flushed
on save (L23). During development the stale-settings surprise is not worth it.

```php
public static function all(): Collection   // adjust to the real signature
{
    if (config('app.debug')) {
        return static::query()->get()->…;   // same mapping, no cache
    }

    return Cache::rememberForever('settings.all', fn () => …);
}
```

Then clear once:

```bash
docker exec portfolio-app-1 php artisan optimize:clear
```

(covers config, route, view and application cache). Do **not** add
`php artisan config:cache` anywhere.

Test: a feature test asserting a `Setting` update is reflected on the next read when
`app.debug` is true.

---

## 4. Experience: only one filter active at a time

`resources/views/experience.blade.php` L188–200 — the click handler toggles into a
`Set`, so any number can be active.

Change to single-select with click-to-clear:

```js
document.querySelectorAll('.exp-filter').forEach(function (btn) {
    btn.addEventListener('click', function () {
        const f = btn.dataset.filter;
        const wasActive = activeFilters.has(f);
        activeFilters.clear();
        document.querySelectorAll('.exp-filter.active').forEach(function (b) {
            b.classList.remove('active');
        });
        if (!wasActive) {
            activeFilters.add(f);
            btn.classList.add('active');
        }
        layoutMasonry();
    });
});
```

`matchesFilters()` keeps working unchanged (the faceted AND/OR logic just degenerates
to a single facet). Leave the faceted code in place — it is correct and harmless.

---

## 5. Experience: keep non-matching cards visible but dimmed, smooth transition

**This is the structurally hardest item.** Today `layoutMasonry()` (L169–186) *clones
matching cards only* from the hidden pool into the two columns and calls
`replaceChildren()` — non-matching cards do not exist in the DOM, so they cannot be
dimmed and every filter change is a hard rebuild (no transition possible).

Approach: always lay out **all** cards, toggle a `.is-dimmed` class instead of
filtering the list.

```js
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
```

But `replaceChildren()` still destroys/recreates nodes, so the opacity change is not
animated. Two-step fix:

1. **First render** builds the columns once (as above).
2. **Subsequent filter changes** must not rebuild — only re-toggle `.is-dimmed` on the
   already-placed clones:

```js
function applyDim() {
    [...colLeft.children, ...colRight.children].forEach(function (clone, i) {
        clone.classList.toggle('is-dimmed', !matchesFilters(allCards[…]));
    });
}
```

Index mapping is fragile — instead stamp an index on each pool card and its clone
(`card.dataset.idx = i` in the pool loop, cloned automatically) and look up
`allCards[clone.dataset.idx]`. Call `layoutMasonry()` only on load + resize; call
`applyDim()` on filter clicks and search input.

Caveat to flag in the commit: because the layout no longer reflows, filtering keeps
the full grid height. That is the requested behaviour ("the non filter things will be
there, but the not selected will be a lot darker").

CSS (`experience.css`, near the card rules):

```css
.exp-card {
    transition: opacity var(--t-base), filter var(--t-base);
}

.exp-card.is-dimmed {
    opacity: 0.25;
    filter: grayscale(0.6);
    pointer-events: none;
}

@media (prefers-reduced-motion: reduce) {
    .exp-card { transition: none; }
}
```

Search behaves the same way (dim, not remove).

---

## 6. Experience: badge filters missing ("i dont see the filters other than work and life, mby cache?")

**Not a cache problem.** `ExperienceController` derives the filter list from the
experiences' attached badges:

```php
$badges = $experiences->flatMap(fn ($e) => $e->badges)->unique('id')->values();
```

Database state today: **14 badges exist, exactly 1 experience has any attached**
(pivot table is `experience_badge`). So the row renders with only that one
experience's badges — visually "missing".

Two options, pick one:

- **(a) preferred)** Keep the derived list (a filter that matches nothing is bad UX)
  and fix the data: attach badges to experiences in `database/seeders/` +
  through the admin at `/manage/experiences`. Update the seeder so a fresh
  `db:seed` produces a realistic spread.
- **(b)** Show all badges: `$badges = Badge::orderBy('sort_order')->get();` — simpler,
  but yields filters that empty the grid. With task 5's dim-don't-remove behaviour
  that is less harmful (everything just goes dark), still misleading.

Recommendation: **(a)**, plus a feature test asserting the experience page lists a
badge filter for every badge attached to at least one experience.

---

## 7. Experience: filter bar design ("do better design on those, at least aligned")

`experience.css` L7–80. Current issues: the two groups + search sit in one
`flex-wrap: wrap` row with `gap: 0`, group divider done with `margin-left`/
`border-left`, badge pills a different size and text-transform than type pills, so
nothing lines up when it wraps.

Concrete changes:

- `#exp-filters`: `gap: 0.75rem` (drop `gap: 0`), `row-gap: 0.75rem`, keep
  `flex-wrap: wrap`, `align-items: center`.
- `.exp-filters-group + .exp-filters-group`: drop `margin-left`/`padding-left`, keep
  the `border-left` divider but hide it when wrapped is not expressible in CSS —
  simpler: remove the divider entirely and rely on the larger `gap`.
- Make both pill variants share one size: same `font-size: var(--fs-sm)`, same
  `padding: 0.3rem 0.9rem`, same `line-height`; badge pills keep only their colour
  differences (`--badge-color` border/text). Removes the ragged baseline.
- `#exp-search-wrap { margin-left: auto }` stays, but add `flex-shrink: 0`.
- ≤992px (L463): stack the groups (`flex-direction: column; align-items: stretch`) and
  let the search go full width.

Verify visually with Playwright from the host against `http://localhost:8008/experience`
in both themes before committing.

---

## 8. Experience: badges next to the date / work-life tag

`experience.blade.php` L58–64: the header meta row holds `.exp-card-year` +
`.exp-card-type`; the badges render in a separate `.exp-card-badges` row at L96.

Move the badges `@foreach` into the same flex row as year/type (replace the inline
`style="display:flex;align-items:center;gap:0.5rem"` with a real class,
`.exp-card-tags`, defined in `experience.css` with `display:flex; flex-wrap:wrap;
align-items:center; gap:0.4rem`). Delete the standalone `.exp-card-badges` block and
its CSS, or keep the class name and just relocate the markup — whichever keeps the
diff smaller.

Watch out: the badge pill font size must drop a notch when it sits next to the small
year/type text.

---

## 9. Experience: highlight special entries

Already implemented — `.exp-card--special` (`experience.css` L203–224) draws an
animated rotating conic-gradient border via the `@property --exp-border-angle`
registered custom property. So this upgrade.md line is likely "I can't see it"
(no experience has `is_special = true`).

Steps:

1. Check the data: `docker exec portfolio-app-1 php artisan tinker --execute 'echo \App\Models\Experience::where("is_special", true)->count();'`
2. If 0 — set one in the seeder/admin, and that is the whole task.
3. Only if the effect is seen and still not liked, add the alternative the user
   mentioned (background glow):
   ```css
   .exp-card--special { box-shadow: 0 0 28px -6px color-mix(in srgb, var(--c-primary) 40%, transparent); }
   ```
   Also add a `prefers-reduced-motion` guard that stops the border rotation —
   currently missing:
   ```css
   @media (prefers-reduced-motion: reduce) {
       .exp-card--special { animation: none; }
   }
   ```

---

## Suggested order

1. Task 3 (cache off) — unblocks seeing every later change immediately.
2. Task 1 + 2 (carousel + hover) — one commit each.
3. Task 6 (badge data) → 4 → 5 → 7 → 8 → 9 — experience page, in this order because
   5 rewrites the layout function 4 touches.
4. Task 10 (admin dark mode).
5. Task 11 when content arrives.

Commit style: `feat(reviews): …`, `fix(experience): …` — match the existing log.
