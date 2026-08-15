# Section head (`sechead`) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the home page's five centred outlined `h2` watermarks with a reusable section-head component — decorative ghost wordmark, gold-rule eyebrow, a real `h2`, and an optional note.

**Architecture:** One anonymous Blade component (`x-portfolio.section-head`) plus one CSS file (`resources/css/components/section-head.css`) imported by `pages/index.css`, exactly like `dock-hero`. All copy moves to `resources/lang/{en,cs}/home/*.php`. The global `.portfolio-page h2` watermark rule stays alive for the other pages and is overridden for heads only.

**Tech Stack:** Laravel 13, Blade anonymous components, Tailwind v4 + hand-written CSS with `--c-*` tokens, Pest 4 (feature + browser tests), Vite.

**Spec:** `docs/superpowers/specs/2026-08-15-section-head-design.md`

## Global Constraints

- **Scope is the home page only.** `about-me`, `experience`, `projects` keep their watermark `h2`. Do not touch `resources/css/app.css:348` (`.portfolio-page h2`).
- **No new design tokens, no new colours.** Only `--c-primary`, `--c-primary-fade`, `--c-muted`, `--c-fg`, `--c-watermark`, `--font-display`, `--fw-*`, `--sp-section`, `--t-fast`, `--border-w`.
- **Class prefix is `.sechead-*`** so nothing collides with `.ab-*` / `.exp-*` / `.dock-hero-*`.
- **Specificity:** global rules `.portfolio-page h2` and `.portfolio-page p` (`app.css:348`, `:376`) are `0,1,1` and beat a bare `.sechead-note`. Every colliding rule is written double-classed — `.sechead .sechead-note`, `.sechead .sechead-row h2` — the same trick `dock-hero.css:142` already uses. The ghost is a `div`, not a `p`, so the global `p` rule never applies to it.
- **`line-height:1.12` + `padding-bottom:.1em` on `.sechead-ghost` are load-bearing.** At `line-height:1` descenders clip — that is the live `My Stats` → `Mu Stats` bug.
- **The ghost is always `aria-hidden="true"`**, `pointer-events:none`, `user-select:none`, and its text must never repeat the `h2` string.
- **Ghosts on Stats, Projects, Reviews only** — never on consecutive sections.
- **Every command runs in Docker:** `docker exec portfolio-app-1 …`. Vite builds run on the **host**.
- **Pint before every commit that touches PHP:** `docker exec portfolio-app-1 vendor/bin/pint --dirty --format agent`.
- **Settings keys `stats_title` / `tools_title` / `reviews_title` stay in the database and in `⚡site-content`.** The home page just stops reading them; `about-me` still reads `stats_title`. Do not delete keys, seeder rows, or `SiteContentManagementTest` assertions.

## File Structure

| File | Responsibility |
|---|---|
| `resources/views/components/portfolio/section-head.blade.php` | **Create.** The component: props, markup, ghost `aria-hidden`. |
| `resources/css/components/section-head.css` | **Create.** All `.sechead-*` rules, variants, breakpoints, the `h2` override. |
| `resources/css/pages/index.css:1-2` | **Modify.** One `@import` line. |
| `resources/views/welcome.blade.php` | **Modify.** Five `<h2>` → five `<x-portfolio.section-head>`. |
| `resources/lang/en/home/{stats,experience,projects,tools,reviews}.php` | **Modify.** `head_ghost` / `head_eyebrow` / `head_title` / `head_note`. |
| `resources/lang/cs/home/{stats,experience,projects,tools,reviews}.php` | **Modify.** Same keys, Czech. |
| `resources/css/app.css` (`.portfolio-col`) | **Modify.** `overflow-x: clip` — Task 5 only. |
| `tests/Feature/SectionHeadTest.php` | **Create.** Rendering, locale, `aria-hidden`, ghost ≠ title, note link. |
| `tests/Browser/SectionHeadTest.php` | **Create.** No sideways scroll at four widths. |

---

### Task 1: Component, CSS, and the Stats head

Stats is first because it is the only section that exercises all four parts at once (ghost + eyebrow + title + note) without needing a link, it sits directly under the dock hero where a ghost collision would show immediately, and it is where the `Mu Stats` clipping bug is visible.

**Files:**
- Create: `resources/views/components/portfolio/section-head.blade.php`
- Create: `resources/css/components/section-head.css`
- Create: `tests/Feature/SectionHeadTest.php`
- Modify: `resources/css/pages/index.css:1` (add `@import`)
- Modify: `resources/views/welcome.blade.php:21-22` (Stats section)
- Modify: `resources/lang/en/home/stats.php`, `resources/lang/cs/home/stats.php`

**Interfaces:**
- Consumes: nothing.
- Produces: `<x-portfolio.section-head :ghost :eyebrow :title :note :variant />` — `ghost` (string, default `''`), `eyebrow` (string, required), `title` (string, required, HTML), `note` (string, default `''`, HTML), `variant` (`'default'|'noghost'|'behind'|'center'`, default `'default'`). CSS classes `.sechead`, `.sechead-ghost`, `.sechead-row`, `.sechead-eyebrow`, `.sechead-note`. Tasks 2–4 use this component unchanged.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/SectionHeadTest.php`:

```php
<?php

test('the stats section is introduced by a section head', function () {
    $this->get(route('home'))
        ->assertSee('By the numbers')
        ->assertSee('Some of it is <em>serious</em>', false)
        ->assertSee("Two numbers I'd defend in an interview, and one I wouldn't.", false);
});

test('the stats ghost wordmark is decorative and does not repeat the title', function () {
    $this->get(route('home'))
        ->assertSee('<div class="sechead-ghost" aria-hidden="true">My stats</div>', false)
        ->assertDontSee('<h2>My stats</h2>', false);
});

test('the stats head renders in Czech', function () {
    $this->withSession(['locale' => 'cs'])
        ->get(route('home'))
        ->assertSee('V číslech')
        ->assertSee('Něco z toho je <em>vážně</em>', false);
});
```

- [ ] **Step 2: Run the test to verify it fails**

```bash
docker exec portfolio-app-1 php artisan test --compact tests/Feature/SectionHeadTest.php
```

Expected: FAIL — the page still renders `<h2>My Stats</h2>` from `Setting::text('stats_title')`, none of the new strings exist.

- [ ] **Step 3: Add the English copy**

In `resources/lang/en/home/stats.php`, add these keys directly under `'title' => 'My Stats',` (leave `title` in place — nothing else reads it yet, and removing it is a separate cleanup):

```php
    'head_ghost' => 'My stats',
    'head_eyebrow' => 'By the numbers',
    'head_title' => 'Some of it is <em>serious</em>',
    'head_note' => "Two numbers I'd defend in an interview, and one I wouldn't.",
```

- [ ] **Step 4: Add the Czech copy**

In `resources/lang/cs/home/stats.php`, under `'title' => 'Moje statistiky',`:

```php
    'head_ghost' => 'Statistiky',
    'head_eyebrow' => 'V číslech',
    'head_title' => 'Něco z toho je <em>vážně</em>',
    'head_note' => 'Dvě čísla bych obhájil u pohovoru, jedno ne.',
```

- [ ] **Step 5: Create the component**

`resources/views/components/portfolio/section-head.blade.php`:

```blade
@props([
    'ghost' => '',
    'eyebrow',
    'title',
    'note' => '',
    'variant' => 'default',
])

{{--
    The opener shared by every page section: a decorative outlined ghost
    wordmark, the gold-rule eyebrow, the section's real h2, and an optional
    note in the right column.

    The ghost is aria-hidden — the h2 carries the accessible name. Before this
    component the outlined word *was* the heading, so a screen reader heard
    `Mu Stats` and no context. Two or three ghosts per page at most, never on
    consecutive sections; past that it stops being emphasis and becomes
    wallpaper.

    `title` and `note` render unescaped so the gold <em> and the note's link
    live in the copy, not in code. Both come from resources/lang — no user
    input reaches them.

    A `div`, not a `p`, for the ghost on purpose: `.portfolio-page p` sets its
    own size and weight and would beat the single-class ghost rule.
--}}
<div @class([
    'sechead',
    'sechead--noghost' => $ghost === '',
    'sechead--'.$variant => $variant !== 'default',
])>
    @if ($ghost !== '')
        <div class="sechead-ghost" aria-hidden="true">{{ $ghost }}</div>
    @endif

    <div class="sechead-row">
        <div>
            <p class="sechead-eyebrow">{{ $eyebrow }}</p>
            <h2>{!! $title !!}</h2>
        </div>

        @if ($note !== '')
            <p class="sechead-note">{!! $note !!}</p>
        @endif
    </div>
</div>
```

- [ ] **Step 6: Create the stylesheet**

`resources/css/components/section-head.css`:

```css
/* ================================================================
   SECTION HEAD (.sechead)
   ================================================================
   The site-wide opener for a page section: ghost wordmark above,
   gold-rule eyebrow, the real h2, optional right-hand note.

   Ported from the design handoff (design_handoff_section_start).
   No new tokens, no new colours.

   Rules that collide with the global `.portfolio-page h2` / `p`
   rules (app.css:348, :376) are written double-classed — those
   globals are 0,1,1 and would otherwise win. Same trick as
   dock-hero.css:142.
   ================================================================ */

.sechead {
    position: relative;
    margin-bottom: 1.875rem;
}

/* Outlined page word above the head. Decorative: aria-hidden in the
   markup, never selectable, never a click target.
   `line-height: 1.12` + `padding-bottom: .1em` are load-bearing — at
   line-height 1 the descenders clip and `My stats` renders as
   `Mu stats`. */
.sechead .sechead-ghost {
    position: absolute;
    left: -0.2rem;
    top: -4.6rem;
    font-family: var(--font-display);
    font-size: clamp(64px, 9vw, 118px);
    font-weight: var(--fw-bold);
    letter-spacing: -0.04em;
    line-height: 1.12;
    padding-bottom: 0.1em;
    white-space: nowrap;
    pointer-events: none;
    user-select: none;
    color: transparent;
    -webkit-text-stroke: 1px color-mix(in srgb, var(--c-primary-fade) 80%, transparent);
}

/* Title block left, note right, bottom-aligned on purpose: the note's
   last line sits on the title's baseline block. */
.sechead .sechead-row {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 2.5rem;
    flex-wrap: wrap;
}

.sechead .sechead-eyebrow {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    font-size: 11px;
    font-weight: var(--fw-semibold);
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--c-muted);
    margin-bottom: 0.7rem;
}

/* The system's section marker — the Experience hero already uses it.
   No emoji, no icons. */
.sechead .sechead-eyebrow::before {
    content: '';
    width: 26px;
    height: 1px;
    background: var(--c-primary);
    flex-shrink: 0;
}

/* Overrides the global watermark h2 (app.css:348) for heads only. That
   rule still dresses the h2 on about-me / experience / projects; when
   the component reaches those pages, delete it there and drop the four
   reset declarations here. */
.sechead .sechead-row h2 {
    text-align: left;
    margin-bottom: 0;
    color: var(--c-fg);
    -webkit-text-stroke: 0;
    font-family: var(--font-display);
    font-size: clamp(1.9rem, 1.2rem + 2vw, 2.6rem);
    font-weight: var(--fw-bold);
    line-height: 1;
    letter-spacing: -0.02em;
    max-width: 26ch;
    text-wrap: pretty;
}

/* One gold phrase per title, never two. Not italic. */
.sechead .sechead-row h2 em {
    font-style: normal;
    font-weight: 500;
    color: var(--c-primary);
}

.sechead .sechead-note {
    font-size: 0.95rem;
    font-weight: var(--fw-light);
    line-height: 1.6;
    color: var(--c-muted);
    max-width: 38ch;
    text-wrap: pretty;
}

.sechead .sechead-note a {
    color: var(--c-fg);
    text-decoration: none;
    border-bottom: var(--border-w) solid var(--c-primary-fade);
    transition: border-color var(--t-fast), color var(--t-fast);
}

.sechead .sechead-note a:hover {
    color: var(--c-primary);
    border-color: var(--c-primary);
}

/* ── Variants ── */

/* Tight gaps and heads inside a card: the ghost overlaps the head
   instead of sitting above it. */
.sechead--behind .sechead-ghost {
    top: -0.5rem;
    left: -0.4rem;
    opacity: 0.75;
}

/* Kept for continuity with pages that still centre their heads. The
   home page does not use it — the note needs a right column. */
.sechead--center {
    text-align: center;
}

.sechead--center .sechead-row {
    flex-direction: column;
    align-items: center;
}

.sechead--center .sechead-eyebrow {
    justify-content: center;
}

.sechead--center .sechead-ghost {
    left: 50%;
    transform: translateX(-50%);
}

/* ── Responsive ── */

/* The note drops to its own full-width row under the title. */
@media (max-width: 1100px) {
    .sechead .sechead-note {
        max-width: none;
        flex-basis: 100%;
    }
}

@media (max-width: 760px) {
    .sechead {
        margin-bottom: 1.4rem;
    }

    .sechead .sechead-ghost {
        top: -2.6rem;
        font-size: clamp(44px, 13vw, 72px);
    }

    .sechead .sechead-row h2 {
        max-width: none;
    }
}
```

- [ ] **Step 7: Load the stylesheet**

`resources/css/pages/index.css` currently starts with:

```css
@import '../components/project-row.css';
@import '../components/dock-hero.css';
```

Add a third line (CSS `@import` must stay at the top of the file, before any rule):

```css
@import '../components/section-head.css';
```

- [ ] **Step 8: Swap the Stats head**

In `resources/views/welcome.blade.php`, replace:

```blade
    <section id="stats" class="portfolio-section">
        <h2>{{ \App\Models\Setting::text('stats_title', $locale) }}</h2>
```

with:

```blade
    <section id="stats" class="portfolio-section">
        <x-portfolio.section-head
            :ghost="__('home/stats.head_ghost')"
            :eyebrow="__('home/stats.head_eyebrow')"
            :title="__('home/stats.head_title')"
            :note="__('home/stats.head_note')"
        />
```

- [ ] **Step 9: Run the tests**

```bash
docker exec portfolio-app-1 php artisan test --compact tests/Feature/SectionHeadTest.php
```

Expected: PASS, 3 tests.

- [ ] **Step 10: Run the home page suite for regressions**

```bash
docker exec portfolio-app-1 php artisan test --compact tests/Feature/HomePageTest.php
```

Expected: PASS. If a test asserted the old `My Stats` heading, it is now wrong — update it to the new head rather than restoring the watermark.

- [ ] **Step 11: Build and eyeball**

On the **host** (not in the container):

```bash
npm run build
```

Then open http://localhost:8008 — the Stats section should show the outlined `My stats` above a gold rule, `BY THE NUMBERS`, and the title with one gold word. Check the ghost's descenders are not clipped, and that it does not collide with the dock hero's own wordmark.

- [ ] **Step 12: Format and commit**

```bash
docker exec portfolio-app-1 vendor/bin/pint --dirty --format agent
git add resources/views/components/portfolio/section-head.blade.php resources/css/components/section-head.css resources/css/pages/index.css resources/views/welcome.blade.php resources/lang/en/home/stats.php resources/lang/cs/home/stats.php tests/Feature/SectionHeadTest.php
git commit -m "feat(section-head): add the section opener and use it on Stats"
```

---

### Task 2: Projects head — ghost plus a note that links out

**Files:**
- Modify: `resources/views/welcome.blade.php` (Projects section, currently `:59-60`)
- Modify: `resources/lang/en/home/projects.php`, `resources/lang/cs/home/projects.php`
- Test: `tests/Feature/SectionHeadTest.php`

**Interfaces:**
- Consumes: `<x-portfolio.section-head>` from Task 1, unchanged.
- Produces: the `:url` placeholder convention for links inside a note — the lang string holds `<a href=":url">…</a>` and the view passes `['url' => route('projects')]` to `__()`. Later heads with links follow this.

- [ ] **Step 1: Write the failing test**

Append to `tests/Feature/SectionHeadTest.php`:

```php
test('the projects head links out to the projects page', function () {
    $this->get(route('home'))
        ->assertSee('Selected work')
        ->assertSee('Things I <em>shipped</em>, not things I started', false)
        ->assertSee('<a href="'.route('projects').'">All projects →</a>', false);
});
```

- [ ] **Step 2: Run it to verify it fails**

```bash
docker exec portfolio-app-1 php artisan test --compact --filter="projects head links out" tests/Feature/SectionHeadTest.php
```

Expected: FAIL — none of those strings are on the page.

- [ ] **Step 3: Add the copy**

`resources/lang/en/home/projects.php`:

```php
    'head_ghost' => 'Projects',
    'head_eyebrow' => 'Selected work',
    'head_title' => 'Things I <em>shipped</em>, not things I started',
    'head_note' => 'Two of eighteen repositories. <a href=":url">All projects →</a>',
```

`resources/lang/cs/home/projects.php`:

```php
    'head_ghost' => 'Projekty',
    'head_eyebrow' => 'Vybraná práce',
    'head_title' => 'Věci, které jsem <em>dokončil</em>, ne ty, co jsem začal',
    'head_note' => 'Dva z osmnácti repozitářů. <a href=":url">Všechny projekty →</a>',
```

- [ ] **Step 4: Swap the head**

In `resources/views/welcome.blade.php`, replace:

```blade
    <section id="projects" class="portfolio-section">
        <h2>{{ __('home/projects.title') }}</h2>
```

with:

```blade
    <section id="projects" class="portfolio-section">
        <x-portfolio.section-head
            :ghost="__('home/projects.head_ghost')"
            :eyebrow="__('home/projects.head_eyebrow')"
            :title="__('home/projects.head_title')"
            :note="__('home/projects.head_note', ['url' => route('projects')])"
        />
```

`home/projects.title` stays in the lang file — `/projects` reads it.

- [ ] **Step 5: Run the tests**

```bash
docker exec portfolio-app-1 php artisan test --compact tests/Feature/SectionHeadTest.php
```

Expected: PASS, 4 tests.

- [ ] **Step 6: Commit**

```bash
docker exec portfolio-app-1 vendor/bin/pint --dirty --format agent
git add resources/views/welcome.blade.php resources/lang/en/home/projects.php resources/lang/cs/home/projects.php tests/Feature/SectionHeadTest.php
git commit -m "feat(section-head): open the Projects section with a linked note"
```

---

### Task 3: Work & Life and Tools heads — no ghost

Both sections stay ghost-less: Work & Life has its tab switcher directly under the head, and a fourth ghost on Tools would put two ghosts on consecutive sections.

**Files:**
- Modify: `resources/views/welcome.blade.php` (Work & Life `:31-32`, Tools `:67-68`)
- Modify: `resources/lang/{en,cs}/home/experience.php`, `resources/lang/{en,cs}/home/tools.php`
- Test: `tests/Feature/SectionHeadTest.php`

**Interfaces:**
- Consumes: `<x-portfolio.section-head>` (Task 1); the `:url` note convention (Task 2).
- Produces: nothing new.

- [ ] **Step 1: Write the failing test**

Append to `tests/Feature/SectionHeadTest.php`:

```php
test('the work and tools heads render without a ghost wordmark', function () {
    $response = $this->get(route('home'));

    $response
        ->assertSee('Track record')
        ->assertSee("Where I've <em>been</em> since 2021", false)
        ->assertSee('<a href="'.route('experience').'">Experience page</a>', false)
        ->assertSee('Daily drivers')
        ->assertSee('What I actually <em>open</em> every day', false)
        ->assertDontSee('>Work &amp; Life</div>', false)
        ->assertDontSee('>Tools</div>', false);
});
```

The two `assertDontSee` lines are the ghost check: `.sechead-ghost` is the only `div` that would carry those bare strings.

- [ ] **Step 2: Run it to verify it fails**

```bash
docker exec portfolio-app-1 php artisan test --compact --filter="without a ghost wordmark" tests/Feature/SectionHeadTest.php
```

Expected: FAIL on `Track record`.

- [ ] **Step 3: Add the Work & Life copy**

`resources/lang/en/home/experience.php` — add next to `title_home`:

```php
    'head_eyebrow' => 'Track record',
    'head_title' => "Where I've <em>been</em> since 2021",
    'head_note' => 'Toggle the two lists. Full record on the <a href=":url">Experience page</a>.',
```

`resources/lang/cs/home/experience.php`:

```php
    'head_eyebrow' => 'Dosavadní dráha',
    'head_title' => 'Kde jsem <em>byl</em> od roku 2021',
    'head_note' => 'Přepni mezi seznamy. Celý přehled na <a href=":url">stránce Zkušenosti</a>.',
```

- [ ] **Step 4: Add the Tools copy**

`resources/lang/en/home/tools.php`:

```php
    'head_eyebrow' => 'Daily drivers',
    'head_title' => 'What I actually <em>open</em> every day',
    'head_note' => 'Ordered by how often, not by how impressive.',
```

`resources/lang/cs/home/tools.php`:

```php
    'head_eyebrow' => 'Denní výbava',
    'head_title' => 'Co si <em>otevřu</em> každý den',
    'head_note' => 'Seřazeno podle četnosti, ne podle působivosti.',
```

- [ ] **Step 5: Swap both heads**

Work & Life — replace:

```blade
    <section class="work portfolio-section">
        <h2>{{ __('home/experience.title_home') }}</h2>
```

with:

```blade
    <section class="work portfolio-section">
        <x-portfolio.section-head
            :eyebrow="__('home/experience.head_eyebrow')"
            :title="__('home/experience.head_title')"
            :note="__('home/experience.head_note', ['url' => route('experience')])"
        />
```

Tools — replace:

```blade
    <section id="tools" class="portfolio-section">
        <h2>{{ \App\Models\Setting::text('tools_title', $locale) }}</h2>
```

with:

```blade
    <section id="tools" class="portfolio-section">
        <x-portfolio.section-head
            :eyebrow="__('home/tools.head_eyebrow')"
            :title="__('home/tools.head_title')"
            :note="__('home/tools.head_note')"
        />
```

Omitting `ghost` is what makes a head ghost-less — the component adds `.sechead--noghost` itself.

- [ ] **Step 6: Run the tests**

```bash
docker exec portfolio-app-1 php artisan test --compact tests/Feature/SectionHeadTest.php
```

Expected: PASS, 5 tests.

- [ ] **Step 7: Commit**

```bash
docker exec portfolio-app-1 vendor/bin/pint --dirty --format agent
git add resources/views/welcome.blade.php resources/lang/en/home/experience.php resources/lang/cs/home/experience.php resources/lang/en/home/tools.php resources/lang/cs/home/tools.php tests/Feature/SectionHeadTest.php
git commit -m "feat(section-head): open Work & Life and Tools without a ghost"
```

---

### Task 4: Reviews head, and prove no watermark `h2` is left on home

**Files:**
- Modify: `resources/views/welcome.blade.php` (Reviews section, currently `:115-116`)
- Modify: `resources/lang/en/home/reviews.php`, `resources/lang/cs/home/reviews.php`
- Test: `tests/Feature/SectionHeadTest.php`

**Interfaces:**
- Consumes: `<x-portfolio.section-head>` (Task 1).
- Produces: nothing new. After this task the home page has no `h2` outside a `.sechead-row`.

- [ ] **Step 1: Write the failing tests**

Append to `tests/Feature/SectionHeadTest.php`:

```php
test('the reviews head renders with a ghost and no note', function () {
    $this->get(route('home'))
        ->assertSee('What people say')
        ->assertSee('Words from people who <em>worked</em> with me', false)
        ->assertSee('<div class="sechead-ghost" aria-hidden="true">Reviews</div>', false);
});

test('every h2 on the home page belongs to a section head', function () {
    $html = $this->get(route('home'))->getContent();

    // 5 section heads + the footer wordmark, which keeps its own oversized
    // treatment and is not a section head.
    expect(preg_match_all('/<h2[\s>]/', $html))->toBe(6)
        ->and(preg_match_all('/<p class="sechead-eyebrow">/', $html))->toBe(5)
        ->and($html)->toContain('<h2 class="portfolio-footer-watermark">');
});
```

The second test is the guard that the old centred watermark heading is gone from home: five sections, five heads, and the only `h2` outside them is the footer wordmark.

- [ ] **Step 2: Run them to verify they fail**

```bash
docker exec portfolio-app-1 php artisan test --compact --filter="reviews head|belongs to a section head" tests/Feature/SectionHeadTest.php
```

Expected: FAIL — `What people say` missing, and the head count is 4, not 5.

- [ ] **Step 3: Add the copy**

`resources/lang/en/home/reviews.php`:

```php
    'head_ghost' => 'Reviews',
    'head_eyebrow' => 'What people say',
    'head_title' => 'Words from people who <em>worked</em> with me',
```

`resources/lang/cs/home/reviews.php`:

```php
    'head_ghost' => 'Reference',
    'head_eyebrow' => 'Co říkají ostatní',
    'head_title' => 'Slova lidí, kteří se mnou <em>pracovali</em>',
```

No `head_note` — the handoff's rule is to leave the note out when there is nothing honest to put there; the head does not look unfinished without it.

- [ ] **Step 4: Swap the head**

Replace:

```blade
    <section id="reviews" class="portfolio-section">
        <h2>{{ \App\Models\Setting::text('reviews_title', $locale) }}</h2>
```

with:

```blade
    <section id="reviews" class="portfolio-section">
        <x-portfolio.section-head
            :ghost="__('home/reviews.head_ghost')"
            :eyebrow="__('home/reviews.head_eyebrow')"
            :title="__('home/reviews.head_title')"
        />
```

- [ ] **Step 5: Run the tests**

```bash
docker exec portfolio-app-1 php artisan test --compact tests/Feature/SectionHeadTest.php
```

Expected: PASS, 7 tests.

- [ ] **Step 6: Check `$locale` is still used**

`welcome.blade.php:3` defines `$locale` and it is still needed by the project rows, experience rows and stats cards. Confirm it is still referenced:

```bash
grep -n '\$locale' resources/views/welcome.blade.php
```

Expected: several hits (stats cards, experience rows, project rows, review text). If the count were zero, the `@php $locale = …` line would have to go — it is not.

- [ ] **Step 7: Commit**

```bash
docker exec portfolio-app-1 vendor/bin/pint --dirty --format agent
git add resources/views/welcome.blade.php resources/lang/en/home/reviews.php resources/lang/cs/home/reviews.php tests/Feature/SectionHeadTest.php
git commit -m "feat(section-head): open the Reviews section, retiring the last watermark h2 on home"
```

---

### Task 5: Kill the sideways scroll the ghost can cause

The ghost is `white-space: nowrap` and 118px tall — on a narrow viewport it is wider than the column. The clip cannot go on `.portfolio-section`: the ghost sits at `top: -4.6rem`, **above** the section box, so clipping there would cut the ghost itself. It goes on `.portfolio-col` (`app.css:425`), the flex column that holds `main`.

**Files:**
- Modify: `resources/css/app.css:425-430` (`.portfolio-col`)
- Create: `tests/Browser/SectionHeadTest.php`

**Interfaces:**
- Consumes: the rendered home page from Tasks 1–4.
- Produces: nothing later tasks depend on.

- [ ] **Step 1: Write the failing browser test**

Create `tests/Browser/SectionHeadTest.php`:

```php
<?php

/**
 * Horizontal overflow of the document, in px. The ghost wordmark is
 * `white-space: nowrap`, so a long word on a narrow viewport is a real
 * sideways-scroll risk, not a theoretical one.
 */
$overflowJs = <<<'JS'
    (() => document.documentElement.scrollWidth - document.documentElement.clientWidth)()
JS;

test('the home page never scrolls sideways at any width', function (int $width) use ($overflowJs) {
    $this->seed(\Database\Seeders\SettingSeeder::class);

    $page = visit('/')->resize($width, 900);

    expect($page->script($overflowJs))->toBeLessThanOrEqual(0);
})->with([360, 760, 1100, 1440]);
```

- [ ] **Step 2: Run it and record which widths fail**

```bash
docker exec portfolio-app-1 php artisan test --compact tests/Browser/SectionHeadTest.php
```

Expected: FAIL at the narrow widths (360, likely 760) with a positive overflow. If every width already passes, still apply Step 3 — the clip is what keeps it passing when a translation lengthens a ghost — and note in the commit that the test was green before the fix.

- [ ] **Step 3: Clip the column**

In `resources/css/app.css`, `.portfolio-col` becomes:

```css
.portfolio-col {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    min-width: 0; /* allow the grid column to shrink below content min-width */
    /* The section-head ghost is nowrap and overflows the column on narrow
       viewports. Clipped here rather than on .portfolio-section, because the
       ghost sits above its section's box and clipping there would cut it.
       `clip` not `hidden`: hidden would make this a scroll container and break
       any future sticky child. */
    overflow-x: clip;
}
```

- [ ] **Step 4: Rebuild and re-run**

On the **host**:

```bash
npm run build
```

Then:

```bash
docker exec portfolio-app-1 php artisan test --compact tests/Browser/SectionHeadTest.php
```

Expected: PASS, 4 tests.

- [ ] **Step 5: Verify the clip did not eat the dock hero**

The hero photo is full-bleed. Run the existing hero browser tests:

```bash
docker exec portfolio-app-1 php artisan test --compact tests/Browser/DockHeroTest.php
```

Expected: PASS. Then look at http://localhost:8008 at 1440 and at 390 — the hero photo must still reach the right edge and the sticky sidebar must still stick. If the photo is cut, move the clip: drop it from `.portfolio-col` and instead wrap the five sections of `welcome.blade.php` in `<div class="sechead-page">` with `.sechead-page { overflow-x: clip; }` added to `section-head.css`, which keeps the hero outside the clip. Re-run Steps 4 and 5.

- [ ] **Step 6: Commit**

```bash
git add resources/css/app.css tests/Browser/SectionHeadTest.php
git commit -m "fix(layout): clip horizontal overflow from the section-head ghost"
```

---

### Task 6: Full verification pass

**Files:**
- No source changes expected. Any fix found here is committed with its own message.

**Interfaces:**
- Consumes: everything from Tasks 1–5.
- Produces: the go/no-go on the home rollout.

- [ ] **Step 1: Run the whole suite**

```bash
docker exec portfolio-app-1 php artisan test --compact
```

Expected: PASS. `SiteContentManagementTest` must still pass untouched — the Settings keys stayed.

- [ ] **Step 2: Rebuild assets**

On the **host**:

```bash
npm run build
```

- [ ] **Step 3: Visual pass, dark theme**

http://localhost:8008 at 1440, 1100, 760, 360. For each width confirm:
- ghost descenders are not clipped (`My stats`, `Projects`);
- the eyebrow's gold rule is 26px and sits on the text's centre line;
- exactly one gold word per title;
- the note sits in the right column above 1100px and drops to its own row below;
- Work & Life and Tools have no ghost, and the tab switcher is not crowded.

- [ ] **Step 4: Visual pass, light theme**

Toggle the theme and repeat at 1440 and 360. The ghost is a `color-mix` off `--c-primary-fade`, so the stroke should thin on parchment rather than vanish or go black. If it reads wrong, follow the precedent at `dock-hero.css:137` — `html:not(.dark)` swaps the hero ghost to a solid `--c-watermark` fill with no stroke — and add the same override for `.sechead-ghost`, with a test at Task 5's widths still green.

- [ ] **Step 5: Screen-reader sanity check**

In DevTools, open the accessibility tree for the home page. Expected: five level-2 headings reading the new titles, and no node for any ghost word.

- [ ] **Step 6: Commit any fixes**

```bash
docker exec portfolio-app-1 vendor/bin/pint --dirty --format agent
git add -A
git commit -m "fix(section-head): <what the pass turned up>"
```

If nothing turned up, skip the commit.

---

## Follow-ups (not this plan)

- Roll the component out to `about-me`, `experience`, `projects` — one `@import` and a markup swap each.
- Delete `.portfolio-page h2` from `app.css:348` and the four reset declarations in `section-head.css` once the last page migrates. Note the footer wordmark (`portfolio-footer.blade.php:2`) is also an `h2` and currently inherits that rule — it needs its own declarations before the global one goes.
- Drop `tools_title` / `reviews_title` from `⚡site-content` and `SettingSeeder` at the same time (`stats_title` and `about_title` stay — `about-me` reads them).
- The handoff's note that the `ChatGPT — The best AI` review should go before a real head invites people to read the quotes — a copy decision for the owner.
