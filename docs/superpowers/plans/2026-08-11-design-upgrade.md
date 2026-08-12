# Design Upgrade Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Turn the findings in `docs/design-upgrade-ideas.md` into shipped design changes on the four public pages — page openers, watermark legibility, a real light theme, and per-page layout fixes — without depending on content that does not exist yet.

**Architecture:** Three layers, in order. (1) *Foundations* — fix the global watermark/heading recipe and the light-theme palette in `resources/css/app.css`, because every page inherits them. (2) *Page opener* — a new shared `<x-portfolio.page-hero>` Blade component plus `resources/css/components/page-hero.css`, adopted by About me / Experience / Projects; all its facts are **derived from the database at render time** and every part of it is optional so it degrades to a bare title when data is missing. (3) *Page tweaks* — targeted CSS/JS/Blade edits per page. Verification is by Pest browser tests that measure real geometry and computed styles (the pattern already used in `tests/Browser/PublicPagesTest.php`), not by screenshots.

**Tech Stack:** Laravel 13 / PHP 8.5, Blade components, Livewire 4 (admin only), Tailwind v4 + hand-written CSS with `@theme` tokens, Vite, Pest 4 + `pestphp/pest-plugin-browser` (Playwright/chromium in the container), PostgreSQL 17, Docker.

## Global Constraints

- **The site's data is incomplete.** No new UI element may hardcode a count, a year, a name, or a label that assumes a specific record exists. Everything the page-hero shows is derived from the DB and rendered only when non-empty. Every new Blade prop except `title` is optional.
- All Artisan/Pint/test commands run in the container: `docker exec portfolio-app-1 <cmd>`.
- Vite builds run **on the host**: `npm run build`. Browser tests read built assets — run `npm run build` on the host after any CSS/JS change and before running browser tests.
- Colors are declared once in the `@theme` block of `resources/css/app.css`. The `--c-*` names in `:root` are aliases and must never be given a value of their own. Light theme overrides the same `--color-*` variables inside `html:not(.dark)`.
- New page-level CSS entry files must be added to the `input` array in `vite.config.js`. **Component** CSS is instead `@import`-ed from the page CSS that needs it (the pattern `resources/css/components/project-row.css` already uses) — prefer this; it needs no Vite change.
- Flux is the **free** tier. No Pro components (no `flux:tabs`).
- After editing any PHP file: `docker exec portfolio-app-1 vendor/bin/pint --dirty --format agent`.
- Translatable JSON columns must be asserted with `toEqual`, never `toBe` (Postgres `jsonb` reorders keys).
- Run tests narrowly: `docker exec portfolio-app-1 php artisan test --compact --filter='<name>'`.
- Static UI copy goes in `resources/lang/en/...` **and** `resources/lang/cs/...`. Editable copy goes in `settings` via the `Setting` model and the `⚡site-content` manage page.
- Commit after every task.

---

## File Structure

**Created**

| File | Responsibility |
|---|---|
| `resources/views/components/portfolio/page-hero.blade.php` | Shared page opener: eyebrow, title, subtitle, meta chips, optional photo, slot for page-specific controls |
| `resources/css/components/page-hero.css` | All page-hero styling; `@import`-ed by the three page CSS files |
| `resources/lang/en/pages/hero.php`, `resources/lang/cs/pages/hero.php` | Static page-hero copy (eyebrows, titles, meta label formats) |
| `tests/Browser/DesignSystemTest.php` | Geometry/computed-style tests for the global heading + light-theme rules |
| `tests/Browser/PageHeroTest.php` | Page-hero rendering + degradation tests |
| `tests/Feature/PageHeroDataTest.php` | Controller-level tests for the derived meta values |

**Modified**

| File | Change |
|---|---|
| `resources/css/app.css` | Watermark h2 recipe, mobile `--fs-h2`, light-theme watermark + h1 accent, `.portfolio-section--lead` |
| `resources/views/{about-me,experience,projects}.blade.php` | Adopt `<x-portfolio.page-hero>`; drop inline `padding-top` |
| `app/Http/Controllers/{AboutMe,Experience,Projects}Controller.php` | Derive hero meta from the DB |
| `resources/views/pages/manage/⚡site-content.blade.php` | Optional hero-subtitle settings |
| `database/seeders/SettingSeeder.php` | Seed the three hero subtitles |
| `config/portfolio.php` | `portrait` image path |
| `resources/css/pages/experience.css` | Continuous timeline spine |
| `resources/css/components/project-row.css` | Uniform screenshot frame, tighter rows |
| `resources/css/pages/projects.css` | Year-numeral overlap cap, halved row gap |
| `resources/css/pages/index.css` | Hero photo treatment, Work/Life pill, review body size, tools grid |
| `resources/js/app.js` | Page-based carousel steps |
| `resources/views/components/mobile-nav.blade.php` | Brand mark next to the hamburger |

---

## Phase A — Foundations

### Task 1: Watermark headings stop eating letters

Fixes `docs/design-upgrade-ideas.md` §2. Uppercasing the watermark removes descenders entirely, so the deliberate overlap can stay dramatic without swallowing a `y`, `j` or `p`. The overlap is also reduced, and `--fs-h2` gets a mobile ceiling so single long words ("PROJEKTANT-PATA") stop clipping at 390px.

**Files:**
- Modify: `resources/css/app.css:152` (the `--fs-h2` token), `resources/css/app.css:328-337` (the `.portfolio-page h2` rule)
- Test: `tests/Browser/DesignSystemTest.php` (create)

**Interfaces:**
- Consumes: nothing.
- Produces: `.portfolio-page h2` is `text-transform: uppercase` with `margin-bottom: -0.25em`; a `@media (max-width: 576px)` block redefines `--fs-h2` on `:root`. Later tasks assume watermarks are uppercase and no longer clipped.

- [x] **Step 1: Write the failing test**

Create `tests/Browser/DesignSystemTest.php`:

```php
<?php

test('watermark headings do not swallow their descenders', function () {
    $page = visit('/')->resize(1440, 900);

    $metrics = $page->script(<<<'JS'
        (() => {
            const h2 = document.querySelector('#stats h2');
            const card = document.querySelector('#stats .stats-cards');
            const style = getComputedStyle(h2);
            return {
                transform: style.textTransform,
                overlapRatio:
                    (h2.getBoundingClientRect().bottom - card.getBoundingClientRect().top)
                    / parseFloat(style.fontSize),
            };
        })()
    JS);

    expect($metrics['transform'])->toBe('uppercase')
        ->and($metrics['overlapRatio'])->toBeLessThan(0.35);
});

test('watermark headings stay inside the viewport on mobile', function () {
    $page = visit('/')->resize(390, 844);

    $overflowing = $page->script(<<<'JS'
        [...document.querySelectorAll('.portfolio-page h2')].filter((h) => {
            const r = h.getBoundingClientRect();
            return r.left < -1 || r.right > document.documentElement.clientWidth + 1;
        }).length
    JS);

    expect($overflowing)->toBe(0);
});

test('the footer watermark keeps its descenders above the footer card', function () {
    $page = visit('/')->resize(1440, 900);

    $clipped = $page->script(<<<'JS'
        (() => {
            const mark = document.querySelector('.portfolio-footer-watermark');
            const footer = document.querySelector('.portfolio-footer');
            return (mark.getBoundingClientRect().bottom - footer.getBoundingClientRect().top)
                / parseFloat(getComputedStyle(mark).fontSize);
        })()
    JS);

    expect($clipped)->toBeLessThan(0.35);
});
```

- [x] **Step 2: Run the test and verify it fails**

```bash
npm run build
docker exec portfolio-app-1 php artisan test --compact --filter='watermark'
```

Expected: FAIL — `transform` is `none` and `overlapRatio` is ≈ 0.45.

- [x] **Step 3: Change the watermark recipe**

In `resources/css/app.css`, replace the `--fs-h2` token line (currently line 152) with:

```css
    --fs-h2:    clamp(2.6rem, 1rem    + 8vw,   6.56rem);  /* watermark h2  */
```

(unchanged — the mobile override below is what handles narrow screens), then replace the whole `.portfolio-page h2` rule (currently lines 325-337) with:

```css
/* Giant watermark heading used as section label — same outline treatment.
   The overlap is set in `em` so it scales with the fluid font size — a
   fixed -50px swallowed the whole label under the card on small screens.
   Uppercase removes descenders altogether, so the overlap can stay bold
   without clipping the `y` in "My Stats" or the `j`/`p` in the footer name. */
.portfolio-page h2 {
    text-align: center;
    margin-bottom: -0.25em;
    font-size: var(--fs-h2);
    font-weight: 500;
    font-family: var(--font-display);
    text-transform: uppercase;
    letter-spacing: 0.01em;
    color: color-mix(in srgb, var(--c-primary-lt) 12%, transparent);
    -webkit-text-stroke: 1px var(--c-primary-lt);
    overflow-wrap: anywhere;
}

/* Narrow screens: a single long watermark word ("PROJEKTANT-PATA") overflows
   the viewport at the fluid size, so cap it here instead of flattening the
   desktop scale. */
@media (max-width: 576px) {
    :root {
        --fs-h2: clamp(1.6rem, 0.4rem + 6vw, 2.75rem);
    }
}
```

- [x] **Step 4: Run the test and verify it passes**

```bash
npm run build
docker exec portfolio-app-1 php artisan test --compact --filter='watermark'
```

Expected: PASS (3 tests).

- [x] **Step 5: Run the existing public-page suite for regressions**

```bash
docker exec portfolio-app-1 php artisan test --compact --filter='PublicPages'
```

Expected: PASS.

- [x] **Step 6: Commit**

```bash
git add resources/css/app.css tests/Browser/DesignSystemTest.php
git commit -m "fix(design): uppercase watermark headings and cap their overlap"
```

---

### Task 2: Light theme gets its own watermark and accent recipe

Fixes §3. In light mode the watermark currently renders as solid amber-400 (`app.css:221-224`) — a heavy filled headline on cream that inverts the visual hierarchy. Light gets a stroke-first recipe of its own, and the h1 accent word switches to the darker light-mode gold so "projektant-pata" keeps its identity instead of washing out to tan.

**Files:**
- Modify: `resources/css/app.css:220-224` (light watermark override), add a light `h1 span` override directly beneath it
- Test: `tests/Browser/DesignSystemTest.php` (append)

**Interfaces:**
- Consumes: the uppercase `.portfolio-page h2` from Task 1.
- Produces: `html:not(.dark) .portfolio-page h2` keeps a non-zero `-webkit-text-stroke`; `html:not(.dark) .portfolio-page h1 span` exists.

- [x] **Step 1: Write the failing test**

Append to `tests/Browser/DesignSystemTest.php`:

```php
test('light mode renders the watermark as an outline, not a filled headline', function () {
    $page = visit('/')->resize(1440, 900);

    $light = $page->script(<<<'JS'
        (() => {
            document.documentElement.classList.remove('dark');
            const style = getComputedStyle(document.querySelector('#stats h2'));
            const fillAlpha = (style.color.match(/[\d.]+\)$/) || ['1)'])[0].slice(0, -1);
            return {
                strokeWidth: parseFloat(style.webkitTextStrokeWidth),
                fillAlpha: parseFloat(fillAlpha),
            };
        })()
    JS);

    expect($light['strokeWidth'])->toBeGreaterThan(0)
        ->and($light['fillAlpha'])->toBeLessThan(0.25);
});

test('light mode keeps a gold outline on the hero accent word', function () {
    $page = visit('/')->resize(1440, 900);

    $strokeWidth = $page->script(<<<'JS'
        (() => {
            document.documentElement.classList.remove('dark');
            return parseFloat(
                getComputedStyle(document.querySelector('.hero-page-text h1 span')).webkitTextStrokeWidth
            );
        })()
    JS);

    expect($strokeWidth)->toBeGreaterThan(0);
});
```

- [x] **Step 2: Run the test and verify it fails**

```bash
npm run build
docker exec portfolio-app-1 php artisan test --compact --filter='light mode'
```

Expected: FAIL — `strokeWidth` is `0` (the light override sets `-webkit-text-stroke: 0`).

- [x] **Step 3: Rewrite the light-theme heading overrides**

In `resources/css/app.css`, replace the block currently at lines 220-224:

```css
/* h2 watermark: solid amber fill in light mode — stroke is too faint on parchment */
html:not(.dark) .portfolio-page h2 {
    color: var(--c-primary-lt);
    -webkit-text-stroke: 0;
}
```

with:

```css
/* h2 watermark, light mode: amber-400 as a solid fill turned the watermark into
   the loudest thing on the page. Outline in the darker amber-700 instead, with a
   whisper of fill — same "engraved" read as dark mode, at a fraction of the weight. */
html:not(.dark) .portfolio-page h2 {
    color: color-mix(in srgb, var(--c-primary) 8%, transparent);
    -webkit-text-stroke: 1.5px color-mix(in srgb, var(--c-primary) 45%, transparent);
}

/* h1 accent word, light mode: the 16% fill of the dark recipe reads as washed
   tan on parchment. Amber-700 at a higher fill keeps the gold identity. */
html:not(.dark) .portfolio-page h1 span {
    color: color-mix(in srgb, var(--c-primary) 24%, transparent);
    -webkit-text-stroke: 2px var(--c-primary);
}
```

- [x] **Step 4: Run the test and verify it passes**

```bash
npm run build
docker exec portfolio-app-1 php artisan test --compact --filter='light mode'
```

Expected: PASS (2 tests).

- [x] **Step 5: Commit**

```bash
git add resources/css/app.css tests/Browser/DesignSystemTest.php
git commit -m "fix(design): give light mode its own watermark and accent recipe"
```

---

### Task 3: One section-rhythm rule instead of three inline paddings

Fixes §8's "section vertical rhythm varies". `about-me`, `experience` and `projects` each carry a hand-written `style="padding-top: var(--sp-section)"` on their first section. Replace it with a modifier class so Phase B can drop the inline style when the page-hero takes over the top of the page.

**Files:**
- Modify: `resources/css/app.css:416-419` (the `.portfolio-section` rule)
- Modify: `resources/views/about-me.blade.php:6`, `resources/views/experience.blade.php:5`, `resources/views/projects.blade.php:4`
- Test: `tests/Browser/DesignSystemTest.php` (append)

**Interfaces:**
- Consumes: nothing.
- Produces: CSS class `.portfolio-section--lead` (top padding of `var(--sp-section)`), used by any page whose first element is a section rather than the home hero.

- [x] **Step 1: Write the failing test**

Append to `tests/Browser/DesignSystemTest.php`:

```php
test('subpages open with the shared section rhythm and no inline padding', function () {
    foreach (['/about-me', '/experience', '/projects'] as $url) {
        $page = visit($url)->resize(1440, 900);

        $lead = $page->script(<<<'JS'
            (() => {
                const first = document.querySelector('.portfolio-main > *');
                return {
                    hasClass: first.classList.contains('portfolio-section--lead'),
                    inlinePadding: first.style.paddingTop,
                };
            })()
        JS);

        expect($lead['hasClass'])->toBeTrue()
            ->and($lead['inlinePadding'])->toBe('');
    }
});
```

- [x] **Step 2: Run the test and verify it fails**

```bash
npm run build
docker exec portfolio-app-1 php artisan test --compact --filter='shared section rhythm'
```

Expected: FAIL — `hasClass` is `false`.

- [x] **Step 3: Add the modifier class**

In `resources/css/app.css`, replace the `.portfolio-section` rule (currently lines 416-419) with:

```css
/* ── Section spacing ── */
.portfolio-section {
    margin-bottom: var(--sp-section);
}

/* First section on a page that has no full-viewport hero — it needs the same
   gap above it that every other section gets below it. */
.portfolio-section--lead {
    padding-top: var(--sp-section);
}
```

- [x] **Step 4: Swap the inline styles for the class**

`resources/views/about-me.blade.php` line 6:

```blade
    <section id="about-me" class="portfolio-section portfolio-section--lead">
```

`resources/views/experience.blade.php` line 5:

```blade
    <section id="experience" class="portfolio-section portfolio-section--lead">
```

`resources/views/projects.blade.php` line 4:

```blade
    <section id="projects" class="portfolio-section portfolio-section--lead">
```

- [x] **Step 5: Run the test and verify it passes**

```bash
npm run build
docker exec portfolio-app-1 php artisan test --compact --filter='shared section rhythm'
```

Expected: PASS.

- [x] **Step 6: Commit**

```bash
git add resources/css/app.css resources/views/about-me.blade.php resources/views/experience.blade.php resources/views/projects.blade.php
git commit -m "refactor(design): replace inline lead-section padding with a modifier class"
```

---

## Phase B — Page openers

### Task 4: The `page-hero` component and its styles

Fixes §1's structural half. This task builds the component and proves it degrades: with only a `title` it renders a title and nothing else — which is exactly what incomplete data will hand it.

**Files:**
- Create: `resources/views/components/portfolio/page-hero.blade.php`
- Create: `resources/css/components/page-hero.css`
- Create: `resources/lang/en/pages/hero.php`, `resources/lang/cs/pages/hero.php`
- Modify: `resources/css/pages/about-me.css:1`, `resources/css/pages/experience.css:1`, `resources/css/pages/projects.css:1` (add the `@import`)
- Create: `tests/Browser/PageHeroTest.php`

**Interfaces:**
- Consumes: `.portfolio-section--lead` from Task 3.
- Produces: `<x-portfolio.page-hero>` with props `eyebrow` (?string, default null), `title` (string, **required**, rendered unescaped so it may carry a `<span>` accent), `subtitle` (?string, default null), `meta` (array<int, string>, default `[]`), `photo` (?string asset path, default null), `photoAlt` (string, default `''`), plus a default slot rendered under the meta row. CSS class names later tasks target: `.page-hero`, `.page-hero-eyebrow`, `.page-hero-title`, `.page-hero-subtitle`, `.page-hero-meta`, `.page-hero-meta-item`, `.page-hero-photo`, `.page-hero-extra`.
- Lang keys produced: `pages/hero.about_eyebrow`, `about_title`, `experience_eyebrow`, `experience_title`, `projects_eyebrow`, `projects_title`, `meta_entries`, `meta_span`, `meta_work`, `meta_life`, `meta_projects`.

- [x] **Step 1: Write the failing test**

Create `tests/Browser/PageHeroTest.php`:

```php
<?php

test('the page hero renders every part when it is given data', function () {
    $page = visit('/about-me')->resize(1440, 900);

    $hero = $page->script(<<<'JS'
        (() => {
            const hero = document.querySelector('.page-hero');
            if (!hero) { return null; }
            return {
                eyebrow: !!hero.querySelector('.page-hero-eyebrow'),
                title: (hero.querySelector('.page-hero-title')?.textContent || '').trim(),
                titleIsSolid: getComputedStyle(hero.querySelector('.page-hero-title')).webkitTextStrokeWidth,
                height: hero.getBoundingClientRect().height,
                viewport: window.innerHeight,
            };
        })()
    JS);

    expect($hero)->not->toBeNull()
        ->and($hero['eyebrow'])->toBeTrue()
        ->and($hero['title'])->not->toBe('')
        ->and($hero['titleIsSolid'])->toBe('0px')
        ->and($hero['height'] / $hero['viewport'])->toBeLessThan(0.55);
});

test('the page hero degrades to a bare title when the optional parts are missing', function () {
    $rendered = Blade::render('<x-portfolio.page-hero title="Just a title" />');

    expect($rendered)->toContain('page-hero-title')
        ->and($rendered)->not->toContain('page-hero-eyebrow')
        ->and($rendered)->not->toContain('page-hero-subtitle')
        ->and($rendered)->not->toContain('page-hero-meta')
        ->and($rendered)->not->toContain('page-hero-photo');
})->group('unit');
```

Add the `Blade` import at the top of the file:

```php
use Illuminate\Support\Facades\Blade;
```

- [x] **Step 2: Run the test and verify it fails**

```bash
docker exec portfolio-app-1 php artisan test --compact --filter='page hero'
```

Expected: FAIL — the component does not exist; the first test gets `null`.

- [x] **Step 3: Create the component**

`resources/views/components/portfolio/page-hero.blade.php`:

```blade
@props([
    'eyebrow' => null,
    'title',
    'subtitle' => null,
    'meta' => [],
    'photo' => null,
    'photoAlt' => '',
])

{{--
    Shared opener for the subpages. Everything except the title is optional and
    is skipped when empty, so a half-filled database still renders a clean hero
    instead of empty chips. `title` is echoed unescaped so callers can wrap an
    accent word in <span> the way the home hero does.
--}}
<header class="page-hero">
    <div class="page-hero-text">
        @if ($eyebrow)
            <p class="page-hero-eyebrow">{{ $eyebrow }}</p>
        @endif

        <h1 class="page-hero-title">{!! $title !!}</h1>

        @if ($subtitle)
            <p class="page-hero-subtitle">{{ $subtitle }}</p>
        @endif

        @if (! empty($meta))
            <ul class="page-hero-meta">
                @foreach ($meta as $item)
                    <li class="page-hero-meta-item">{{ $item }}</li>
                @endforeach
            </ul>
        @endif

        @if (! $slot->isEmpty())
            <div class="page-hero-extra">{{ $slot }}</div>
        @endif
    </div>

    @if ($photo)
        <div class="page-hero-photo">
            <img src="{{ asset($photo) }}" alt="{{ $photoAlt }}">
        </div>
    @endif
</header>
```

- [x] **Step 4: Create the component stylesheet**

`resources/css/components/page-hero.css`:

```css
/* ================================================================
   PAGE HERO COMPONENT
   Opener for the subpages (about-me / experience / projects). Pairs
   with resources/views/components/portfolio/page-hero.blade.php.
   Deliberately short — content must stay one small scroll away.
   ================================================================ */

.page-hero {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 3rem;
    min-height: 32vh;
    margin-bottom: 2.5rem;
}

.page-hero-text {
    flex: 1;
    min-width: 0;
}

/* Same mono-ish small-caps eyebrow the home hero uses for "HELLO WORLD!" */
.page-hero-eyebrow {
    font-size: var(--fs-base);
    font-weight: var(--fw-regular);
    color: var(--c-muted);
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: 1.2rem;
}

/* Solid, readable page title — deliberately NOT the outlined watermark
   treatment, which stays on the h2 section labels below. */
.page-hero-title {
    font-size: clamp(2.2rem, 1.4rem + 3.2vw, 3.4rem);
    line-height: 1.05;
}

.page-hero-title span {
    color: var(--c-primary);
    -webkit-text-stroke: 0;
}

.page-hero-subtitle {
    margin-top: 1rem;
    max-width: 46ch;
    font-size: var(--fs-h4);
    font-weight: var(--fw-light);
    color: var(--c-muted);
    line-height: 1.45;
}

.page-hero-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 1.6rem;
}

.page-hero-meta-item {
    font-size: var(--fs-mini);
    padding: 0.3rem 0.85rem;
    border-radius: 999px;
    border: var(--border-w) solid color-mix(in srgb, var(--c-primary) 35%, transparent);
    background: color-mix(in srgb, var(--c-primary) 10%, transparent);
    color: var(--c-primary);
    white-space: nowrap;
}

.page-hero-extra {
    margin-top: 2rem;
}

/* Photo gets the site's card treatment so it reads as a framed portrait
   rather than a rectangle floating in space. */
.page-hero-photo {
    flex: 0 0 auto;
}

.page-hero-photo img {
    display: block;
    width: clamp(180px, 18vw, 260px);
    aspect-ratio: 3 / 4;
    object-fit: cover;
    border-radius: var(--r-card);
    border: var(--border-w) solid var(--c-primary-lt);
    box-shadow: 0 18px 40px -24px color-mix(in srgb, var(--c-primary) 60%, transparent);
}

@media (max-width: 992px) {
    .page-hero {
        flex-direction: column-reverse;
        align-items: flex-start;
        gap: 1.75rem;
        min-height: 0;
    }

    .page-hero-photo img {
        width: clamp(140px, 40vw, 200px);
    }
}
```

- [x] **Step 5: Import the stylesheet from the three page CSS files**

Add as the **first** line of `resources/css/pages/about-me.css` and `resources/css/pages/experience.css`:

```css
@import '../components/page-hero.css';
```

In `resources/css/pages/projects.css` add it directly under the existing import so the file starts:

```css
@import '../components/project-row.css';
@import '../components/page-hero.css';
```

- [x] **Step 6: Create the language files**

`resources/lang/en/pages/hero.php`:

```php
<?php

return [
    'about_eyebrow' => 'Who am I',
    'about_title' => 'About <span>me</span>',
    'experience_eyebrow' => 'Where I’ve been',
    'experience_title' => 'My <span>experience</span>',
    'projects_eyebrow' => 'What I’ve built',
    'projects_title' => 'Selected <span>projects</span>',

    'meta_entries' => ':count entries',
    'meta_span' => ':from – :to',
    'meta_work' => ':count work',
    'meta_life' => ':count life',
    'meta_projects' => ':count projects',
];
```

`resources/lang/cs/pages/hero.php`:

```php
<?php

return [
    'about_eyebrow' => 'Kdo jsem',
    'about_title' => 'O <span>mně</span>',
    'experience_eyebrow' => 'Kde jsem byl',
    'experience_title' => 'Moje <span>zkušenosti</span>',
    'projects_eyebrow' => 'Co jsem postavil',
    'projects_title' => 'Vybrané <span>projekty</span>',

    'meta_entries' => 'záznamů: :count',
    'meta_span' => ':from – :to',
    'meta_work' => 'práce: :count',
    'meta_life' => 'život: :count',
    'meta_projects' => 'projektů: :count',
];
```

- [x] **Step 7: Wire a minimal hero into the About page so the browser test has something to measure**

In `resources/views/about-me.blade.php`, insert directly above the `<section id="about-me" ...>` line:

```blade
    <x-portfolio.page-hero
        :eyebrow="__('pages/hero.about_eyebrow')"
        :title="__('pages/hero.about_title')"
    />
```

and drop `portfolio-section--lead` from that section — the hero now opens the page, so the section line becomes:

```blade
    <section id="about-me" class="portfolio-section">
```

Give sections that follow a hero their own smaller gap by appending to `resources/css/components/page-hero.css`:

```css
/* A section that follows a page-hero needs less air above it than a lead
   section, because the hero already opened the page. */
.page-hero + .portfolio-section {
    padding-top: 2rem;
}
```

- [x] **Step 8: Run the test and verify it passes**

```bash
npm run build
docker exec portfolio-app-1 php artisan test --compact --filter='page hero'
```

Expected: PASS (2 tests).

- [x] **Step 9: Run the About-page feature tests for regressions**

```bash
docker exec portfolio-app-1 php artisan test --compact --filter='AboutMePage'
```

Expected: PASS.

- [x] **Step 10: Commit**

```bash
git add resources/views/components/portfolio/page-hero.blade.php resources/css/components/page-hero.css resources/css/pages resources/lang resources/views/about-me.blade.php tests/Browser/PageHeroTest.php
git commit -m "feat(design): add shared page-hero component for the subpages"
```

---

### Task 5: Editable hero subtitles in the admin

Fixes §1's "one-sentence subtitle, translatable, editable via the existing Settings admin". The three new keys are **optional** in validation — an unfilled subtitle simply doesn't render, which is the correct behaviour while the content is still being written.

**Files:**
- Modify: `resources/views/pages/manage/⚡site-content.blade.php:15-22` (key lists), `:39-63` (`save`), `:87-118` (form)
- Modify: `database/seeders/SettingSeeder.php:12-23`
- Test: `tests/Feature/SiteContentManagementTest.php` (append)

**Interfaces:**
- Consumes: nothing.
- Produces: settings keys `about_hero_subtitle`, `experience_hero_subtitle`, `projects_hero_subtitle`, readable via `Setting::text($key, $locale)` and empty-string when unset. The component adds a public array property `$optionalTextKeys` alongside the existing `$textKeys`.

- [x] **Step 1: Write the failing test**

Append to `tests/Feature/SiteContentManagementTest.php`:

```php
test('hero subtitles are optional and saved per locale', function () {
    Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.site-content')
        ->set('texts.hero_suptitle', ['en' => 'Hello', 'cs' => 'Ahoj'])
        ->set('texts.hero_title', ['en' => 'I am X', 'cs' => 'Jsem X'])
        ->set('texts.stats_title', ['en' => 'Stats', 'cs' => 'Statistiky'])
        ->set('texts.tools_title', ['en' => 'Tools', 'cs' => 'Nástroje'])
        ->set('texts.reviews_title', ['en' => 'Reviews', 'cs' => 'Reference'])
        ->set('texts.about_title', ['en' => 'About', 'cs' => 'O mně'])
        ->set('texts.about_hero_subtitle', ['en' => 'Student and developer.', 'cs' => ''])
        ->set('roles.en', "Developer")
        ->set('roles.cs', "Vývojář")
        ->call('save')
        ->assertHasNoErrors();

    expect(Setting::text('about_hero_subtitle', 'en'))->toBe('Student and developer.')
        // An empty Czech translation falls back to the English one.
        ->and(Setting::text('about_hero_subtitle', 'cs'))->toBe('Student and developer.')
        // Untouched optional keys stay empty rather than blocking the save.
        ->and(Setting::text('experience_hero_subtitle', 'en'))->toBe('');
});
```

- [x] **Step 2: Run the test and verify it fails**

```bash
docker exec portfolio-app-1 php artisan test --compact --filter='hero subtitles are optional'
```

Expected: FAIL — "Unable to set component data. Public property [texts.about_hero_subtitle] not found" or a validation error.

- [x] **Step 3: Add the optional keys to the Livewire component**

In `resources/views/pages/manage/⚡site-content.blade.php`, directly after the `$textKeys` property (line 22), add:

```php
    /** @var array<int, string> Keys edited as {en,cs} text fields that may stay empty. */
    public array $optionalTextKeys = [
        'about_hero_subtitle',
        'experience_hero_subtitle',
        'projects_hero_subtitle',
    ];

    /** @return array<int, string> */
    public function allTextKeys(): array
    {
        return [...$this->textKeys, ...$this->optionalTextKeys];
    }
```

Replace the body of `mount()`:

```php
    public function mount(): void
    {
        foreach ($this->allTextKeys() as $key) {
            $this->texts[$key] = [
                'en' => Setting::text($key, 'en'),
                'cs' => Setting::text($key, 'cs'),
            ];
        }

        $this->roles = [
            'en' => implode("\n", Setting::list('hero_roles', 'en')),
            'cs' => implode("\n", Setting::list('hero_roles', 'cs')),
        ];
    }
```

Replace the rules/persist part of `save()`:

```php
    public function save(): void
    {
        $rules = ['roles.en' => ['required', 'string'], 'roles.cs' => ['nullable', 'string']];

        foreach ($this->textKeys as $key) {
            $rules["texts.{$key}.en"] = ['required', 'string', 'max:2000'];
            $rules["texts.{$key}.cs"] = ['nullable', 'string', 'max:2000'];
        }

        foreach ($this->optionalTextKeys as $key) {
            $rules["texts.{$key}.en"] = ['nullable', 'string', 'max:2000'];
            $rules["texts.{$key}.cs"] = ['nullable', 'string', 'max:2000'];
        }

        $this->validate($rules);

        foreach ($this->allTextKeys() as $key) {
            Setting::updateOrCreate(['key' => $key], ['value' => [
                'en' => $this->texts[$key]['en'] ?? '',
                'cs' => ($this->texts[$key]['cs'] ?? '') ?: ($this->texts[$key]['en'] ?? ''),
            ]]);
        }

        Setting::updateOrCreate(['key' => 'hero_roles'], ['value' => [
            'en' => $this->splitLines($this->roles['en']),
            'cs' => $this->splitLines($this->roles['cs'] ?: $this->roles['en']),
        ]]);

        Flux::toast(text: 'Site content saved.', variant: 'success');
    }
```

- [x] **Step 4: Render the optional fields in both locale tabs**

In the same file, inside `<x-slot:en>` add after the existing `@foreach ($textKeys as $key)` loop:

```blade
                @foreach ($optionalTextKeys as $key)
                    <flux:field>
                        <flux:label>{{ $this->label($key) }}</flux:label>
                        <flux:input wire:model="texts.{{ $key }}.en" />
                        <flux:description>Shown under the page title. Leave empty to hide it.</flux:description>
                        <flux:error name="texts.{{ $key }}.en" />
                    </flux:field>
                @endforeach
```

and the mirrored block inside `<x-slot:cs>`:

```blade
                @foreach ($optionalTextKeys as $key)
                    <flux:field>
                        <flux:label>{{ $this->label($key) }}</flux:label>
                        <flux:input wire:model="texts.{{ $key }}.cs" />
                        <flux:error name="texts.{{ $key }}.cs" />
                    </flux:field>
                @endforeach
```

- [x] **Step 5: Seed starter copy**

In `database/seeders/SettingSeeder.php`, add to the `$settings` array after `'about_title'`:

```php
            'about_hero_subtitle' => [
                'en' => 'Student, freelancer and chess player — here is the longer version.',
                'cs' => 'Student, freelancer a šachista — tady je delší verze.',
            ],
            'experience_hero_subtitle' => [
                'en' => 'Every certificate, competition and job, on one timeline.',
                'cs' => 'Všechny certifikáty, soutěže a práce na jedné časové ose.',
            ],
            'projects_hero_subtitle' => [
                'en' => 'Things I have shipped, newest first.',
                'cs' => 'Věci, které jsem vydal, od nejnovějších.',
            ],
```

- [x] **Step 6: Run the tests and verify they pass**

```bash
docker exec portfolio-app-1 vendor/bin/pint --dirty --format agent
docker exec portfolio-app-1 php artisan test --compact --filter='SiteContentManagement'
docker exec portfolio-app-1 php artisan test --compact --filter='DatabaseSeeder'
```

Expected: PASS.

- [x] **Step 7: Commit**

```bash
git add "resources/views/pages/manage/⚡site-content.blade.php" database/seeders/SettingSeeder.php tests/Feature/SiteContentManagementTest.php
git commit -m "feat(admin): editable, optional hero subtitles for the subpages"
```

---

### Task 6: About me hero — portrait, subtitle, meta chips

Fixes §1's About bullet, including "the About page is anonymous, which is backwards". Meta chips come from a **settings list** (same mechanism as `hero_roles`) so they can be edited without a deploy and render nothing while empty.

**Files:**
- Modify: `config/portfolio.php`
- Modify: `resources/views/about-me.blade.php`
- Modify: `resources/views/pages/manage/⚡site-content.blade.php` (add the `about_hero_meta` list editor)
- Test: `tests/Browser/PageHeroTest.php` (append), `tests/Feature/AboutMePageTest.php` (append)

**Interfaces:**
- Consumes: `<x-portfolio.page-hero>` (Task 4), the optional-settings machinery (Task 5).
- Produces: `config('portfolio.portrait')` — a path relative to `public/`; settings key `about_hero_meta` as a `{en: string[], cs: string[]}` list read with `Setting::list()`.

- [x] **Step 1: Write the failing test**

Append to `tests/Feature/AboutMePageTest.php`:

```php
use App\Models\Setting;

test('about me hero renders meta chips from settings and skips them when empty', function () {
    Setting::updateOrCreate(['key' => 'about_hero_meta'], ['value' => [
        'en' => ['19 years old', 'Pardubice, CZ'],
        'cs' => ['19 let', 'Pardubice, CZ'],
    ]]);

    $this->get(route('about-me'))
        ->assertOk()
        ->assertSee('19 years old')
        ->assertSee('page-hero-meta', false);

    Setting::where('key', 'about_hero_meta')->delete();

    $this->get(route('about-me'))
        ->assertOk()
        ->assertDontSee('page-hero-meta', false);
});

test('about me hero shows a portrait', function () {
    $this->get(route('about-me'))
        ->assertOk()
        ->assertSee('page-hero-photo', false);
});
```

- [x] **Step 2: Run the test and verify it fails**

```bash
docker exec portfolio-app-1 php artisan test --compact --filter='about me hero'
```

Expected: FAIL — "Failed asserting that the response contains 'page-hero-meta'".

- [x] **Step 3: Add the portrait path to config**

In `config/portfolio.php`, add before the closing `];`:

```php
    /*
    |--------------------------------------------------------------------------
    | Portrait
    |--------------------------------------------------------------------------
    |
    | Path (relative to public/) of the portrait shown in the About page hero.
    | Falls back to the home-hero photo until a dedicated portrait exists.
    |
    */

    'portrait' => 'images/id-photo-portrait-businessman-suit-260nw-1505360618 1.png',
```

- [x] **Step 4: Fill in the About hero**

In `resources/views/about-me.blade.php`, replace the `<x-portfolio.page-hero ... />` added in Task 4 with:

```blade
    <x-portfolio.page-hero
        :eyebrow="__('pages/hero.about_eyebrow')"
        :title="__('pages/hero.about_title')"
        :subtitle="\App\Models\Setting::text('about_hero_subtitle', $locale)"
        :meta="\App\Models\Setting::list('about_hero_meta', $locale)"
        :photo="config('portfolio.portrait')"
        photo-alt="projektant-pata"
    />
```

(`photo-alt` is a plain string, not the title — the title carries `<span>` markup that has no business in an `alt` attribute.)

- [x] **Step 5: Add the meta-list editor to the admin**

In `resources/views/pages/manage/⚡site-content.blade.php`, add a property next to `$roles`:

```php
    /** About-page hero meta chips as newline-separated strings per locale. */
    public array $aboutMeta = ['en' => '', 'cs' => ''];
```

At the end of `mount()`:

```php
        $this->aboutMeta = [
            'en' => implode("\n", Setting::list('about_hero_meta', 'en')),
            'cs' => implode("\n", Setting::list('about_hero_meta', 'cs')),
        ];
```

In `save()`, add to `$rules`:

```php
        $rules['aboutMeta.en'] = ['nullable', 'string'];
        $rules['aboutMeta.cs'] = ['nullable', 'string'];
```

and after the `hero_roles` write:

```php
        Setting::updateOrCreate(['key' => 'about_hero_meta'], ['value' => [
            'en' => $this->splitLines($this->aboutMeta['en']),
            'cs' => $this->splitLines($this->aboutMeta['cs'] ?: $this->aboutMeta['en']),
        ]]);
```

In the `<x-slot:en>` block, after the hero-roles field:

```blade
                <flux:field>
                    <flux:label>About hero meta</flux:label>
                    <flux:textarea wire:model="aboutMeta.en" rows="4" placeholder="One chip per line" />
                    <flux:description>One chip per line — age, location, school…</flux:description>
                    <flux:error name="aboutMeta.en" />
                </flux:field>
```

and the mirrored field in `<x-slot:cs>` bound to `aboutMeta.cs`.

- [x] **Step 6: Guard the seeder's canonical-key deletion**

`SettingSeeder::run()` deletes every key not in its own `$settings` array, which would wipe `about_hero_meta`. Add it to `$settings` with an empty default so seeding never destroys admin-entered chips wholesale but still starts empty:

```php
            'about_hero_meta' => ['en' => [], 'cs' => []],
```

Guard it so re-seeding does not clobber chips that already exist — replace the write loop in `run()` with:

```php
        foreach ($settings as $key => $value) {
            // Never overwrite an existing value with an intentionally empty default.
            if ($value === ['en' => [], 'cs' => []] && Setting::where('key', $key)->exists()) {
                continue;
            }

            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
```

- [x] **Step 7: Run the tests and verify they pass**

```bash
docker exec portfolio-app-1 vendor/bin/pint --dirty --format agent
docker exec portfolio-app-1 php artisan test --compact --filter='AboutMePage'
docker exec portfolio-app-1 php artisan test --compact --filter='SiteContentManagement'
docker exec portfolio-app-1 php artisan test --compact --filter='DatabaseSeeder'
```

Expected: PASS.

- [x] **Step 8: Commit**

```bash
git add config/portfolio.php resources/views/about-me.blade.php "resources/views/pages/manage/⚡site-content.blade.php" database/seeders/SettingSeeder.php tests/Feature/AboutMePageTest.php
git commit -m "feat(about): page hero with portrait, subtitle and editable meta chips"
```

---

### Task 7: Experience hero — derived counts, year span, filter bar inside

Fixes §1's Experience bullet. Counts and the year span are computed from whatever rows exist; with an empty table the hero shows a title and nothing else. The filter bar moves into the hero's slot so it stops reading as a floating toolbar.

**Files:**
- Modify: `app/Http/Controllers/ExperienceController.php`
- Modify: `resources/views/experience.blade.php:5-32`
- Create: `tests/Feature/PageHeroDataTest.php`

**Interfaces:**
- Consumes: `<x-portfolio.page-hero>` (Task 4), lang keys `pages/hero.meta_*` (Task 4).
- Produces: `ExperienceController` passes `$heroMeta` (`array<int, string>`) to the view. A private `yearSpan(Collection $experiences): ?array{from: string, to: string}` extracts the min/max four-digit year across every locale of the `year` jsonb column and returns `null` when no year is parseable.

- [x] **Step 1: Write the failing test**

Create `tests/Feature/PageHeroDataTest.php`:

```php
<?php

use App\Models\Experience;
use App\Models\Project;

test('experience hero meta counts entries and spans the parsed years', function () {
    Experience::query()->delete();
    Experience::factory()->create(['type' => 'work', 'year' => ['en' => '2021 - now', 'cs' => '2021 - teď']]);
    Experience::factory()->create(['type' => 'work', 'year' => ['en' => '2024', 'cs' => '2024']]);
    Experience::factory()->create(['type' => 'life', 'year' => ['en' => '2026', 'cs' => '2026']]);

    $this->get(route('experience'))
        ->assertOk()
        ->assertSee('3 entries')
        ->assertSee('2021 – 2026')
        ->assertSee('2 work')
        ->assertSee('1 life');
});

test('experience hero meta stays empty when there are no experiences', function () {
    Experience::query()->delete();

    $this->get(route('experience'))
        ->assertOk()
        ->assertDontSee('page-hero-meta', false);
});

test('experience hero omits the year span when no year is parseable', function () {
    Experience::query()->delete();
    Experience::factory()->create(['type' => 'work', 'year' => ['en' => 'ongoing', 'cs' => 'probíhá']]);

    $this->get(route('experience'))
        ->assertOk()
        ->assertSee('1 entries')
        ->assertDontSee('–');
});

test('projects hero meta counts projects and lists their tech badges', function () {
    Project::query()->delete();
    Project::factory()->create(['header' => ['en' => 'Alpha', 'cs' => 'Alpha']]);
    Project::factory()->create(['header' => ['en' => 'Beta', 'cs' => 'Beta']]);

    $this->get(route('projects'))
        ->assertOk()
        ->assertSee('2 projects');
});

test('projects hero meta stays empty with no projects', function () {
    Project::query()->delete();

    $this->get(route('projects'))
        ->assertOk()
        ->assertDontSee('page-hero-meta', false);
});
```

- [x] **Step 2: Run the test and verify it fails**

```bash
docker exec portfolio-app-1 php artisan test --compact --filter='PageHeroData'
```

Expected: FAIL — "Failed asserting that the response contains '3 entries'".

- [x] **Step 3: Derive the meta in the controller**

Replace `app/Http/Controllers/ExperienceController.php` with:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Experience;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ExperienceController extends Controller
{
    public function __invoke(): View
    {
        $experiences = Experience::with('badges')
            ->orderBy('sort_order')
            ->get();

        $badges = $experiences
            ->flatMap(fn ($e) => $e->badges)
            ->unique('id')
            ->values();

        $heroMeta = $this->heroMeta($experiences);

        return view('experience', compact('experiences', 'badges', 'heroMeta'));
    }

    /**
     * Facts for the page hero, derived from whatever rows exist. Each entry is
     * added only when it is meaningful, so a half-filled table renders fewer
     * chips instead of empty ones.
     *
     * @param  Collection<int, Experience>  $experiences
     * @return array<int, string>
     */
    private function heroMeta(Collection $experiences): array
    {
        if ($experiences->isEmpty()) {
            return [];
        }

        $meta = [__('pages/hero.meta_entries', ['count' => $experiences->count()])];

        if ($span = $this->yearSpan($experiences)) {
            $meta[] = __('pages/hero.meta_span', $span);
        }

        $byType = $experiences->countBy('type');

        if ($work = $byType->get('work')) {
            $meta[] = __('pages/hero.meta_work', ['count' => $work]);
        }

        if ($life = $byType->get('life')) {
            $meta[] = __('pages/hero.meta_life', ['count' => $life]);
        }

        return $meta;
    }

    /**
     * Earliest and latest four-digit year found anywhere in the translatable
     * `year` column ("2021 - now", "2024", "2021–2023" all parse).
     *
     * @param  Collection<int, Experience>  $experiences
     * @return array{from: string, to: string}|null
     */
    private function yearSpan(Collection $experiences): ?array
    {
        $years = $experiences
            ->flatMap(function (Experience $experience): array {
                $values = is_array($experience->year) ? $experience->year : [];
                preg_match_all('/\b(19|20)\d{2}\b/', implode(' ', $values), $matches);

                return $matches[0];
            })
            ->map(fn (string $year) => (int) $year)
            ->unique();

        if ($years->isEmpty()) {
            return null;
        }

        return ['from' => (string) $years->min(), 'to' => (string) $years->max()];
    }
}
```

- [x] **Step 4: Put the hero at the top of the Experience page and move the filter bar into it**

In `resources/views/experience.blade.php`, replace lines 5-32 (the opening `<section>` through the closing `</div>` of `#exp-filters`) with:

```blade
    <x-portfolio.page-hero
        :eyebrow="__('pages/hero.experience_eyebrow')"
        :title="__('pages/hero.experience_title')"
        :subtitle="\App\Models\Setting::text('experience_hero_subtitle', $locale)"
        :meta="$heroMeta"
    >
        {{-- Filter tabs live inside the hero so they read as part of the opener
             rather than a toolbar floating above the grid. --}}
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
    </x-portfolio.page-hero>

    <section id="experience" class="portfolio-section">
        <h2>{{ __('home/experience.title') }}</h2>
```

The hero's slot is full-width, so make the filter bar fill it — append to `resources/css/components/page-hero.css`:

```css
/* The Experience filter bar rides in the hero slot and needs the full row. */
.page-hero-extra > #exp-filters {
    width: 100%;
}
```

- [x] **Step 5: Run the tests and verify they pass**

```bash
docker exec portfolio-app-1 vendor/bin/pint --dirty --format agent
npm run build
docker exec portfolio-app-1 php artisan test --compact --filter='PageHeroData'
docker exec portfolio-app-1 php artisan test --compact --filter='ExperiencePage'
docker exec portfolio-app-1 php artisan test --compact --filter='PublicPages'
```

Expected: the Experience and Projects hero tests pass except the two `projects` ones, which Task 8 covers. Confirm the Experience ones are green before moving on.

- [x] **Step 6: Commit**

```bash
git add app/Http/Controllers/ExperienceController.php resources/views/experience.blade.php resources/css/components/page-hero.css tests/Feature/PageHeroDataTest.php
git commit -m "feat(experience): page hero with derived counts and the filter bar inside"
```

---

### Task 8: Projects hero — derived count and tech chips

Fixes §1's Projects bullet. Tech chips reuse the existing badge system rather than a hardcoded stack list, so they track whatever projects exist.

**Files:**
- Modify: `app/Http/Controllers/ProjectsController.php`
- Modify: `resources/views/projects.blade.php`
- Test: `tests/Feature/PageHeroDataTest.php` (already written in Task 7)

**Interfaces:**
- Consumes: `<x-portfolio.page-hero>` (Task 4), `pages/hero.meta_projects` (Task 4).
- Produces: `ProjectsController` passes `$heroMeta` (`array<int, string>`) to the view — the project count followed by up to six distinct badge names.

- [x] **Step 1: Run the two projects tests and verify they fail**

```bash
docker exec portfolio-app-1 php artisan test --compact --filter='projects hero meta'
```

Expected: FAIL — "Failed asserting that the response contains '2 projects'".

- [x] **Step 2: Derive the meta in the controller**

Replace `app/Http/Controllers/ProjectsController.php` with:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ProjectsController extends Controller
{
    /**
     * How many tech chips the hero shows before it stops — enough to signal the
     * stack, few enough to stay one line on a laptop.
     */
    private const HERO_BADGE_LIMIT = 6;

    public function __invoke(): View
    {
        $all = Project::with(['badges', 'links'])
            ->orderBy('sort_order')
            ->orderBy('year', 'desc')
            ->orderByRaw("header->>'en'")
            ->get();

        $projects = $all->groupBy('year');
        $heroMeta = $this->heroMeta($all);

        return view('projects', compact('projects', 'heroMeta'));
    }

    /**
     * Project count plus the distinct tech badges across every project. Empty
     * when there are no projects, so the hero drops the chip row entirely.
     *
     * @param  Collection<int, Project>  $projects
     * @return array<int, string>
     */
    private function heroMeta(Collection $projects): array
    {
        if ($projects->isEmpty()) {
            return [];
        }

        $locale = app()->getLocale();

        $badges = $projects
            ->flatMap(fn (Project $project) => $project->badges)
            ->unique('id')
            ->map(fn ($badge) => $badge->getTranslation('name', $locale))
            ->filter()
            ->take(self::HERO_BADGE_LIMIT)
            ->values()
            ->all();

        return [
            __('pages/hero.meta_projects', ['count' => $projects->count()]),
            ...$badges,
        ];
    }
}
```

- [x] **Step 3: Put the hero at the top of the Projects page**

In `resources/views/projects.blade.php`, insert above the `<section id="projects" ...>` line and drop the lead class from the section:

```blade
    <x-portfolio.page-hero
        :eyebrow="__('pages/hero.projects_eyebrow')"
        :title="__('pages/hero.projects_title')"
        :subtitle="\App\Models\Setting::text('projects_hero_subtitle', $locale)"
        :meta="$heroMeta"
    />

    <section id="projects" class="portfolio-section">
```

- [x] **Step 4: Run the tests and verify they pass**

```bash
docker exec portfolio-app-1 vendor/bin/pint --dirty --format agent
npm run build
docker exec portfolio-app-1 php artisan test --compact --filter='PageHeroData'
docker exec portfolio-app-1 php artisan test --compact --filter='PublicPages'
```

Expected: PASS.

- [x] **Step 5: Commit**

```bash
git add app/Http/Controllers/ProjectsController.php resources/views/projects.blade.php
git commit -m "feat(projects): page hero with derived count and tech chips"
```

---

## Phase C — Page tweaks

### Task 9: A continuous Experience timeline spine

Fixes §4's "the spine renders as disconnected dot-plus-short-segment fragments". `#exp-grid-line` is already a full-height 2px bar (`experience.css:149-156`), but each card paints a 3px `var(--c-bg)` ring around its dot (`:448-461`, `:476-489`) which punches holes in it. Thin the ring and give the spine a soft gradient fade at both ends so it reads as one line.

**Files:**
- Modify: `resources/css/pages/experience.css:149-156` (`#exp-grid-line`), `:448-461` and `:476-489` (the two dot rules)
- Test: `tests/Browser/DesignSystemTest.php` (append)

**Interfaces:**
- Consumes: nothing.
- Produces: no new selectors.

- [ ] **Step 1: Write the failing test**

Append to `tests/Browser/DesignSystemTest.php`:

```php
test('the experience timeline spine is one continuous line behind the dots', function () {
    $page = visit('/experience')->resize(1440, 900);

    $spine = $page->script(<<<'JS'
        (() => {
            const line = document.getElementById('exp-grid-line');
            const dots = [...document.querySelectorAll('#exp-col-left .exp-card, #exp-col-right .exp-card')];
            if (!line || dots.length === 0) { return null; }
            const ring = parseFloat(getComputedStyle(dots[0], '::after').borderTopWidth);
            return {
                height: line.getBoundingClientRect().height,
                ringWidth: ring,
            };
        })()
    JS);

    expect($spine)->not->toBeNull()
        ->and($spine['height'])->toBeGreaterThan(0)
        // A fat background-coloured ring is what cut the spine into fragments.
        ->and($spine['ringWidth'])->toBeLessThanOrEqual(1.5);
});
```

- [ ] **Step 2: Run the test and verify it fails**

```bash
npm run build
docker exec portfolio-app-1 php artisan test --compact --filter='timeline spine'
```

Expected: FAIL — `ringWidth` is `3`.

- [ ] **Step 3: Thin the dot rings and soften the spine ends**

In `resources/css/pages/experience.css`, replace the `#exp-grid-line` rule (lines 149-156) with:

```css
#exp-grid-line {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    width: 2px;
    /* Fades in and out at the ends so the spine does not start and stop with a
       hard edge above the first / below the last card. */
    background: linear-gradient(
        to bottom,
        transparent 0%,
        var(--c-primary-fade) 6%,
        var(--c-primary-fade) 94%,
        transparent 100%
    );
    z-index: 0;
}
```

In both dot rules (`#exp-col-left .exp-card::after` and `#exp-col-right .exp-card::after`), change:

```css
    border: 3px solid var(--c-bg);
```

to:

```css
    border: 1px solid var(--c-bg);
```

- [ ] **Step 4: Run the test and verify it passes**

```bash
npm run build
docker exec portfolio-app-1 php artisan test --compact --filter='timeline spine'
```

Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add resources/css/pages/experience.css tests/Browser/DesignSystemTest.php
git commit -m "fix(experience): make the timeline spine read as one continuous line"
```

---

### Task 10: Projects page — tighter rows, capped year overlap, uniform screenshot frame

Fixes §5's first three bullets. The uniform frame also neutralises inconsistent source screenshots, which matters while the project images are still placeholders.

**Files:**
- Modify: `resources/css/components/project-row.css:7-32` (row + image), `:87-130` (responsive)
- Modify: `resources/css/pages/projects.css`
- Test: `tests/Browser/DesignSystemTest.php` (append)

**Interfaces:**
- Consumes: nothing.
- Produces: `.projects-row > img` and `.projects-row-img-placeholder` share a fixed `16 / 10` aspect ratio; `.projects-year-group` uses `--sp-project-gap`.

- [ ] **Step 1: Write the failing test**

Append to `tests/Browser/DesignSystemTest.php`:

```php
test('project screenshots share one frame and rows do not leave a screen of dead space', function () {
    $page = visit('/projects')->resize(1440, 900);

    $layout = $page->script(<<<'JS'
        (() => {
            const shots = [...document.querySelectorAll('.projects-row > img, .projects-row-img-placeholder')];
            if (shots.length === 0) { return null; }

            const ratios = shots.map((s) => {
                const r = s.getBoundingClientRect();
                return Math.round((r.width / r.height) * 100) / 100;
            });

            const groups = [...document.querySelectorAll('.projects-year-group')];
            const gaps = groups.slice(1).map((g, i) =>
                g.getBoundingClientRect().top - groups[i].getBoundingClientRect().bottom
            );

            return {
                ratioSpread: Math.max(...ratios) - Math.min(...ratios),
                maxGap: gaps.length ? Math.max(...gaps) : 0,
                viewport: window.innerHeight,
            };
        })()
    JS);

    expect($layout)->not->toBeNull()
        ->and($layout['ratioSpread'])->toBeLessThan(0.05)
        ->and($layout['maxGap'])->toBeLessThan($layout['viewport'] * 0.6);
});
```

- [ ] **Step 2: Run the test and verify it fails**

```bash
npm run build
docker exec portfolio-app-1 php artisan test --compact --filter='project screenshots share one frame'
```

Expected: FAIL — the fixed `height: 360px` + variable `width` gives a ratio spread above 0.05 on the notebook breakpoint, and `maxGap` exceeds the threshold.

- [ ] **Step 3: Give every screenshot the same frame**

In `resources/css/components/project-row.css`, replace lines 7-32 with:

```css
.projects-row {
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.projects-row--reverse {
    flex-direction: row-reverse;
}

/* One frame for every screenshot regardless of how the source image was
   cropped: fixed aspect ratio, cover-fit from the top (so browser chrome in a
   tall capture is what gets trimmed, not the content), card border + radius. */
.projects-row > img,
.projects-row-img-placeholder {
    width: 600px;
    aspect-ratio: 16 / 10;
    height: auto;
    flex-shrink: 0;
    border-radius: var(--r-card);
    object-fit: cover;
    object-position: top center;
    border: var(--border-w) solid var(--c-primary-fade);
}

.projects-row-img-placeholder {
    background-color: var(--c-primary-fade);
}
```

In the responsive blocks, replace the `height` overrides that no longer apply. Lines 87-96 become:

```css
@media (max-width: 1440px) {
    .projects-row > img,
    .projects-row-img-placeholder {
        width: 500px;
    }
}
```

and the `max-width: 992px` block's image rule becomes:

```css
    .projects-row > img,
    .projects-row-img-placeholder {
        width: 100%;
        height: auto;
        order: 1;
    }
```

(drop the `max-height: 300px` — the aspect ratio now governs).

- [ ] **Step 4: Halve the year-group gap and cap the year-numeral overlap**

Replace `resources/css/pages/projects.css` (below its two imports) with:

```css
/* ================================================================
   PROJECTS PAGE STYLES
   ================================================================ */

:root {
    /* Half of --sp-section: the year markers still get their drama, without
       the "is the page over?" hole between rows. */
    --sp-project-gap: 3.125rem;
}

/* ── Year group ── */
.projects-year-group {
    margin-bottom: var(--sp-project-gap);
}

/* The giant year numeral is the page's watermark. It keeps a smaller overlap
   than the section watermarks so the full digit height stays visible. */
.projects-year-label {
    color: var(--c-primary);
    text-align: left !important;
    margin-bottom: -0.12em;
}

.projects-year-group .projects-row:first-of-type {
    margin-top: 1.5rem;
}
```

- [ ] **Step 5: Run the test and verify it passes**

```bash
npm run build
docker exec portfolio-app-1 php artisan test --compact --filter='project screenshots share one frame'
docker exec portfolio-app-1 php artisan test --compact --filter='PublicPages'
```

Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add resources/css/components/project-row.css resources/css/pages/projects.css tests/Browser/DesignSystemTest.php
git commit -m "fix(projects): uniform screenshot frame, tighter rows, visible year numerals"
```

---

### Task 11: Home hero photo treatment and a real Work/Life active state

Fixes §6's first two bullets. Both tabs currently render as identical outlined pills with only a text-colour change (`index.css:109-125`); the active one becomes a filled gold pill.

**Files:**
- Modify: `resources/css/pages/index.css:65` (hero image), `:109-125` (work tabs)
- Test: `tests/Browser/DesignSystemTest.php` (append)

**Interfaces:**
- Consumes: nothing.
- Produces: `.hero-page-image img` carries the card border/radius; `.work-top-btn.active` has a non-transparent background.

- [ ] **Step 1: Write the failing test**

Append to `tests/Browser/DesignSystemTest.php`:

```php
test('the home hero photo wears the card treatment', function () {
    $page = visit('/')->resize(1440, 900);

    $photo = $page->script(<<<'JS'
        (() => {
            const style = getComputedStyle(document.querySelector('.hero-page-image img'));
            return {
                radius: parseFloat(style.borderTopLeftRadius),
                border: parseFloat(style.borderTopWidth),
            };
        })()
    JS);

    expect($photo['radius'])->toBeGreaterThan(0)
        ->and($photo['border'])->toBeGreaterThan(0);
});

test('the active work/life tab is filled, not just recoloured', function () {
    $page = visit('/')->resize(1440, 900);

    $backgrounds = $page->script(<<<'JS'
        (() => {
            const active = document.querySelector('.work-top-btn.active');
            const inactive = document.querySelector('.work-top-btn:not(.active)');
            return {
                active: getComputedStyle(active).backgroundColor,
                inactive: getComputedStyle(inactive).backgroundColor,
            };
        })()
    JS);

    expect($backgrounds['active'])->not->toBe($backgrounds['inactive'])
        ->and($backgrounds['active'])->not->toBe('rgba(0, 0, 0, 0)');
});
```

- [ ] **Step 2: Run the test and verify it fails**

```bash
npm run build
docker exec portfolio-app-1 php artisan test --compact --filter='home hero photo|active work/life tab'
```

Expected: FAIL — radius `0`, and both backgrounds `rgba(0, 0, 0, 0)`.

- [ ] **Step 3: Frame the hero photo**

In `resources/css/pages/index.css`, replace line 65:

```css
.hero-page-image img { width: 400px; }
```

with:

```css
/* Card treatment so the portrait reads as a framed object anchored in the
   hero, not a bare rectangle floating in the empty right half. */
.hero-page-image img {
    width: 400px;
    aspect-ratio: 4 / 5;
    object-fit: cover;
    object-position: top center;
    border-radius: var(--r-card);
    border: var(--border-w) solid var(--c-primary-lt);
    box-shadow: 0 24px 60px -32px color-mix(in srgb, var(--c-primary) 70%, transparent);
    rotate: 1.5deg;
}
```

- [ ] **Step 4: Fill the active tab**

Replace the `.work-top-btn` block (lines 109-125) with:

```css
.work-top-btn {
    padding: 0.5rem 1.5rem;
    border-radius: 999px;
    border: var(--border-w) solid var(--c-primary-fade);
    background: transparent;
    cursor: pointer;
    transition: background-color var(--t-fast), border-color var(--t-fast);
}

.work-top-btn:first-child { margin-right: 10px; }

.work-top-btn h4 { transition: var(--t-fast); }

.work-top-btn:hover { border-color: var(--c-primary); }

.work-top-btn:hover h4 { color: var(--c-primary); }

/* Active tab is a filled gold pill — an outlined pill with gold text read as
   the same thing as the inactive one. */
.work-top-btn.active {
    background: var(--c-primary);
    border-color: var(--c-primary);
}

.work-top-btn.active h4 {
    color: var(--c-selection-fg);
    font-weight: var(--fw-semibold);
}
```

- [ ] **Step 5: Run the tests and verify they pass**

```bash
npm run build
docker exec portfolio-app-1 php artisan test --compact --filter='home hero photo|active work/life tab'
docker exec portfolio-app-1 php artisan test --compact --filter='HomePage'
```

Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add resources/css/pages/index.css tests/Browser/DesignSystemTest.php
git commit -m "feat(home): frame the hero photo and fill the active work/life tab"
```

---

### Task 12: Page-based carousel dots and readable review text

Fixes §6's reviews bullet. `measureSteps()` in `resources/js/app.js:473-483` currently creates one step per **card**, so ten reviews at three-per-view yield eight dots. Step by a full page instead, keeping the final clamped offset so the last cards stay reachable and whole.

**Files:**
- Modify: `resources/js/app.js:473-483`
- Modify: `resources/css/pages/index.css:317-320` (`.reviews-row-card p`)
- Test: `tests/Browser/PublicPagesTest.php` (append)

**Interfaces:**
- Consumes: the `--reviews-per-view` CSS variable already declared on `.reviews-carousel` (`index.css:237-243`).
- Produces: `steps` holds one offset per page; `pageCount()` therefore equals the dot count.

- [ ] **Step 1: Write the failing test**

Append to `tests/Browser/PublicPagesTest.php`:

```php
test('the reviews carousel shows one dot per page, not one per card', function () {
    Review::query()->delete();
    foreach (range(0, 9) as $i) {
        Review::factory()->create(['sort_order' => $i, 'name' => "Reviewer $i"]);
    }

    $page = visit('/')->resize(1280, 900);

    $dots = $page->script("document.querySelectorAll('.reviews-carousel-dot').length");

    // 10 reviews, 3 per view → 4 pages. One dot per card would give 8.
    expect($dots)->toBe(4);
});
```

- [ ] **Step 2: Run the test and verify it fails**

```bash
npm run build
docker exec portfolio-app-1 php artisan test --compact --filter='one dot per page'
```

Expected: FAIL — 8 dots.

- [ ] **Step 3: Step by a page**

In `resources/js/app.js`, replace `measureSteps` (lines 473-483) with:

```javascript
    const measureSteps = () => {
        const maxScroll = Math.max(0, viewport.scrollWidth - viewport.clientWidth);
        const rowLeft = row.getBoundingClientRect().left + viewport.scrollLeft;

        const perView = Math.max(
            1,
            Math.round(parseFloat(getComputedStyle(carousel).getPropertyValue('--reviews-per-view')) || 1)
        );

        // One step per *page* of cards, not per card — otherwise ten reviews at
        // three-per-view produce eight dots for four screens of content. Offsets
        // stay card-aligned, so every step still shows whole cards.
        const offsets = [...row.children]
            .filter((_, i) => i % perView === 0)
            .map((card) =>
                Math.min(Math.round(card.getBoundingClientRect().left + viewport.scrollLeft - rowLeft), maxScroll)
            );

        steps = [...new Set(offsets)];
    };
```

- [ ] **Step 4: Bump the review body text a step**

In `resources/css/pages/index.css`, replace the `.reviews-row-card p` rule (lines 317-320) with:

```css
.reviews-row-card p {
    font-size: var(--fs-base);
    font-weight: var(--fw-regular);
    line-height: 1.6;
    text-align: justify;
}
```

- [ ] **Step 5: Run the tests and verify they pass**

```bash
npm run build
docker exec portfolio-app-1 php artisan test --compact --filter='PublicPages'
```

Expected: PASS — including the existing "every reviews carousel step shows only whole cards" test, which the page-aligned offsets still satisfy.

- [ ] **Step 6: Commit**

```bash
git add resources/js/app.js resources/css/pages/index.css tests/Browser/PublicPagesTest.php
git commit -m "fix(reviews): page-based carousel dots and a readable review body size"
```

---

### Task 13: A curated tools grid

Fixes §6's tools bullet: normalise the icon box so full-colour brand marks, mono glyphs and pixel art sit at one visual weight, add a hover state, and stop the last row orphaning.

**Files:**
- Modify: `resources/css/pages/index.css:207-235`
- Test: `tests/Browser/DesignSystemTest.php` (append)

**Interfaces:**
- Consumes: nothing.
- Produces: `.tools-row` becomes an auto-fit grid; `.tools-row-card img` has a fixed square box.

- [ ] **Step 1: Write the failing test**

Append to `tests/Browser/DesignSystemTest.php`:

```php
test('tool icons share one box and the grid does not orphan its last row', function () {
    $page = visit('/')->resize(1440, 900);

    $tools = $page->script(<<<'JS'
        (() => {
            const cards = [...document.querySelectorAll('.tools-row-card')];
            if (cards.length === 0) { return null; }

            const boxes = cards.map((c) => {
                const r = c.querySelector('img').getBoundingClientRect();
                return { w: Math.round(r.width), h: Math.round(r.height) };
            });

            const rows = new Map();
            cards.forEach((c) => {
                const top = Math.round(c.getBoundingClientRect().top);
                rows.set(top, [...(rows.get(top) || []), c]);
            });
            const lefts = [...rows.values()].map((row) => Math.round(row[0].getBoundingClientRect().left));

            return {
                widthSpread: Math.max(...boxes.map((b) => b.w)) - Math.min(...boxes.map((b) => b.w)),
                heightSpread: Math.max(...boxes.map((b) => b.h)) - Math.min(...boxes.map((b) => b.h)),
                // Every row must start on the same left edge — a centred final
                // row is what makes the orphan look accidental.
                leftSpread: Math.max(...lefts) - Math.min(...lefts),
            };
        })()
    JS);

    expect($tools)->not->toBeNull()
        ->and($tools['widthSpread'])->toBeLessThanOrEqual(1)
        ->and($tools['heightSpread'])->toBeLessThanOrEqual(1)
        ->and($tools['leftSpread'])->toBeLessThanOrEqual(1);
});
```

- [ ] **Step 2: Run the test and verify it fails**

```bash
npm run build
docker exec portfolio-app-1 php artisan test --compact --filter='tool icons share one box'
```

Expected: FAIL — `widthSpread` is large (icons are `object-fit: contain` inside auto-width boxes).

- [ ] **Step 3: Normalise the grid**

In `resources/css/pages/index.css`, replace lines 207-235 with:

```css
/* ── Tools ── */
.tools-row {
    max-width: 62.5rem;
    margin: auto;
    display: grid;
    /* auto-fill (not auto-fit) keeps the track count fixed, so a short last row
       starts at the left edge instead of centring into an orphan. */
    grid-template-columns: repeat(auto-fill, minmax(9.5rem, 1fr));
    justify-items: start;
    gap: 1rem;
}

.tools-row-card {
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    padding: 1rem 0.5rem;
    margin-bottom: 0;
    border: var(--border-w) solid transparent;
    border-radius: var(--r-card-sm);
    transition: border-color var(--t-fast), background-color var(--t-fast);
}

.tools-row-card:hover {
    border-color: var(--c-primary-fade);
    background: color-mix(in srgb, var(--c-primary) 6%, transparent);
}

/* One square box for every mark, whatever its intrinsic size or style. */
.tools-row-card img {
    width: 84px;
    height: 84px;
    object-fit: contain;
}

/* GitHub logo swaps with theme */
.tool-github-light { display: none; }
html:not(.dark) .tool-github-dark { display: none; }
html:not(.dark) .tool-github-light { display: block; }

.tools-row-card h4 {
    text-align: center;
    margin-top: 1rem;
    font-size: var(--fs-base);
}
```

Then delete the now-redundant `.tools-row` and `.tools-row-card` overrides in the three responsive blocks (`index.css` `min-width: 993px`, `max-width: 992px`, `max-width: 576px`) — `auto-fill` handles the reflow. Keep every other rule in those blocks untouched.

- [ ] **Step 4: Run the test and verify it passes**

```bash
npm run build
docker exec portfolio-app-1 php artisan test --compact --filter='tool icons share one box'
```

Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add resources/css/pages/index.css tests/Browser/DesignSystemTest.php
git commit -m "feat(home): normalise the tools grid and add a hover state"
```

---

## Phase D — Mobile

### Task 14: Mobile fixes — hero wrap, two-up stats, branded header

Fixes §7. The stats "pyramid" on About is deliberate (`about-me.css:40-70` documents the maths), so this task does not touch the desktop stagger — it only replaces the single-column mobile tower with a two-up grid.

**Files:**
- Modify: `resources/css/app.css` (`--fs-h1` mobile override, mobile brand mark)
- Modify: `resources/css/pages/about-me.css:105-109` (the `576px` block)
- Modify: `resources/css/pages/index.css:413-428` (the `576px` block)
- Modify: `resources/views/components/mobile-nav.blade.php:1`
- Test: `tests/Browser/DesignSystemTest.php` (append)

**Interfaces:**
- Consumes: nothing.
- Produces: `#mobile-brand` — a link rendered next to `#toggle-mobile-nav`, visible only under 992px.

- [ ] **Step 1: Write the failing test**

Append to `tests/Browser/DesignSystemTest.php`:

```php
test('mobile stats pair up instead of towering', function () {
    $page = visit('/about-me')->resize(390, 844);

    $columns = $page->script(<<<'JS'
        (() => {
            const cards = [...document.querySelectorAll('.about-me-stats-cards > div')];
            if (cards.length < 2) { return null; }
            const firstTop = Math.round(cards[0].getBoundingClientRect().top);
            return cards.filter((c) => Math.round(c.getBoundingClientRect().top) === firstTop).length;
        })()
    JS);

    expect($columns)->toBe(2);
});

test('the hero headline does not tower on a phone', function () {
    $page = visit('/')->resize(390, 844);

    $lines = $page->script(<<<'JS'
        (() => {
            const h1 = document.querySelector('.hero-page-text h1');
            const style = getComputedStyle(h1);
            const lineHeight = parseFloat(style.lineHeight) || parseFloat(style.fontSize) * 0.95;
            return Math.round(h1.getBoundingClientRect().height / lineHeight);
        })()
    JS);

    expect($lines)->toBeLessThanOrEqual(3);
});

test('the mobile header carries a brand mark next to the hamburger', function () {
    $page = visit('/')->resize(390, 844);

    $visible = $page->script(
        "getComputedStyle(document.getElementById('mobile-brand')).display !== 'none'"
    );

    expect($visible)->toBeTrue();
});
```

- [ ] **Step 2: Run the tests and verify they fail**

```bash
npm run build
docker exec portfolio-app-1 php artisan test --compact --filter='mobile stats pair up|hero headline does not tower|mobile header carries a brand'
```

Expected: FAIL — 1 column, 4 headline lines, and no `#mobile-brand` element.

- [ ] **Step 3: Pair the mobile stats**

In `resources/css/pages/about-me.css`, replace the `max-width: 576px` block (lines 105-109) with:

```css
@media (max-width: 576px) {
    /* Two-up rather than one full-width card per fact — ten one-line stats made
       four screens of scrolling as a single column. */
    .about-me-stats-cards > div {
        flex: 0 0 calc(50% - 0.5rem);
    }

    .about-me-stats-cards {
        gap: 1rem;
    }
}
```

In `resources/css/pages/index.css`, inside the existing `max-width: 576px` block, replace:

```css
    .stats-cards { grid-template-columns: 1fr; }
```

with:

```css
    .stats-cards { grid-template-columns: repeat(2, 1fr); gap: 1rem; }
```

- [ ] **Step 4: Lower the mobile h1 floor**

In `resources/css/app.css`, extend the `max-width: 576px` block added in Task 1 so it reads:

```css
@media (max-width: 576px) {
    :root {
        --fs-h2: clamp(1.6rem, 0.4rem + 6vw, 2.75rem);
        /* The hero headline wrapped to four lines at the old floor, leaving the
           trailing comma dangling on its own. */
        --fs-h1: clamp(1.9rem, 1.1rem + 4.6vw, 2.6rem);
    }
}
```

- [ ] **Step 5: Add the mobile brand mark**

In `resources/views/components/mobile-nav.blade.php`, replace line 1 with:

```blade
<a href="{{ route('home') }}" id="mobile-brand">projektant-pata</a>
<button id="toggle-mobile-nav">☰</button>
```

Add to `resources/css/app.css`, inside the existing `@media (max-width: 992px)` block that styles `#toggle-mobile-nav` (line 643):

```css
    /* The hamburger sat alone with nothing anchoring the header. */
    #mobile-brand {
        position: fixed;
        top: 1.25rem;
        left: 1.25rem;
        z-index: 1000;
        font-family: var(--font-display);
        font-size: var(--fs-sm);
        letter-spacing: 0.02em;
        color: var(--c-primary);
    }
```

and, outside that media query (so it stays hidden on desktop):

```css
#mobile-brand {
    display: none;
}

@media (max-width: 992px) {
    #mobile-brand {
        display: block;
    }
}
```

- [ ] **Step 6: Run the tests and verify they pass**

```bash
npm run build
docker exec portfolio-app-1 php artisan test --compact --filter='mobile stats pair up|hero headline does not tower|mobile header carries a brand'
docker exec portfolio-app-1 php artisan test --compact --filter='watermark headings stay inside'
```

Expected: PASS.

- [ ] **Step 7: Commit**

```bash
git add resources/css/app.css resources/css/pages/about-me.css resources/css/pages/index.css resources/views/components/mobile-nav.blade.php tests/Browser/DesignSystemTest.php
git commit -m "fix(mobile): pair stats, tame the hero headline, brand the header"
```

---

### Task 15: Full-suite verification

**Files:**
- Test: everything

- [ ] **Step 1: Build assets on the host**

```bash
npm run build
```

- [ ] **Step 2: Run the whole suite**

```bash
docker exec portfolio-app-1 php artisan test --compact
```

Expected: PASS. If a browser test fails on geometry, re-run it alone first — the container's chromium occasionally needs a warm run.

- [ ] **Step 3: Check formatting**

```bash
docker exec portfolio-app-1 vendor/bin/pint --dirty --format agent
```

Expected: no changes, or formatting applied and re-committed.

- [ ] **Step 4: Commit any formatting fixes**

```bash
git add -A
git commit -m "chore: pint formatting after design upgrade"
```

---

## Deferred: content work, not code

These items from `docs/design-upgrade-ideas.md` are blocked on the site's data being finished, and this plan deliberately leaves them out. Everything above is built to look right whether or not they land.

| Item | Source | Why deferred |
|---|---|---|
| Timeline reads 2024, 2024, … 2026 last | §4 | `ExperienceController` orders by `sort_order`, which is admin-controlled data. Fix by reordering in `⚡experiences`, not in code. A code-side `ORDER BY year` is not viable while `year` is a free-text jsonb column ("2021 - now"). |
| "Hackathon AstroPi" vs "AstroPi Hackathon 2024" duplicate | §4 | Editorial call on two seeded rows. |
| "competetion" / "Automatization" typos | §4 | `database/seeders/ExperienceSeeder.php:154-155` plus a `competetion` badge-color key in `2026_04_12_165907_seed_badge_colors.php`; renaming needs the badge key and the seeded badge name changed together. |
| Only 3 projects vs "5+ Projects Completed" | §5 | Add the missing projects, or edit the stat in `⚡stats`. Pure content. |
| Project screenshots re-shot consistently | §5 | Task 10's uniform frame makes mismatched sources far less visible; new captures are still an asset task. |
| Tech badges on project rows | §5 | `project-row.blade.php:19-23` already renders `$project->badges` — the badges just are not attached to the project rows yet. Attach them in `⚡projects`. |
| Marking joke reviews | §8 | Editorial; the `source` badge mechanism already exists. |

## Already resolved

- §8's "check first whether the clock is already live" — it is. `updateClock()` in `resources/js/app.js:21` drives `#mobile-nav-clock` from real client time. No work needed.
- §5's tech badges — see the table above; the rendering path exists.

## Self-review notes

- **Spec coverage.** §1 → Tasks 4–8. §2 → Task 1. §3 → Task 2. §4 → Task 9 (spine); ordering/duplicates/typos deferred as content; the "empty block bottom-left" masonry balance is left alone deliberately, because `layoutMasonry()` in `experience.blade.php:170-187` already balances by measured column height — the gap the review saw is a consequence of the card mix, which changes as data lands. §5 → Task 10; seeding deferred. §6 → Tasks 11–13; the stats "pyramid" is intentional and documented in `about-me.css:40-70`, so it is not "fixed" — only the mobile tower is, in Task 14. §7 → Task 14. §8 → Task 3 (rhythm), Task 10 (home vs projects card parity via the shared frame), clock already live, joke reviews deferred.
- **Interfaces.** `heroMeta` is the prop name in both controllers and both views. The component's props are `eyebrow`, `title`, `subtitle`, `meta`, `photo`, `photoAlt` throughout. `allTextKeys()` is defined in Task 5 and used only there.
