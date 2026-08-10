# Plan — footer variants, tools hover, hero rotator colour, reviews carousel

Implementation plan for five UI changes on the public site. Written for a follow-up
session; each task is independent and can be shipped separately.

Key files:

- `resources/views/components/portfolio-footer.blade.php`
- `resources/views/components/portfolio-layout.blade.php`
- `resources/css/app.css` (footer block, ~L489–640)
- `resources/css/pages/index.css` (tools ~L198–277, reviews ~L228–295, hero ~L8–55)
- `resources/js/app.js` (`initHeroRotator()` ~L138, `initScrollReveal()` ~L194)
- `resources/views/welcome.blade.php` (reviews section ~L115)
- `database/seeders/ReviewSeeder.php`

---

## 1. Old footer on desktop, current band on mobile

**Current:** commit `29b10e6` moved `<x-portfolio-footer />` out of `.portfolio-wrapper`
so it is a full-bleed band: `border: 0; border-top: … ; background: var(--c-surface)`,
inner content capped at `--layout-max` and centred.

**Old (pre-`29b10e6`):** footer lived inside `.portfolio-col` (right column, offset by
the sidebar) and was a card: full border, `border-radius: var(--r-card) var(--r-card) 0 0`,
no bottom border, `.portfolio-footer-inner` with no max-width/centring.

**Approach — CSS only, no DOM move.** Moving the element back into the wrapper for
desktop would need duplicated markup or a Blade breakpoint hack; instead keep it where
it is and re-create the old geometry at `min-width: 993px` (the existing desktop
breakpoint — see `@media (max-width: 992px)` in `app.css`).

In `app.css`, after the current footer rules:

```css
/* Desktop: restore the pre-band card footer — aligned to the content column,
   full border, rounded top corners. ≤992px keeps the full-bleed band. */
@media (min-width: 993px) {
    .portfolio-footer-band {
        /* same centring maths as .portfolio-wrapper */
        width: min(100% - var(--layout-padding) * 2, var(--layout-max));
        margin-inline: auto;
    }

    .portfolio-footer {
        border: var(--border-w) solid var(--c-primary-lt);
        border-bottom: 0;
        border-radius: var(--r-card) var(--r-card) 0 0;
        /* skip the sidebar column so the card lines up under the content */
        margin-left: calc(var(--sidebar-w) + var(--content-gap));
    }

    .portfolio-footer-inner {
        max-width: none;
        margin-inline: 0;
    }
}
```

Notes / checks:

- `--sidebar-w` is overridden to `clamp(20rem, 22vw, 24rem)` in the 993–1440px block —
  `calc()` picks that up automatically, so the card tracks the notebook sidebar.
- The watermark `.portfolio-footer-watermark` must stay centred over the *card*, not the
  viewport. Since the band is now narrowed and the watermark is inside it, verify the
  `-0.45em` overlap still reads right; if the watermark looks off-centre, give it the
  same `margin-left` as the footer inside the media query.
- Verify at 1280px, 1440px, 1600px that nothing overflows horizontally (the overflow bug
  `29b10e6` fixed was at notebook widths — re-test 993–1200px specifically). If the old
  card overflows there, restrict the restore to `min-width: 1441px` and leave the band
  on notebook tier; state that choice in the commit message.

---

## 2. Remove tools hover effect

`resources/css/pages/index.css` L268–272 — delete the whole rule:

```css
.tools-row-card:hover {
    translate: 0 -6px;
    scale: 1.03;
    box-shadow: 0 12px 24px -10px …;
}
```

Leave the shared entrance-transition block (L251–266) intact: `.tools-row-card` still
needs `opacity`/`translate` for scroll reveal. `scale`/`box-shadow` in its `transition`
list become inert — harmless, but they may be dropped from the `.tools-row-card` side if
the selector list is split. Do not touch `.reviews-row-card:hover`.

---

## 3. Footer nav links get the "Follow me" hover treatment

Social links (`app.css` L591–609) have: icon `transition: filter, transform`, and on
hover `transform: translateY(-2px)` + gold `drop-shadow`, plus the label going
`var(--c-primary)`.

Nav links currently only recolour the label. Add the icon half:

```css
.portfolio-footer-nav-link img {
    height: 1.4rem;
    border-radius: 0.3rem;
    transition: filter var(--t-fast), transform var(--t-fast);
}

.portfolio-footer-nav-link:hover img {
    transform: translateY(-2px);
    filter: drop-shadow(0 0 8px color-mix(in srgb, var(--c-primary) 70%, transparent));
}
```

Preferred: merge the two into shared selector lists
(`.portfolio-footer-nav-link img, .portfolio-footer-social-link img { … }`) so the two
groups can't drift again.

---

## 4. Hero rotator: white by default, gold only inside tags

**Current:** `index.css` L36–40 paints all of `#hero-rotator` gold. `initHeroRotator()`
(`app.js` L142–192) types with `el.textContent = text.slice(0, i)` — plain text only.
Role strings in `settings.hero_roles` are plain (`"Full-stack developer"`, …).

Target behaviour matches `hero_title`, which is rendered with `{!! !!}` and whose
`<span>` is gold via `app.css` L320 (`.portfolio-page h1 span`).

**Steps:**

1. **CSS** — drop `#hero-rotator` from the gold rule, keep it for `.underh1 span` that
   are *inside* the rotator:

   ```css
   .underh1 > span#hero-rotator { color: var(--c-text); font-weight: var(--fw-semibold); }
   #hero-rotator span { color: var(--c-primary); }
   ```

   Check the actual default text token name in the `@theme` block of `app.css`
   (`--c-text` / `--c-fg`) — use whatever the page body already uses; do not hardcode
   `#fff`, light theme must stay readable. `.hero-caret` stays gold.

2. **Blade** — `welcome.blade.php` L12 renders the initial role with `{{ }}`; switch to
   `{!! !!}` so markup in the first role isn't escaped. `data-roles='@json($heroRoles)'`
   already carries raw strings through; keep the single-quoted attribute.

3. **JS** — the typewriter must type *through* markup. Replace the `textContent` slicing
   with a per-role token list built once:

   - Parse the role HTML into a flat list of `{char, tagPath}` by walking a detached
     `template` element's DOM, or simpler: pre-split each role into segments
     `[{text, gold: bool}, …]` (gold = the char sits inside any element), then render
     `i` characters as: for each segment, take its slice and wrap gold segments in
     `<span>`; assign with `el.innerHTML`.
   - Only site-authored settings feed this, but still restrict the allowed tags: build
     the output from the parsed segments (text is re-escaped, wrapper is always a plain
     `<span>`), never `innerHTML = rawSlice`. That keeps admin-entered content from
     injecting arbitrary markup into the hero.
   - `erase()` uses the same renderer with a decreasing `i`.
   - Char counting must be over *visible characters*, not raw HTML length, or the typing
     speed will stutter on tag boundaries.

4. **Content** — add markup to the seeded roles so the effect is visible, e.g.
   `"Full-stack <span>developer</span>"`, `"Laravel <span>craftsman</span>"` in
   `SettingSeeder` (`hero_roles`, both `en` and `cs`). Values are also editable from the
   settings admin page, so keep them valid HTML.

5. **Reduced motion** — `initHeroRotator()` bails on `prefers-reduced-motion`, leaving
   the Blade-rendered first role visible; that path now shows markup correctly thanks to
   step 2.

---

## 5. Reviews carousel (3 visible, wheel/looping)

**Current:** `welcome.blade.php` L115–127 renders every review into `.reviews-row`
(a 3-up flex row). DB has 3 rows. `Review` has `sort_order`; `HomeController::__invoke`
does `Review::orderBy('sort_order')->get()`.

### 5a. More reviews

Extend `database/seeders/ReviewSeeder.php` to ~7–9 entries (it already
`Review::query()->delete()`s first, so re-seeding is safe). Follow
`docs/content-skeleton/04-reviews.md` for the shape: `name`, `position` `{cs,en}`,
`text` `{cs,en}`, `sort_order`.

> **Assumption to confirm:** new reviews will be written as plausible placeholder
> testimonials (colleagues/clients, both languages). They are seed content the site
> owner edits in `/dashboard/reviews` — they are not presented as verified quotes from
> real named people beyond the ones already seeded. If real quotes exist, hand them over
> instead of inventing names.

### 5b. Carousel

Markup (`welcome.blade.php`):

```blade
<section id="reviews" class="portfolio-section">
    <h2>…</h2>
    <div class="reviews-carousel" data-reviews-carousel>
        <button class="reviews-carousel-arrow" data-dir="-1" aria-label="…">‹</button>
        <div class="reviews-carousel-viewport">
            <article class="reviews-row" role="list">
                @foreach ($reviews as $review) … @endforeach
            </article>
        </div>
        <button class="reviews-carousel-arrow" data-dir="1" aria-label="…">›</button>
        <div class="reviews-carousel-dots" aria-hidden="true"></div>
    </div>
</section>
```

Keep the existing `.reviews-row` / `.reviews-row-card` classes so card styling, hover
lift and scroll reveal survive.

CSS (`index.css`):

- `.reviews-carousel-viewport { overflow: hidden; }`
- `.reviews-row` becomes the track: `display: flex; gap: 1.25rem;
  transition: translate var(--t-base);` (use `translate`, not `transform` — the cards
  already reserve `translate` for reveal, but the *track* is a different element, so no
  conflict).
- Card width from the per-view count:
  `.reviews-row-card { flex: 0 0 calc((100% - 2 * 1.25rem) / 3); max-width: none; }`
- Responsive: 3 per view ≥993px, 2 at 577–992px, 1 ≤576px — replace the existing
  wrap/column rules at L305–307 and L333–334 with per-view flex-basis overrides.
- Arrows: absolutely positioned, gold border/`--c-surface` fill, hover = the same lift
  used elsewhere; hide with `display: none` when the carousel fits (`.is-static`).
- Respect `prefers-reduced-motion`: no autoplay, no slide transition.

JS (`app.js`, new `initReviewsCarousel()` called from `DOMContentLoaded` alongside the
other inits):

- Read per-view from a `matchMedia` check (or `getComputedStyle` on a card) and
  recompute on `resize` (debounced).
- **Looping ("wheel"):** move by one *page*; when the index passes the end, wrap to 0.
  Simple index wrap + `translate: calc(-1 * index * 100%)` is enough and avoids
  clone-management bugs; only do clone-based infinite scrolling if a seamless (no
  snap-back) loop is explicitly wanted.
- Autoplay every ~6s, paused on `pointerenter`, on `focusin`, and when
  `document.hidden`.
- Keyboard: left/right arrows on the focused carousel; arrow buttons are real
  `<button>`s so they're reachable by tab.
- Touch: horizontal swipe via `pointerdown/up` delta threshold.
- Dots: one per page, `aria-current` on the active one.
- **Scroll reveal interaction:** `initScrollReveal()` sets `--i` on `.reviews-row`
  children and observes `.reviews-row-card`. Off-screen cards inside the viewport never
  intersect, so they'd stay at `opacity: 0` when scrolled into view. Fix by observing the
  carousel container instead and adding `.is-visible` to all its cards at once, or by
  dropping the per-card observer for reviews and revealing the whole track.
- `livewire:navigated` — check how the other inits are re-run after SPA nav (see the
  `applyTheme` listener at `app.js` L378) and register the carousel the same way.

---

## Testing

Every change needs a test (`docker exec portfolio-app-1 php artisan test --compact`):

- Feature: home page renders N reviews and the carousel wrapper (`tests/Feature/…`).
- Feature: `hero_roles` markup survives to the page unescaped.
- Browser (`tests/Browser/`, Pest 4 + Playwright, runs in the container): carousel
  advances on arrow click; footer card variant present at desktop width and band at
  mobile width.
- Run `vendor/bin/pint --dirty --format agent` after touching PHP.
- Rebuild assets on the **host**: `npm run build` (or `npm run dev`).

## Suggested commit split

1. `feat(footer): restore card footer on desktop, keep band on mobile`
2. `fix(tools): remove hover lift on tool cards`
3. `feat(footer): match nav link hover to social link hover`
4. `feat(hero): white rotator text with gold accents inside tags`
5. `feat(reviews): 3-up looping carousel` (+ seeder content)
