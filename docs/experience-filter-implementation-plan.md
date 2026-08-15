# Experience filter redesign — implementation plan

Source: design handoff `newfilter.zip` (`design_handoff_experience_filter/`) — a static HTML prototype of a
redesigned filter bar for the Experience page. This plan translates it to this codebase.

**This plan is split into three sessions.** Each one is self-contained: it lists what must already be true,
what to read, which steps to do, and how to know it is finished. Do **one session per context** — read the
"Context" and "Scope" sections below plus your own session's block, and ignore the other sessions' steps.
Within a session, do the steps in order and run the verification at the end of each one.

| session | steps | deliverable | commitable alone |
|---|---|---|---|
| **A** | 0–2 | special-card glow no longer flashes when a card un-dims | yes |
| **B** | 3–6 | the redesigned filter bar, working | yes |
| **C** | 7–8 | tests for both, full suite green | yes |

Session A does not touch the filter bar at all and can ship on its own. Session B assumes A is merged only
for the `--t-base-time` token (step 1). Session C assumes B is done.

---

## Context: what exists today

- `resources/views/experience.blade.php` — page markup + an inline IIFE that owns filtering and the masonry layout.
  Cards are rendered into a hidden `#exp-cards-pool`, then **cloned** into `#exp-col-left` / `#exp-col-right`
  by `layoutMasonry()`. `#exp-grid-line` is sized in JS by `updateGridLine()`.
- Filtering never rebuilds the grid — `applyDim()` only toggles `.is-dimmed` on the already-placed clones,
  so the grid never reflows and the dim transition can animate.
- `resources/css/pages/experience.css` — all page CSS. Filter-bar rules live at lines 7–136,
  responsive overrides at lines 493–549.
- Badges come from the DB (`$badges` in `ExperienceController`): each has `slug`, `color`, translated `name`.
- `experiences.type` is `'work'` or `'life'`.

## Scope of the change

| | today | target |
|---|---|---|
| scope filter | two pills (work/life), clicking the active one deselects | segmented control All / Work / Life with a sliding gold thumb |
| search | collapsed behind a round button, matches **title only** | always-visible inline field, matches the card's whole text |
| badge filters | solid filled pills | quiet outline chips with a coloured dot |
| feedback | none | live `n / total` count, `Clear filters` visible only while filtering, empty state |
| hiding | `.is-dimmed` (dim in place, no reflow) | **unchanged — keep dim** |

### Decisions already made — do not revisit

1. **Keep `.is-dimmed`.** The handoff hides non-matching cards with `display: none`; we do not.
   `layoutMasonry()`, `updateGridLine()`, the card cloning and the resize debounce stay **exactly as they are**.
2. **Empty state does not hide the grid.** At zero matches, show `.exp-empty` and leave the (fully dimmed)
   grid in place — hiding it would make the page jump.
3. **`--badge-color`, not `--bc`.** The handoff uses `--bc`; this codebase already uses `--badge-color`
   on `.exp-badge`. Keep `--badge-color` everywhere.
4. **Scope semantics change.** Scope is now single-choice with an explicit `all` default. Cross-group logic
   stays AND (scope AND tags AND query); tags stay OR among themselves.

---

# ══ SESSION A — glow fix (steps 0–2) ══

**Prerequisites:** none.
**Read:** `resources/css/pages/experience.css` lines 176–275 only (the card + special-card block), and the
motion tokens in `resources/css/app.css` around lines 180–190. You do **not** need the Blade file or the
lang files in this session.
**Done when:** dimming and un-dimming a special card shows no gold/white wash over the card face, the halo
eases in only after the card has finished fading back, `npm run build` passes, and
`docker exec portfolio-app-1 php artisan test --compact --filter=Experience` is green.
**Hand off:** report that `--t-base-time` now exists in `app.css` — session B uses nothing else from here.

---

## Step 0 — Pre-flight

1. Confirm the branch: `git status` (work continues on the current design branch; do not create a new one
   unless the user asks).
2. Confirm the motion tokens in `resources/css/app.css`: `--t-fast: 0.3s ease-out` (line ~186),
   `--t-base: 0.5s ease-out` (line ~185).

**Verify:** nothing to run yet.

---

## Step 1 — New duration token

**File:** `resources/css/app.css`, motion block (next to `--t-base`, ~line 185).

`--t-base` is a shorthand of duration **and** easing (`0.5s ease-out`), so it cannot be used as a
`transition-delay`. Add a bare duration next to it:

```css
--t-base-time: 0.5s;   /* bare duration of --t-base, usable as transition-delay */
```

**Verify:** `npm run build` on the host succeeds (not inside the container).

---

## Step 2 — Fix the special-card glow flash

**File:** `resources/css/pages/experience.css`, lines 263–274.

**The bug:** `.exp-card.is-dimmed .exp-card-glow { display: none }` un-hides the glow instantly when a card
un-dims, but the card is still running its 0.5s `opacity`/`filter` transition. While `opacity < 1` or a
`filter` is set, the card is a stacking context, so the glow's `z-index: -1` cannot escape behind it and it
paints **over the card face** — the flash the user reported.

**The fix:** keep the glow hidden for the whole un-dim transition, then fade it in. Replace the
`.exp-card.is-dimmed .exp-card-glow` rule (and its comment) with:

```css
/* The glow must stay invisible while the card is mid-transition: a dimming or
   un-dimming card has opacity/filter set, which makes it a stacking context, and
   the z-index:-1 glow would then paint over the card face instead of behind it.
   So: dim → hide instantly; un-dim → wait out the card's transition, then fade in. */
.exp-card-glow {
    visibility: visible;
    transition:
        opacity var(--t-base) var(--t-base-time),
        visibility 0s var(--t-base-time);
}

.exp-card.is-dimmed .exp-card-glow {
    opacity: 0;
    visibility: hidden;
    transition: opacity 0s, visibility 0s;
}
```

`.exp-card-glow` already declares `opacity: var(--exp-glow-opacity)` at line 258 — leave that line where it
is; the block above only **adds** `visibility` and `transition` to the same selector (merge it into the
existing `.exp-card-glow` rule rather than duplicating the selector).

Then extend the reduced-motion block at lines 269–274:

```css
@media (prefers-reduced-motion: reduce) {
    .exp-card--special,
    .exp-card-glow {
        animation: none;
    }

    .exp-card-glow {
        transition: none;
    }
}
```

**Verify:** `npm run build`, then open http://localhost:8008/experience, click a filter that dims the
special card and click it off again. The halo must be absent while the card fades back in and only then
ease in. No white/gold wash over the card face at any point.

---

# ══ SESSION B — filter bar (steps 3–6) ══

**Prerequisites:** session A done — `--t-base-time` exists in `resources/css/app.css`. If it does not, add it
first (see step 1); nothing else from session A matters here.
**Read:** `resources/views/experience.blade.php` in full, `resources/css/pages/experience.css` lines 1–140
and 493–549, both `resources/lang/{en,cs}/home/experience.php`.
**Do not read or touch** the special-card / glow block (lines ~200–275) — session A owns it.
**Done when:** all the by-hand checks at the end of step 6 pass on http://localhost:8008/experience in both
locales and both themes, and `docker exec portfolio-app-1 php artisan test --compact --filter=Experience`
is green (existing tests only — new tests are session C).
**Hand off:** list the element ids the JS binds to (`exp-filterbar`, `exp-scope`, `exp-scope-thumb`,
`exp-tags`, `exp-clear`, `exp-count`, `exp-empty`, `exp-reset`, `exp-search`) — session C asserts on them.

---

## Step 3 — Language strings

**Files:** `resources/lang/en/home/experience.php`, `resources/lang/cs/home/experience.php`.

Add (keep them next to the existing `title_work` / `title_life` / `search_placeholder` keys):

**en**
```php
'title_all' => 'All',
'tags_label' => 'Tags',
'clear_filters' => 'Clear filters',
'empty' => 'No entries match those filters.',
'reset' => 'Reset',
'count_one' => ':count / :total entry',
'count_few' => ':count / :total entries',
'count_many' => ':count / :total entries',
'search_placeholder' => 'Search titles, places, tech…',   // replaces 'Search by title…'
```

**cs**
```php
'title_all' => 'Vše',
'tags_label' => 'Štítky',
'clear_filters' => 'Zrušit filtry',
'empty' => 'Žádný záznam neodpovídá filtrům.',
'reset' => 'Zrušit filtry',
'count_one' => ':count / :total záznam',
'count_few' => ':count / :total záznamy',
'count_many' => ':count / :total záznamů',
'search_placeholder' => 'Hledat názvy, místa, technologie…',   // replaces 'Hledat podle názvu…'
```

Three separate count keys (not `trans_choice`) because the count is recomputed in JS: the three forms are
handed to the browser as data attributes and JS picks one (Czech needs 1 / 2–4 / 5+; English reuses the
plural for both `few` and `many`).

**Verify:** `docker exec portfolio-app-1 php artisan test --compact --filter=Experience` still green.

---

## Step 4 — Filter-bar markup

**File:** `resources/views/experience.blade.php`, replace lines 18–41 (the whole `<div id="exp-filters">`).

```blade
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
                <span class="exp-tags-label">{{ __('home/experience.tags_label') }}</span>
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
```

Then, immediately **after** the closing `</div>` of `#exp-grid` (currently line 48) add the empty state:

```blade
<p class="exp-empty" id="exp-empty" hidden>
    {{ __('home/experience.empty') }}
    <button type="button" class="exp-empty-reset" id="exp-reset">{{ __('home/experience.reset') }}</button>
</p>
```

Notes:
- The old 24×24 magnifier SVG and `#exp-search-btn` / `#exp-search-wrap` are gone. The new glyph is the
  14px one specified by the handoff (`viewBox="0 0 16 16"`, circle 6.8/6.8/4.6, line 10.4,10.4 → 14,14).
- `#exp-count` is filled by JS; it renders empty on first paint, which is fine (JS runs at the end of the page).
- The tag row and `#exp-clear` only exist when badges exist — the JS in step 6 must null-guard both.

**Verify:** `docker exec portfolio-app-1 php artisan test --compact --filter=ExperiencePage`. The page will
look broken until step 5 — that is expected.

---

## Step 5 — Filter-bar CSS

**File:** `resources/css/pages/experience.css`.

### 5a. Replace lines 7–136

Delete `#exp-filters`, `.exp-filters-group`, `.exp-filter`, `.exp-filter--badge`, `#exp-search-wrap`,
`#exp-search-btn`, `#exp-search` and their state rules. Put this in their place:

```css
/* ── Filter bar ── */

.exp-filterbar {
    display: grid;
    gap: 0.8rem;
    margin-bottom: 2.25rem;
    padding: 0.8rem 1rem;
    background: var(--c-surface);
    border: var(--border-w) solid var(--c-primary-fade);
    border-radius: var(--r-card-sm);
    position: relative;
    z-index: 2;
}

.exp-filterbar-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.6rem 1rem;
}

.exp-filterbar-row + .exp-filterbar-row {
    border-top: 1px solid color-mix(in srgb, var(--c-primary-fade) 55%, transparent);
    padding-top: 0.8rem;
}

/* ── Scope segmented control ── */

.exp-scope {
    position: relative;
    display: inline-flex;
    gap: 0.1rem;
    padding: 0.2rem;
    border-radius: 999px;
    background: var(--c-surface-sunken);
    border: 1px solid color-mix(in srgb, var(--c-primary-fade) 60%, transparent);
}

/* Width and translateX are set from JS off the pressed button's box. */
.exp-scope-thumb {
    position: absolute;
    top: 0.2rem;
    bottom: 0.2rem;
    left: 0.2rem;
    width: 0;
    border-radius: 999px;
    background: var(--c-primary);
    transition: transform var(--t-fast), width var(--t-fast);
}

.exp-scope button {
    position: relative;
    font: inherit;
    font-size: var(--fs-sm);
    font-weight: var(--fw-semibold);
    letter-spacing: 0.08em;
    text-transform: uppercase;
    padding: 0.35rem 1.05rem;
    border: 0;
    border-radius: 999px;
    background: transparent;
    color: var(--c-muted);
    cursor: pointer;
    transition: color var(--t-fast);
}

.exp-scope button:hover {
    color: var(--c-fg);
}

.exp-scope button[aria-pressed="true"] {
    color: var(--c-bg);
}

/* ── Search ── */

.exp-search {
    flex: 1 1 15rem;
    min-width: 11rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.4rem 0.85rem;
    border-radius: 999px;
    background: var(--c-surface-sunken);
    border: 1px solid transparent;
    transition: border-color var(--t-fast);
}

.exp-search:focus-within {
    border-color: var(--c-primary);
}

.exp-search svg {
    flex-shrink: 0;
    width: 14px;
    height: 14px;
    fill: none;
    stroke: var(--c-muted);
    stroke-width: 1.6;
    transition: stroke var(--t-fast);
}

.exp-search:focus-within svg {
    stroke: var(--c-primary);
}

.exp-search input {
    flex: 1;
    min-width: 0;
    border: 0;
    background: transparent;
    color: var(--c-fg);
    font-family: inherit;
    font-size: var(--fs-sm);
    outline: none;
}

.exp-search input::placeholder {
    color: var(--c-muted);
}

/* ── Result count ── */

.exp-count {
    margin-left: auto;
    font-family: ui-monospace, monospace;
    font-size: var(--fs-mini);
    color: var(--c-muted);
    white-space: nowrap;
}

.exp-count b {
    color: var(--c-primary);
    font-weight: var(--fw-semibold);
}

/* ── Badge tag chips ── */

.exp-tags {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.4rem;
    flex: 1;
}

.exp-tags-label {
    font-size: 11px;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--c-muted);
    margin-right: 0.3rem;
}

.exp-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    font-family: inherit;
    font-size: var(--fs-mini);
    padding: 0.25rem 0.75rem 0.25rem 0.6rem;
    border-radius: 999px;
    border: 1px solid color-mix(in srgb, var(--c-primary-fade) 70%, transparent);
    background: transparent;
    color: var(--c-muted);
    cursor: pointer;
    transition: color var(--t-fast), border-color var(--t-fast), background var(--t-fast);
}

.exp-tag::before {
    content: '';
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--badge-color, var(--c-primary));
    transition: box-shadow var(--t-fast);
}

.exp-tag:hover {
    color: var(--c-fg);
    border-color: color-mix(in srgb, var(--badge-color, var(--c-primary)) 55%, transparent);
}

.exp-tag[aria-pressed="true"] {
    color: var(--badge-color, var(--c-primary));
    border-color: color-mix(in srgb, var(--badge-color, var(--c-primary)) 60%, transparent);
    background: color-mix(in srgb, var(--badge-color, var(--c-primary)) 14%, transparent);
}

.exp-tag[aria-pressed="true"]::before {
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--badge-color, var(--c-primary)) 28%, transparent);
}

/* ── Clear ── */

.exp-clear {
    font-family: inherit;
    font-size: var(--fs-mini);
    color: var(--c-muted);
    background: transparent;
    border: 0;
    padding: 0.25rem 0.2rem;
    cursor: pointer;
    opacity: 0;
    pointer-events: none;
    transition: opacity var(--t-fast), color var(--t-fast);
}

.exp-filterbar.has-filters .exp-clear {
    opacity: 1;
    pointer-events: auto;
}

.exp-clear:hover {
    color: var(--c-primary);
}

/* ── Empty state ── */

.exp-empty {
    padding: 2.5rem 0;
    text-align: center;
    font-size: var(--fs-sm);
    color: var(--c-muted);
}

.exp-empty-reset {
    font-family: inherit;
    font-size: inherit;
    color: var(--c-muted);
    background: transparent;
    border: 0;
    padding: 0;
    text-decoration: underline;
    cursor: pointer;
    transition: color var(--t-fast);
}

.exp-empty-reset:hover {
    color: var(--c-primary);
}

@media (prefers-reduced-motion: reduce) {
    .exp-scope-thumb,
    .exp-tag,
    .exp-clear {
        transition: none;
    }
}
```

Stacking note: the global reset gives every element `z-index: 1` (see the comment at line ~232). The thumb
and the scope buttons therefore tie on z-index and paint in source order — the thumb is written first, so the
buttons sit above it. Do not add a `z-index` to either; keep the source order.

### 5b. Responsive block

In `@media (max-width: 992px)` replace the `#exp-filters` / `.exp-filters-group` / `#exp-search-wrap` rules
(lines 514–533) with:

```css
    .exp-scope {
        flex: 1;
        justify-content: center;
    }

    .exp-count {
        margin-left: 0;
    }
```

In `@media (max-width: 576px)` replace the `#exp-filters` and `.exp-filter` rules (lines 537–543) with:

```css
    .exp-filterbar {
        padding: 0.7rem 0.8rem;
    }

    .exp-scope button {
        padding: 0.35rem 0.7rem;
        font-size: var(--fs-mini);
    }
```

Leave the `.exp-card` rule in that block untouched.

**Verify:** `npm run build`; the bar renders as two rows and nothing overflows at 375px, 768px, 1440px.
Filtering does not work yet — that is step 6.

---

## Step 6 — JavaScript

**File:** `resources/views/experience.blade.php`, the `<script>` IIFE (currently lines 117–268).

**Keep unchanged:** `updateGridLine()`, `layoutMasonry()`, the `.is-dimmed` toggling inside `applyDim()`,
the resize debounce, and the final `layoutMasonry()` call.

**Remove:** the `#exp-search-btn` open/close handler and the outside-click handler (currently lines 239–258),
and the old `.exp-filter` click loop (lines 207–235).

Replace the state and handlers with:

```js
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
```

`applyDim()` becomes the single place that reports state — extend it (keep the existing clone loop):

```js
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
```

Handlers:

```js
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
```

Init — extend the existing tail of the IIFE:

```js
layoutMasonry();   // already there; applyDim() runs from it via the clone loop, or call it right after
moveThumb();
applyDim();

// Label widths change once the webfont swaps in, and again on resize.
if (document.fonts) { document.fonts.ready.then(moveThumb); }
```

and add `moveThumb();` inside the existing debounced `resize` handler next to `layoutMasonry()`.

**Verify:** `npm run build`, then on http://localhost:8008/experience check by hand:
- thumb sits under `All` on load and slides to Work / Life with the right width;
- count reads `8 / 8 entries` at rest and drops as filters narrow;
- two tags widen the set (OR), a tag plus Work narrows it (AND);
- `Clear filters` appears only when something is filtered and resets everything;
- zero matches shows the empty state, `Reset` clears;
- switching to Czech (`/language/toggle`) keeps the thumb sized correctly and shows the right plural form.

---

# ══ SESSION C — tests + finish (steps 7–8) ══

**Prerequisites:** sessions A and B done — the filter bar is live and verified by hand.
**Read:** `tests/Feature/ExperiencePageTest.php`, `tests/Browser/PublicPagesTest.php`,
`database/factories/ExperienceFactory.php`, `database/factories/BadgeFactory.php`, and the rendered filter-bar
markup in `resources/views/experience.blade.php` (lines 18–70-ish) — enough to know the selectors.
You do not need the CSS file in this session.
**Done when:** the new tests pass, the full suite is green, and Pint is clean.
**Note:** the ids to assert on are `exp-filterbar`, `exp-scope`, `exp-scope-thumb`, `exp-tags`, `exp-clear`,
`exp-count`, `exp-empty`, `exp-reset`, `exp-search`; card state is the `.is-dimmed` class on cards inside
`#exp-col-left` / `#exp-col-right` (**not** on the hidden `#exp-cards-pool` originals).

---

## Step 7 — Tests

### 7a. `tests/Feature/ExperiencePageTest.php` — add

- the page renders three scope buttons (`data-scope="all|work|life"`);
- every badge attached to an experience renders an `.exp-tag` with its `data-tag` slug and `--badge-color`;
- the count, clear and empty elements are present (`exp-count`, `exp-clear`, `exp-empty`);
- the old markup is gone: assert the response does **not** contain `exp-search-btn`.

Keep the existing special-card / glow tests as they are — that markup did not change.

### 7b. `tests/Browser/ExperienceFilterTest.php` — new file

`php artisan make:test --pest tests/Browser/ExperienceFilterTest.php` and cover, with factories that
create a known mix of `work` / `life` experiences and badges:

- clicking `Work` dims the life cards (assert `.is-dimmed` present on them) and updates the count;
- clicking two tags widens the set (OR);
- typing in the search narrows it, and matches text that is **not** in the title (subtitle or content) —
  this is the behaviour change from title-only search;
- `Clear filters` resets scope, tags and query and hides itself;
- a query that matches nothing shows `#exp-empty`.

`tests/Browser/PublicPagesTest.php` already smoke-tests `/experience` for JS errors — make sure it still passes.

**Verify:**
```bash
docker exec portfolio-app-1 php artisan test --compact --filter=Experience
docker exec portfolio-app-1 php artisan test --compact --filter=PublicPages
```

---

## Step 8 — Finish

1. `docker exec portfolio-app-1 vendor/bin/pint --dirty --format agent`
2. `npm run build` on the **host**
3. `docker exec portfolio-app-1 php artisan test --compact` (full suite)
4. Report what changed; do not commit unless asked.

---

## Out of scope

- No controller, model, migration or seeder changes. `ExperienceController` already provides everything.
- `layoutMasonry()` / `updateGridLine()` / `#exp-grid-line` — untouched.
- `.exp-card--special` frame animation — untouched apart from the glow transition in step 2.
- The handoff's `.exp-hidden { display: none }` and its "hide the grid on empty" behaviour — deliberately
  not adopted (see Decisions 1 and 2).
