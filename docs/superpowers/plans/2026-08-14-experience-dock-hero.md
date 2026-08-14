# Experience Dock Hero Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the shared page hero on the Experience page with the design handoff's three-column bordered composition (dock / copy / photo), built as a reusable `<x-portfolio.dock-hero>` component.

**Architecture:** A new Blade component plus one new CSS partial, imported by `resources/css/pages/experience.css`. The hero ships **visible first** — Task 1 puts it on `/experience` fed by the settings that already exist plus a new lang file, so the owner can look at it and comment before any admin work happens. Only after the look does Task 4 promote the two new copy fields (tags, photo caption) into the `Setting` model and the `⚡site-content` admin page. No new JS, no migrations, no new dependencies.

**Tech Stack:** Laravel 13, Blade components, Tailwind v4 + hand-written CSS partials, Livewire 4 (admin page, Task 4 only), Pest 4 (feature + browser tests), Vite, Docker.

**Source spec:** `docs/superpowers/specs/2026-08-14-experience-dock-hero-design.md`
**Design handoff:** `projektant-pata Design System.zip` → `design_handoff_experience_hero/`

## Task order and review gates

| Task | Deliverable | Owner reviews? |
| --- | --- | --- |
| 1 | Hero live on `/experience`, desktop | **yes — stop and show screenshots** |
| 2 | Responsive stacking + browser tests | **yes — stop and show screenshots** |
| 3 | Crop/seam tuning from the owner's comments | yes |
| 4 | Tags + caption move into Settings and the admin form | no |
| 5 | Whole-suite verification and handoff notes | no |

Tasks 1 and 2 end with a hard stop: publish the screenshots, wait for comments, and fold them into Task 3. Do not start Task 4 before Task 3 closes — the admin form's field list should describe copy that is already known to be right.

## Global Constraints

- Every PHP/artisan/test command runs inside Docker: `docker exec portfolio-app-1 <cmd>`. Vite builds (`npm run build`) run on the **host**.
- Run `docker exec portfolio-app-1 vendor/bin/pint --dirty --format agent` before every commit that touched PHP.
- Tests: `docker exec portfolio-app-1 php artisan test --compact --filter=<Name>`. Postgres backs the suite.
- Translatable/JSON settings are asserted with `toEqual`, never `toBe` — Postgres reorders `jsonb` keys.
- No new design tokens. Use the existing `--c-primary`, `--c-primary-fade`, `--c-bg`, `--c-surface`, `--c-fg`, `--c-muted`, `--border-w`, `--r-card`, `--t-fast`, `--font-display` from `resources/css/app.css`. Do not add values to the `@theme` block.
- No new composer or npm dependencies.
- `SettingSeeder::run()` deletes every key not listed in its `$settings` array. Any new setting key **must** be added there or it will be wiped on the next seed.
- The rotator element keeps the id `hero-rotator`; exactly one per page. `resources/js/app.js` binds it and already guards with `if (!el) return`.
- Do not add the `portfolio-section` class to the new hero: that class sets `opacity: 0` until the scroll observer fires.
- Copy is never hardcoded in the component — it arrives as props from `Setting::text()` / `Setting::list()` or `__()`.

---

### Task 1: Put the hero on screen (desktop)

The biggest task, and deliberately so: it ends with something to look at. Extracts the shared rotator CSS, builds the component and its desktop stylesheet, swaps it into the Experience page, and retires the shared hero's claim on that page.

Copy sources in this task: eyebrow / title / roles come from the settings that already exist; wordmark, dock label and tag chips come from a new lang file. The photo caption is not rendered yet (Task 4 gives it a home in the admin).

**Files:**
- Create: `resources/css/components/hero-rotator.css`
- Create: `resources/css/components/dock-hero.css`
- Create: `resources/views/components/portfolio/dock-hero.blade.php`
- Create: `resources/lang/en/pages/experience.php`
- Create: `resources/lang/cs/pages/experience.php`
- Modify: `resources/css/components/page-hero.css:57-74`
- Modify: `resources/css/pages/experience.css:1`
- Modify: `resources/views/experience.blade.php:5-12`
- Modify: `config/portfolio.php:37-42`
- Modify: `tests/Feature/PageHeroTest.php` (datasets + one assertion)
- Modify: `tests/Browser/PageHeroTest.php` (four datasets)
- Test: `tests/Feature/DockHeroTest.php` (create)

**Interfaces:**
- Consumes: `App\Models\Setting::text()` / `::list()` (existing static helpers); settings `experience_hero_suptitle`, `experience_hero_title`, `experience_hero_roles` (all already seeded).
- Produces:
  - Blade component `<x-portfolio.dock-hero>`, props `title` (required), `eyebrow`, `roles` (array), `tags` (array), `wordmark`, `dockLabel`, `dockImage`, `dockImageAlt`, `photo`, `photoAlt`, `caption` — all optional except `title`, all strings except the two arrays.
  - CSS classes `.dock-hero`, `-dock`, `-dock-label`, `-copy`, `-ghost`, `-eyebrow`, `-title`, `-roles`, `-tags`, `-tag`, `-photo`, `-cap`. Task 2 adds media queries over these exact names.
  - `resources/css/components/hero-rotator.css` holding `#hero-rotator span`, `.hero-caret`, `@keyframes caret-blink`.
  - Config key `portfolio.hero_images.experience_dock` (string, `''` until the asset exists).
  - Lang keys `pages/experience.hero_wordmark`, `.hero_dock_label`, `.hero_tags` (array of 5 strings).

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/DockHeroTest.php`:

```php
<?php

use App\Models\Setting;

beforeEach(function () {
    $this->seed(\Database\Seeders\SettingSeeder::class);
});

test('the experience page opens with the dock hero, not the shared page hero', function () {
    $this->get(route('experience'))
        ->assertOk()
        ->assertSee('class="dock-hero"', false)
        ->assertDontSee('class="hero-page', false);
});

test('the dock hero renders its settings copy', function () {
    Setting::updateOrCreate(['key' => 'experience_hero_suptitle'], ['value' => ['en' => '🗓️ Where I have been', 'cs' => '🗓️ Kudy jsem prošel']]);
    Setting::updateOrCreate(['key' => 'experience_hero_title'], ['value' => ['en' => 'My <span>journey</span>,', 'cs' => 'Moje <span>cesta</span>,']]);

    $this->get(route('experience'))
        ->assertSee('🗓️ Where I have been')
        ->assertSee('<h1 class="dock-hero-title">My <span>journey</span>,</h1>', false)
        ->assertSee('id="hero-rotator"', false)
        ->assertSee('data-roles=', false);
});

test('the dock hero renders the wordmark, dock label and tags from the lang files', function () {
    $this->get(route('experience'))
        ->assertSee('aria-hidden="true">Experience<', false)
        ->assertSee('Navigate')
        ->assertSee('>Backend</li>', false)
        ->assertSee('>Erasmus</li>', false);

    $this->withSession(['locale' => 'cs'])
        ->get(route('experience'))
        ->assertSee('aria-hidden="true">Zkušenosti<', false)
        ->assertSee('Navigace')
        ->assertSee('>Soutěže</li>', false);
});

test('the dock hero renders the czech title under the cs locale', function () {
    $this->withSession(['locale' => 'cs'])
        ->get(route('experience'))
        ->assertSee('Moje <span>cesta</span>,', false)
        ->assertDontSee('My <span>journey</span>,', false);
});

test('the experience page still renders exactly one h1 and one rotator', function () {
    $html = $this->get(route('experience'))->assertOk()->getContent();

    expect(substr_count($html, '<h1'))->toBe(1)
        ->and(substr_count($html, 'id="hero-rotator"'))->toBe(1);
});

test('the dock hero omits the caption markup when no caption is passed', function () {
    $this->get(route('experience'))
        ->assertDontSee('dock-hero-cap', false);
});

test('the dock column renders label-only while the dock asset is missing', function () {
    config()->set('portfolio.hero_images.experience_dock', '');

    $this->get(route('experience'))
        ->assertOk()
        ->assertDontSee('experience-dock', false);
});

test('the dock column renders the image once the asset is configured', function () {
    config()->set('portfolio.hero_images.experience_dock', 'images/experience-dock.webp');

    $this->get(route('experience'))
        ->assertSee('images/experience-dock.webp', false);
});
```

- [ ] **Step 2: Run it to make sure it fails**

```bash
docker exec portfolio-app-1 php artisan test --compact --filter=DockHeroTest
```

Expected: FAIL — `class="dock-hero"` is not in the page.

- [ ] **Step 3: Extract the rotator CSS**

Create `resources/css/components/hero-rotator.css`:

```css
/* ================================================================
   HERO ROTATOR
   ================================================================
   The rotating role line and its blinking caret, shared by the
   full-width page hero and the Experience dock hero. Imported by
   both component stylesheets; the JS that drives it lives in
   resources/js/app.js.
   ================================================================ */

#hero-rotator span {
    color: var(--c-primary);
}

/* Blinking caret next to the rotating role text */
.hero-caret {
    display: inline-block;
    width: 2px;
    height: 0.9em;
    margin-left: 2px;
    background-color: var(--c-primary);
    vertical-align: text-bottom;
    animation: caret-blink 1s step-end infinite;
}

@keyframes caret-blink {
    50% { opacity: 0; }
}
```

In `resources/css/components/page-hero.css`, delete exactly these blocks (lines 57-74):

```css
#hero-rotator span {
    color: var(--c-primary);
}

/* Blinking caret next to the rotating role text */
.hero-caret {
    display: inline-block;
    width: 2px;
    height: 0.9em;
    margin-left: 2px;
    background-color: var(--c-primary);
    vertical-align: text-bottom;
    animation: caret-blink 1s step-end infinite;
}

@keyframes caret-blink {
    50% { opacity: 0; }
}
```

Keep `.underh1`, `.underh1 span` and `.underh1 > span#hero-rotator` — they style the old hero's layout, not the rotator itself. Add as the **first line** of `page-hero.css`:

```css
@import './hero-rotator.css';
```

- [ ] **Step 4: Create the lang files**

Create `resources/lang/en/pages/experience.php`:

```php
<?php

return [
    'hero_wordmark' => 'Experience',
    'hero_dock_label' => 'Navigate',
    'hero_tags' => ['Backend', 'Hardware', 'Competitions', 'Erasmus', 'Speaking'],
];
```

Create `resources/lang/cs/pages/experience.php`:

```php
<?php

return [
    'hero_wordmark' => 'Zkušenosti',
    'hero_dock_label' => 'Navigace',
    'hero_tags' => ['Backend', 'Hardware', 'Soutěže', 'Erasmus', 'Přednášky'],
];
```

- [ ] **Step 5: Create the Blade component**

Create `resources/views/components/portfolio/dock-hero.blade.php`:

```blade
@props([
    'title',
    'eyebrow' => '',
    'roles' => [],
    'tags' => [],
    'wordmark' => '',
    'dockLabel' => '',
    'dockImage' => '',
    'dockImageAlt' => '',
    'photo' => '',
    'photoAlt' => '',
    'caption' => '',
])

{{--
    Bordered three-column opener: a labelled dock column, the copy column
    carrying the outlined wordmark, and a full-bleed photo. Built generic so
    other public pages can adopt it; Experience is the first caller.

    No `portfolio-section` class on purpose — that class starts at opacity 0
    and waits for the scroll observer, which is wrong above the fold.
--}}
<section class="dock-hero">
    <div class="dock-hero-dock">
        @if ($dockLabel !== '')
            <p class="dock-hero-dock-label">{{ $dockLabel }}</p>
        @endif
        @if ($dockImage !== '')
            <img src="{{ asset($dockImage) }}" alt="{{ $dockImageAlt }}">
        @endif
    </div>

    <div class="dock-hero-copy">
        @if ($wordmark !== '')
            <p class="dock-hero-ghost" aria-hidden="true">{{ $wordmark }}</p>
        @endif

        @if ($eyebrow !== '')
            <p class="dock-hero-eyebrow">{{ $eyebrow }}</p>
        @endif

        <h1 class="dock-hero-title">{!! $title !!}</h1>

        @if (count($roles) > 1)
            <p class="dock-hero-roles"><span id="hero-rotator" data-roles='@json($roles)' aria-live="polite">{!! $roles[0] !!}</span><span class="hero-caret" aria-hidden="true"></span></p>
        @endif

        @if ($tags !== [])
            <ul class="dock-hero-tags">
                @foreach ($tags as $tag)
                    <li class="dock-hero-tag">{{ $tag }}</li>
                @endforeach
            </ul>
        @endif
    </div>

    <figure class="dock-hero-photo">
        @if ($photo !== '')
            <img src="{{ asset($photo) }}" alt="{{ $photoAlt }}">
        @endif
        @if ($caption !== '')
            <figcaption class="dock-hero-cap">{!! $caption !!}</figcaption>
        @endif
    </figure>
</section>
```

- [ ] **Step 6: Create the desktop CSS**

Create `resources/css/components/dock-hero.css`. Values are the handoff's, verbatim, minus the metric strip; `min-height` is 640px because the strip is gone.

```css
@import './hero-rotator.css';

/* ================================================================
   DOCK HERO
   ================================================================
   Bordered three-column page opener: dock (fixed 250px) / copy
   (flexible) / photo (fixed 470px). Column widths are fixed on
   purpose — the dock must not grow past the device shot, and the
   photo must not shrink below a readable crop.

   From design_handoff_experience_hero. Deviations from that
   handoff, all deliberate: no metric strip, 640px min-height
   instead of 720px (the strip is what made up the difference),
   the rotator line in place of the static lede, and an eyebrow
   that carries an emoji instead of the rule + letterspacing.
   ================================================================ */

.dock-hero {
    position: relative;
    display: grid;
    grid-template-columns: 250px 1fr 470px;
    min-height: 640px;
    margin-bottom: 2.25rem;
    background: var(--c-bg);
    border: var(--border-w) solid var(--c-primary-fade);
    border-radius: var(--r-card);
    overflow: hidden;
}

/* ── Dock column ── */

.dock-hero-dock {
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding-top: 2.6rem;
    overflow: hidden;
    border-right: 1px solid color-mix(in srgb, var(--c-primary-fade) 55%, transparent);
    background: linear-gradient(to bottom, color-mix(in srgb, var(--c-surface) 70%, transparent), transparent 70%);
}

.dock-hero-dock-label {
    margin-bottom: 1.4rem;
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--c-muted);
}

/* The device shot is meant to be cropped by the column's bottom edge —
   it bleeds out, it is not a centered object. */
.dock-hero-dock img {
    display: block;
    width: 178px;
    filter: drop-shadow(0 24px 50px rgba(0, 0, 0, 0.55));
}

/* ── Copy column ── */

.dock-hero-copy {
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    gap: 1.5rem;
    padding: 4.6rem 3.4rem 3.6rem;
}

/* Outlined page word behind the copy. Lives inside the copy column, not
   the grid: anchored to the grid it gets clipped or collides with the
   bottom edge. */
.dock-hero-ghost {
    position: absolute;
    left: 0;
    top: -0.5rem;
    z-index: 0;
    font-family: var(--font-display);
    font-size: 132px;
    font-weight: 700;
    letter-spacing: -0.04em;
    line-height: 1;
    white-space: nowrap;
    pointer-events: none;
    color: transparent;
    -webkit-text-stroke: 1px color-mix(in srgb, var(--c-primary-fade) 80%, transparent);
}

.dock-hero-eyebrow {
    font-size: 11px;
    font-weight: 600;
    color: var(--c-muted);
}

.dock-hero-title {
    font-family: var(--font-display);
    font-size: 70px;
    font-weight: 700;
    line-height: 0.96;
    letter-spacing: -0.02em;
}

.dock-hero-title span {
    font-weight: 500;
    color: var(--c-primary);
}

.dock-hero-roles {
    max-width: 36ch;
    font-size: 15.5px;
    font-weight: 200;
    line-height: 1.65;
    color: var(--c-muted);
    text-wrap: pretty;
}

.dock-hero-roles > #hero-rotator {
    color: var(--c-fg);
    font-weight: 600;
}

/* Summary chips — display only. Deliberately smaller and dot-less so they
   do not read as the filter bar's .exp-tag buttons further down the page. */
.dock-hero-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    list-style: none;
    padding: 0;
    margin: 0;
}

.dock-hero-tag {
    padding: 0.4rem 0.8rem;
    border: 1px solid color-mix(in srgb, var(--c-primary-fade) 75%, transparent);
    border-radius: 999px;
    font-size: 11.5px;
    font-weight: 500;
    letter-spacing: 0.02em;
    color: var(--c-muted);
    cursor: default;
    transition: var(--t-fast);
}

.dock-hero-tag:hover {
    border-color: var(--c-primary);
    color: var(--c-fg);
}

/* ── Photo column ── */

.dock-hero-photo {
    position: relative;
    z-index: 2;
    overflow: hidden;
    margin: 0;
}

.dock-hero-photo img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: 52% 22%;
}

/* Softens the seam against the copy column. */
.dock-hero-photo::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(to right, color-mix(in srgb, var(--c-bg) 65%, transparent), transparent 26%);
}

/* The dark bottom wash exists only to carry caption text. */
.dock-hero-photo:has(.dock-hero-cap)::after {
    background:
        linear-gradient(to top, rgba(19, 18, 16, 0.9) 0 18%, transparent 55%),
        linear-gradient(to right, color-mix(in srgb, var(--c-bg) 65%, transparent), transparent 26%);
}

/* Caption colours are literal, not tokens: they sit on the photo's dark
   gradient in both themes. */
.dock-hero-cap {
    position: absolute;
    left: 2rem;
    right: 1.8rem;
    bottom: 1.5rem;
    z-index: 3;
    font-size: 11.5px;
    font-weight: 200;
    line-height: 1.55;
    color: #D6D3D1;
}

.dock-hero-cap b {
    display: block;
    margin-bottom: 0.15rem;
    font-size: 12.5px;
    font-weight: 600;
    letter-spacing: 0.01em;
    color: #fff;
}
```

- [ ] **Step 7: Swap the imports and the page markup**

In `resources/css/pages/experience.css`, replace line 1:

```css
@import '../components/page-hero.css';
```

with:

```css
@import '../components/dock-hero.css';
```

In `config/portfolio.php`, inside `hero_images`, add after the `experience` line:

```php
        /* Device shot in the Experience hero's dock column. Empty until the
           clean transparent export lands — the column then renders label-only. */
        'experience_dock' => '',
```

In `resources/views/experience.blade.php`, replace the whole `<x-portfolio.page-hero ... />` call (lines 5-12) with:

```blade
    <x-portfolio.dock-hero
        :eyebrow="\App\Models\Setting::text('experience_hero_suptitle', $locale)"
        :title="\App\Models\Setting::text('experience_hero_title', $locale)"
        :roles="\App\Models\Setting::list('experience_hero_roles', $locale)"
        :tags="__('pages/experience.hero_tags')"
        :wordmark="__('pages/experience.hero_wordmark')"
        :dock-label="__('pages/experience.hero_dock_label')"
        :dock-image="config('portfolio.hero_images.experience_dock')"
        dock-image-alt=""
        :photo="config('portfolio.hero_images.experience')"
        photo-alt=""
    />
```

Both images are decorative next to the copy that names them, so both alts stay empty. Task 4 moves `:tags` onto a setting and adds `:caption`.

- [ ] **Step 8: Update the shared-hero tests to stop expecting a page hero on Experience**

In `tests/Feature/PageHeroTest.php`:

- In `'every public subpage renders exactly one h1'`, change the dataset from `['about-me', 'experience', 'projects']` to `['about-me', 'projects']`.
- In `'the subpage heroes render the czech copy under the cs locale'`, delete the `experience` half (its `Setting::updateOrCreate` line and its `route('experience')` request), keeping `projects`. Experience's cs coverage now lives in `DockHeroTest`.

In `tests/Browser/PageHeroTest.php`, drop `/experience` from all four datasets:

- `'a subpage hero fills the first screen and still lets the next section peek in'` → `['/about-me', '/projects']`
- `'a subpage hero fills the first screen and still lets the next section peek in on mobile'` → `['/about-me', '/projects']`
- `'every hero centres its text on the same line as the home hero'` → `['/', '/about-me', '/projects']`
- `'the section after a subpage hero fades in on scroll like every other section'` → `['/about-me', '/projects']`

- [ ] **Step 9: Build and run the tests**

Host:

```bash
npm run build
```

Then:

```bash
docker exec portfolio-app-1 php artisan test --compact --filter=DockHeroTest
docker exec portfolio-app-1 php artisan test --compact --filter=PageHeroTest
```

Expected: both PASS.

- [ ] **Step 10: Commit**

```bash
docker exec portfolio-app-1 vendor/bin/pint --dirty --format agent
git add resources/views/components/portfolio/dock-hero.blade.php resources/css/components/dock-hero.css resources/css/components/hero-rotator.css resources/css/components/page-hero.css resources/css/pages/experience.css resources/views/experience.blade.php config/portfolio.php resources/lang/en/pages/experience.php resources/lang/cs/pages/experience.php tests/Feature/DockHeroTest.php tests/Feature/PageHeroTest.php tests/Browser/PageHeroTest.php
git commit -m "feat(experience): open the page with the dock hero"
```

- [ ] **Step 11: Screenshot desktop, both themes, and STOP**

Confirm the app serves the page:

```bash
curl -s -o /dev/null -w '%{http_code}\n' http://localhost:8008/experience
```

Expected `200`; a 500 means the Vite manifest is stale — re-run `npm run build` on the host.

Write this to the session scratchpad as `shots.mjs` and run `node shots.mjs` there:

```js
import { chromium } from 'playwright';

const browser = await chromium.launch();

for (const theme of ['dark', 'light']) {
    const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
    await page.goto('http://localhost:8008/experience', { waitUntil: 'networkidle' });
    await page.evaluate((t) => {
        document.documentElement.classList.toggle('dark', t === 'dark');
    }, theme);
    await page.waitForTimeout(400);
    await page.screenshot({ path: `hero-1440-${theme}.png` });
    await page.close();
}

await browser.close();
```

If `playwright` does not resolve from the scratchpad, run node from the repo root — the package lives in the bind-mounted `node_modules` there.

**Send both screenshots to the owner and stop.** Collect their comments before Task 2; anything about proportions, crop, colour or copy belongs in Task 3.

---

### Task 2: Responsive stacking

**Files:**
- Modify: `resources/css/components/dock-hero.css` (append media queries)
- Test: `tests/Browser/DockHeroTest.php` (create)

**Interfaces:**
- Consumes: every `.dock-hero*` class from Task 1.
- Produces: no new names — only media queries over the existing ones.

- [ ] **Step 1: Write the failing test**

Create `tests/Browser/DockHeroTest.php`:

```php
<?php

beforeEach(function () {
    $this->seed(\Database\Seeders\SettingSeeder::class);
});

/** Column geometry of the three children, as integers. */
$boxesJs = <<<'JS'
    (() => {
        const box = (s) => {
            const r = document.querySelector(s).getBoundingClientRect();
            return { left: Math.round(r.left), right: Math.round(r.right), top: Math.round(r.top), width: Math.round(r.width) };
        };
        return {
            dock: box('.dock-hero-dock'),
            copy: box('.dock-hero-copy'),
            photo: box('.dock-hero-photo'),
        };
    })()
JS;

test('the dock hero puts its three columns side by side on desktop', function () use ($boxesJs) {
    $page = visit('/experience')->resize(1440, 900);

    $boxes = $page->script($boxesJs);

    expect($boxes['copy']['left'])->toBeGreaterThanOrEqual($boxes['dock']['right'] - 1)
        ->and($boxes['photo']['left'])->toBeGreaterThanOrEqual($boxes['copy']['right'] - 1)
        ->and($boxes['dock']['width'])->toBe(250)
        ->and($boxes['photo']['width'])->toBe(470);
});

test('the dock hero stacks dock, copy then photo below 1200px', function (int $width) use ($boxesJs) {
    $page = visit('/experience')->resize($width, 900);

    $boxes = $page->script($boxesJs);

    expect($boxes['copy']['top'])->toBeGreaterThan($boxes['dock']['top'])
        ->and($boxes['photo']['top'])->toBeGreaterThan($boxes['copy']['top'])
        ->and($boxes['photo']['left'])->toBe($boxes['copy']['left']);
})->with([1100, 520]);

test('the wordmark stays inside the hero at every width', function (int $width) {
    $page = visit('/experience')->resize($width, 900);

    $fits = <<<'JS'
        (() => {
            const hero = document.querySelector('.dock-hero').getBoundingClientRect();
            const ghost = document.querySelector('.dock-hero-ghost').getBoundingClientRect();
            return ghost.top >= hero.top - 1;
        })()
    JS;

    expect($page->script($fits))->toBeTrue();
})->with([1440, 1100, 520]);

test('the hero hands off to the filter bar below it', function () {
    $page = visit('/experience')->resize(1440, 900);

    $gap = <<<'JS'
        (() => {
            const hero = document.querySelector('.dock-hero').getBoundingClientRect();
            const bar = document.querySelector('.exp-filterbar').getBoundingClientRect();
            return Math.round(bar.top - hero.bottom);
        })()
    JS;

    expect($page->script($gap))->toBeGreaterThan(0);
});

test('the dock hero rotator cycles its roles', function () {
    $page = visit('/experience')->resize(1440, 900);

    $read = "document.getElementById('hero-rotator').textContent";
    $first = $page->script($read);
    $page->wait(3);

    expect($page->script($read))->not->toBe($first);
});

test('the dock hero border thickens in the light theme', function () {
    $page = visit('/experience')->resize(1440, 900);

    $borderJs = <<<'JS'
        (() => {
            document.documentElement.classList.remove('dark');
            return getComputedStyle(document.querySelector('.dock-hero')).borderTopWidth;
        })()
    JS;

    expect($page->script($borderJs))->toBe('2px');
});
```

- [ ] **Step 2: Run it to see which parts fail**

```bash
docker exec portfolio-app-1 php artisan test --compact --filter=DockHeroTest
```

Expected: the desktop, wordmark-at-1440, handoff, rotator and light-theme tests PASS (Task 1 delivered those); the stacking test and the wordmark tests at 1100/520 FAIL — no media queries yet.

If the very first `visit()` times out, that is the known ~17s first-visit cost: re-run once the app is warm.

- [ ] **Step 3: Append the media queries**

Add to the end of `resources/css/components/dock-hero.css`:

```css
/* ── Responsive ── */

/* Below 1200px the three columns stack. The dock turns into a horizontal
   row so the device shot does not eat a whole screen, and the copy column
   buys headroom for the wordmark that now sits above it. */
@media (max-width: 1200px) {
    .dock-hero {
        grid-template-columns: 1fr;
        min-height: 0;
    }

    .dock-hero-dock {
        flex-direction: row;
        align-items: flex-end;
        gap: 1.6rem;
        padding: 2rem 2rem 0;
        border-right: 0;
        border-bottom: 1px solid var(--c-primary-fade);
    }

    .dock-hero-dock-label {
        margin-bottom: 0.4rem;
    }

    .dock-hero-dock img {
        width: 130px;
    }

    .dock-hero-copy {
        padding: 9.5rem 2rem 2.5rem;
    }

    .dock-hero-ghost {
        left: -0.5rem;
        top: 1.4rem;
        font-size: clamp(64px, 11vw, 120px);
    }

    .dock-hero-photo {
        min-height: 380px;
    }

    .dock-hero-photo::after,
    .dock-hero-photo:has(.dock-hero-cap)::after {
        background: linear-gradient(to top, rgba(19, 18, 16, 0.9) 0 22%, transparent 60%);
    }
}

@media (max-width: 560px) {
    .dock-hero-title {
        font-size: clamp(38px, 11vw, 70px);
    }

    .dock-hero-copy {
        padding: 8rem 1.4rem 2rem;
    }

    .dock-hero-cap {
        left: 1.4rem;
        right: 1.2rem;
    }
}
```

- [ ] **Step 4: Rebuild and re-run**

Host:

```bash
npm run build
```

Then:

```bash
docker exec portfolio-app-1 php artisan test --compact --filter=DockHeroTest
```

Expected: PASS, every test in both `tests/Feature/DockHeroTest.php` and `tests/Browser/DockHeroTest.php`.

- [ ] **Step 5: Commit**

```bash
git add resources/css/components/dock-hero.css tests/Browser/DockHeroTest.php
git commit -m "feat(experience): stack the dock hero below 1200px"
```

- [ ] **Step 6: Screenshot the narrow widths and STOP**

Re-run the `shots.mjs` script from Task 1 with the viewport list `[[1100, 900], [390, 844]]` instead of the single 1440 size, then send those four images (two widths × two themes) to the owner and wait for comments before Task 3.

---

### Task 3: Fold in the owner's comments

Everything the owner asked for after Tasks 1 and 2, plus the one value that can only be judged by eye: how the reused portrait crops inside the photo column.

**Files:**
- Modify: `resources/css/components/dock-hero.css`
- Modify: whichever of `resources/views/components/portfolio/dock-hero.blade.php`, `resources/lang/{en,cs}/pages/experience.php` the comments touch
- Test: `tests/Feature/DockHeroTest.php`, `tests/Browser/DockHeroTest.php` (update only if behaviour changed)

**Interfaces:**
- Consumes: Tasks 1 and 2.
- Produces: nothing new. If a comment demands a new prop, add it to the `@props` list with a safe default and note it here so Task 4 knows.

- [ ] **Step 1: List the comments as concrete changes**

Write each comment down as a file + value change before touching anything. If a comment is ambiguous ("the photo feels heavy"), ask which of the two readings is meant rather than guessing.

- [ ] **Step 2: Judge the crop and the seam**

Three things to check against the screenshots:
1. The portrait's subject inside the photo column — if the head is cut or drifts off-centre, change `object-position: 52% 22%` in `.dock-hero-photo img`. This is the only value in the file meant to be tuned by eye.
2. The left gradient wash where the photo meets the copy column: it should soften the seam, not wash the picture out. In the light theme it uses `--c-bg` (`#F3F1EC`) — check it does not read as a white smear.
3. The wordmark stroke against `--c-bg` in the light theme — `--c-primary-fade` is `#FDE68A` there and must still be visible without shouting.

- [ ] **Step 3: Apply the changes and rebuild**

Host: `npm run build`.

- [ ] **Step 4: Re-shoot and re-run**

```bash
docker exec portfolio-app-1 php artisan test --compact --filter=DockHeroTest
```

Expected: PASS. Re-run `shots.mjs` at all three widths and send the images.

- [ ] **Step 5: Commit**

```bash
docker exec portfolio-app-1 vendor/bin/pint --dirty --format agent
git add -A resources docs
git commit -m "fix(experience): tune the dock hero from review"
```

Skip if nothing changed.

---

### Task 4: Move the tags and add the photo caption to the admin

Now that the hero is agreed, its two hero-specific copy fields become editable. `experience_hero_tags` takes over from the lang array; `experience_hero_photo_caption` is new and starts empty. Both are **optional** — the hero must render without them — which the admin page has no notion of today.

**Files:**
- Modify: `database/seeders/SettingSeeder.php:12-42`
- Modify: `resources/views/pages/manage/⚡site-content.blade.php`
- Modify: `resources/views/experience.blade.php` (the `:tags` line, plus a new `:caption` line)
- Modify: `resources/lang/en/pages/experience.php`, `resources/lang/cs/pages/experience.php` (drop `hero_tags`)
- Modify: `tests/Feature/DockHeroTest.php` (the tags assertions move from lang to settings)
- Test: `tests/Feature/SiteContentDockHeroTest.php` (create)

**Interfaces:**
- Consumes: the component's `tags` and `caption` props from Task 1.
- Produces:
  - `Setting` key `experience_hero_tags` → `['en' => string[], 'cs' => string[]]`, read with `Setting::list()`.
  - `Setting` key `experience_hero_photo_caption` → `['en' => string, 'cs' => string]`, read with `Setting::text()`; may be `''`.
  - Livewire public property `array $optionalKeys` and method `isOptional(string $key): bool` on the site-content component.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/SiteContentDockHeroTest.php`:

```php
<?php

use App\Models\Setting;
use App\Models\User;
use Livewire\Volt\Volt;

test('the seeder provides the dock hero tags and an empty photo caption', function () {
    $this->seed(\Database\Seeders\SettingSeeder::class);

    expect(Setting::list('experience_hero_tags', 'en'))->toHaveCount(5)
        ->and(Setting::list('experience_hero_tags', 'cs'))->toHaveCount(5)
        ->and(Setting::text('experience_hero_photo_caption', 'en'))->toBe('')
        ->and(Setting::where('key', 'experience_hero_roles')->exists())->toBeTrue();
});

test('the site content page saves the dock hero fields', function () {
    $this->seed(\Database\Seeders\SettingSeeder::class);

    $this->actingAs(User::factory()->create());

    Volt::test('pages.manage.site-content')
        ->set('roleLists.experience_hero_tags.en', "Backend\nHardware")
        ->set('roleLists.experience_hero_tags.cs', "Backend\nHardware")
        ->set('texts.experience_hero_photo_caption.en', '<b>Tour de App, 2024</b>Regional finals.')
        ->set('texts.experience_hero_photo_caption.cs', '<b>Tour de App, 2024</b>Krajské finále.')
        ->call('save')
        ->assertHasNoErrors();

    expect(Setting::list('experience_hero_tags', 'en'))->toEqual(['Backend', 'Hardware'])
        ->and(Setting::text('experience_hero_photo_caption', 'cs'))->toBe('<b>Tour de App, 2024</b>Krajské finále.');
});

test('the optional dock hero fields may be saved empty', function () {
    $this->seed(\Database\Seeders\SettingSeeder::class);

    $this->actingAs(User::factory()->create());

    Volt::test('pages.manage.site-content')
        ->set('texts.experience_hero_photo_caption.en', '')
        ->set('texts.experience_hero_photo_caption.cs', '')
        ->set('roleLists.experience_hero_tags.en', '')
        ->set('roleLists.experience_hero_tags.cs', '')
        ->call('save')
        ->assertHasNoErrors();

    expect(Setting::text('experience_hero_photo_caption', 'en'))->toBe('')
        ->and(Setting::list('experience_hero_tags', 'en'))->toEqual([]);
});

test('the required hero fields still reject an empty english value', function () {
    $this->seed(\Database\Seeders\SettingSeeder::class);

    $this->actingAs(User::factory()->create());

    Volt::test('pages.manage.site-content')
        ->set('texts.experience_hero_title.en', '')
        ->call('save')
        ->assertHasErrors('texts.experience_hero_title.en');
});

test('the hero renders the caption once the setting carries one', function () {
    $this->seed(\Database\Seeders\SettingSeeder::class);

    $this->get(route('experience'))->assertDontSee('dock-hero-cap', false);

    Setting::updateOrCreate(['key' => 'experience_hero_photo_caption'], ['value' => [
        'en' => '<b>Tour de App, 2024</b>Regional finals jury.',
        'cs' => '<b>Tour de App, 2024</b>Krajské finále.',
    ]]);

    $this->get(route('experience'))
        ->assertSee('dock-hero-cap', false)
        ->assertSee('<b>Tour de App, 2024</b>Regional finals jury.', false);
});

test('the hero renders its tags from the setting, not the lang file', function () {
    $this->seed(\Database\Seeders\SettingSeeder::class);

    Setting::updateOrCreate(['key' => 'experience_hero_tags'], ['value' => [
        'en' => ['Only', 'Two'],
        'cs' => ['Jen', 'Dva'],
    ]]);

    $html = $this->get(route('experience'))->assertOk()->getContent();

    expect(substr_count($html, 'dock-hero-tag"'))->toBe(2);

    $this->get(route('experience'))->assertSee('>Only</li>', false);
});
```

Note on the Volt component name: if `Volt::test('pages.manage.site-content')` cannot resolve, check how sibling tests in `tests/Feature/` address the other `⚡` manage pages and copy that exact string.

- [ ] **Step 2: Run it to make sure it fails**

```bash
docker exec portfolio-app-1 php artisan test --compact --filter=SiteContentDockHeroTest
```

Expected: FAIL — neither setting key exists.

- [ ] **Step 3: Add the new keys to the seeder**

In `database/seeders/SettingSeeder.php`, inside `$settings`, directly after the `experience_hero_roles` entry:

```php
            'experience_hero_tags' => [
                'en' => ['Backend', 'Hardware', 'Competitions', 'Erasmus', 'Speaking'],
                'cs' => ['Backend', 'Hardware', 'Soutěže', 'Erasmus', 'Přednášky'],
            ],
            'experience_hero_photo_caption' => ['en' => '', 'cs' => ''],
```

- [ ] **Step 4: Teach the admin page about optional fields**

In `resources/views/pages/manage/⚡site-content.blade.php`:

Add `'experience_hero_photo_caption'` to the end of `$textKeys`, and `'experience_hero_tags'` to the end of `$roleListKeys`.

Add below `$roleListKeys`:

```php
    /**
     * Keys whose English value may be left blank. Everything else is
     * required — an empty hero title would render a hole on the site.
     *
     * @var array<int, string>
     */
    public array $optionalKeys = [
        'experience_hero_tags',
        'experience_hero_photo_caption',
    ];
```

Change the `Experience hero` group to:

```php
        'Experience hero' => ['experience_hero_suptitle', 'experience_hero_title', 'experience_hero_roles', 'experience_hero_tags', 'experience_hero_photo_caption'],
```

In `save()`, replace the two rule-building loops with:

```php
        foreach ($this->textKeys as $key) {
            $rules["texts.{$key}.en"] = [$this->isOptional($key) ? 'nullable' : 'required', 'string', 'max:2000'];
            $rules["texts.{$key}.cs"] = ['nullable', 'string', 'max:2000'];
        }

        foreach ($this->roleListKeys as $key) {
            $rules["roleLists.{$key}.en"] = [$this->isOptional($key) ? 'nullable' : 'required', 'string'];
            $rules["roleLists.{$key}.cs"] = ['nullable', 'string'];
        }
```

Add the helper next to `isRoleList()`:

```php
    /** True when a key may be saved with an empty English value. */
    public function isOptional(string $key): bool
    {
        return in_array($key, $this->optionalKeys, true);
    }
```

In the Blade half, in the **`en` slot only**, replace:

```blade
                            <flux:label>{{ $this->label($key) }} <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
```

with:

```blade
                            <flux:label>
                                {{ $this->label($key) }}
                                @unless ($this->isOptional($key))
                                    <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge>
                                @endunless
                            </flux:label>
```

The `cs` slot's label carries no badge already — leave it. `save()`'s existing `?:` fallback (cs falls back to en) is correct for the optional keys too: both empty yields empty.

- [ ] **Step 5: Point the page at the settings**

In `resources/views/experience.blade.php`, change the tags line and add the caption line:

```blade
        :tags="\App\Models\Setting::list('experience_hero_tags', $locale)"
        :caption="\App\Models\Setting::text('experience_hero_photo_caption', $locale)"
```

Delete the now-unused `hero_tags` array from both `resources/lang/en/pages/experience.php` and `resources/lang/cs/pages/experience.php`. `hero_wordmark` and `hero_dock_label` stay — they are structural, not prose.

- [ ] **Step 6: Update the Task 1 tags assertions**

In `tests/Feature/DockHeroTest.php`, the test `'the dock hero renders the wordmark, dock label and tags from the lang files'` now over-claims. Rename it to `'the dock hero renders the wordmark and dock label from the lang files'` and delete its four tag assertions (`>Backend</li>`, `>Erasmus</li>`, `>Soutěže</li>`). Tag coverage now lives in `SiteContentDockHeroTest`.

Also delete `'the dock hero omits the caption markup when no caption is passed'` — `SiteContentDockHeroTest` covers both the empty and filled caption cases against the real source.

- [ ] **Step 7: Run the tests and seed**

```bash
docker exec portfolio-app-1 php artisan test --compact --filter=SiteContentDockHeroTest
docker exec portfolio-app-1 php artisan test --compact --filter=DockHeroTest
docker exec portfolio-app-1 php artisan db:seed --class=SettingSeeder --no-interaction
```

Expected: both suites PASS.

- [ ] **Step 8: Format and commit**

```bash
docker exec portfolio-app-1 vendor/bin/pint --dirty --format agent
git add database/seeders/SettingSeeder.php "resources/views/pages/manage/⚡site-content.blade.php" resources/views/experience.blade.php resources/lang/en/pages/experience.php resources/lang/cs/pages/experience.php tests/Feature/SiteContentDockHeroTest.php tests/Feature/DockHeroTest.php
git commit -m "feat(settings): make the dock hero tags and caption admin-editable"
```

---

### Task 5: Whole-suite verification and handoff

**Files:** none expected. Fix whatever the suite turns up.

- [ ] **Step 1: Run everything**

```bash
docker exec portfolio-app-1 php artisan test --compact
```

Expected: PASS. A failure elsewhere means something depended on Experience rendering `.hero-page` — fix it here, do not leave it.

- [ ] **Step 2: Check the admin page renders**

Log into http://localhost:8008 and open the Site content page; confirm the Experience hero group shows five fields, with `Tags` as a textarea and `Photo caption` as an input, neither carrying the Required badge.

- [ ] **Step 3: Report what still needs the owner**

State plainly in the final message:
- The dock column stays empty until a clean transparent device export lands at `public/images/experience-dock.webp` and `config('portfolio.hero_images.experience_dock')` points at it (~356px wide, no baked-in shadow — the CSS drop-shadow supplies it).
- The photo caption stays hidden until it is filled in on the admin's Site content page.
- The photo column reuses `public/images/experience-hero.webp`; a wider source (~1400×1500) would keep the `object-fit: cover` crop sharper.

---

## Notes for whoever executes this

- Tasks are strictly ordered, and Tasks 1 and 2 each end with a stop-and-show step. Do not roll past them.
- Task 1 is the only one that changes what a visitor sees on first render; Task 4 changes where its copy comes from, not how it looks.
- If a test fails for a reason this plan did not predict, use `superpowers:systematic-debugging` — do not loosen the assertion to make it pass.
- The design handoff (`README.md` in the zip) is the authority on any value this plan does not quote. Its metric strip section is deliberately not implemented.
