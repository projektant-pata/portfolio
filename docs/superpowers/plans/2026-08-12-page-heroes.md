# Page Heroes for the Public Subpages — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Give About me, Experience and Projects a real `<h1>` hero built from one shared Blade component, sized to stop just short of the fold so the next section peeks in, with all copy editable per-locale in the admin.

**Architecture:** Extract the existing home-hero markup into `x-portfolio.page-hero` and the existing hero CSS into `resources/css/components/page-hero.css`, imported by all four page stylesheets. A single `--hero-peek` token drives `min-height: calc(100svh - var(--hero-peek))`; the home page opts into `.hero-page--full` for the unchanged full-viewport look. Copy lives in the `settings` table (nine new keys) and is edited in the generalised `⚡site-content` Livewire page.

**Tech Stack:** Laravel 13, Livewire 4 (single-file `⚡` pages), Flux UI free, Tailwind v4 + hand-written CSS, Pest 4 (feature + `pest-plugin-browser`), PostgreSQL 17, Docker.

**Source spec:** `docs/superpowers/specs/2026-08-12-page-heroes-design.md`

## Global Constraints

- Artisan / Pint / tests run in the container: `docker exec portfolio-app-1 …`.
- Vite builds run **on the host**: `npm run build`. Required after any CSS change and **before** any browser test run.
- Run Pint after touching PHP: `docker exec portfolio-app-1 vendor/bin/pint --dirty --format agent`.
- Translatable JSON columns are asserted with `toEqual`, never `toBe` (Postgres reorders `jsonb` keys).
- Exactly one `<h1>` per public page. Watermark headings stay `<h2>`.
- No pixel constants for hero height — `rem` / `svh` only.
- `svh`, not `vh`, for hero heights.
- Class names are preserved **verbatim** when hero CSS moves out of `index.css`.
- Copy is taken verbatim from the spec's Copy tables, including the `<span>` accents and emoji.
- Hero images come from `config('portfolio.hero_images.*')`, never hardcoded in a template.

---

### Task 1: Characterize the home hero geometry before touching CSS

Risk 1 in the spec: moving hero CSS out of `index.css` can silently regress the home page. Lock the current geometry in a browser test *first*, so the later move is verified rather than assumed.

**Files:**
- Create: `tests/Browser/PageHeroTest.php`

**Interfaces:**
- Consumes: nothing.
- Produces: `tests/Browser/PageHeroTest.php` — later tasks append tests to this same file. The helper JS snippets defined here (`$heroPeekJs`) are reused in Task 7.

- [ ] **Step 1: Write the home-hero geometry test**

Create `tests/Browser/PageHeroTest.php`:

```php
<?php

/**
 * Distance in px between the bottom of the hero and the bottom of the
 * viewport. Positive = the next section peeks in; <= 0 = the hero fills
 * (or overflows) the first screen.
 */
$heroPeekJs = <<<'JS'
    (() => {
        const hero = document.querySelector('.hero-page');
        return Math.round(window.innerHeight - hero.getBoundingClientRect().bottom);
    })()
JS;

test('the home hero fills the first screen', function () use ($heroPeekJs) {
    $page = visit('/')->resize(1440, 900);

    expect($page->script($heroPeekJs))->toBeLessThanOrEqual(1);
});

test('the home hero keeps its side-by-side desktop layout', function () {
    $page = visit('/')->resize(1440, 900);

    $sideBySide = <<<'JS'
        (() => {
            const text = document.querySelector('.hero-page-text').getBoundingClientRect();
            const image = document.querySelector('.hero-page-image').getBoundingClientRect();
            return image.left >= text.right - 1;
        })()
    JS;

    expect($page->script($sideBySide))->toBeTrue();
});

test('the home hero rotator cycles its roles', function () {
    $page = visit('/')->resize(1440, 900);

    $read = "document.getElementById('hero-rotator').textContent";
    $first = $page->script($read);
    $page->wait(3);

    expect($page->script($read))->not->toBe($first);
});
```

- [ ] **Step 2: Build assets and run the test to verify it passes against the current code**

On the host:

```bash
npm run build
```

Then:

```bash
docker exec portfolio-app-1 php artisan test --compact tests/Browser/PageHeroTest.php
```

Expected: PASS (3 tests). This is a characterization test — it must be green *before* the refactor. If the rotator test is flaky, raise the `wait(3)` rather than weakening the assertion.

- [ ] **Step 3: Commit**

```bash
git add tests/Browser/PageHeroTest.php
git commit -m "test(hero): pin home hero geometry before the CSS extraction"
```

---

### Task 2: Extract the hero CSS into a shared component stylesheet

Move the hero rules verbatim out of `index.css` into `resources/css/components/page-hero.css`, add the `--hero-peek` token and the `--full` variant, and import the new file from all four page stylesheets — the same pattern `project-row.css` already uses, so `vite.config.js` needs no change.

**Files:**
- Create: `resources/css/components/page-hero.css`
- Modify: `resources/css/app.css` (`@theme` block, after `--sp-card-pad`)
- Modify: `resources/css/pages/index.css` (remove hero rules, add `@import`)
- Modify: `resources/css/pages/about-me.css` (add `@import`)
- Modify: `resources/css/pages/experience.css` (add `@import`)
- Modify: `resources/css/pages/projects.css` (add `@import`)
- Modify: `resources/views/welcome.blade.php:7` (add `hero-page--full`)
- Test: `tests/Browser/PageHeroTest.php` (existing, unchanged — it is the verification)

**Interfaces:**
- Consumes: nothing.
- Produces:
  - CSS token `--hero-peek` on `:root`.
  - Classes `.hero-page`, `.hero-page--full`, `.hero-page-text`, `.hero-page-image`, `.hero-suptitle`, `.underh1`, `.hero-caret`, `#hero-rotator`, `.hero-loaded` — all names unchanged from today.
  - `resources/css/components/page-hero.css` is the single home of hero styling; page stylesheets `@import '../components/page-hero.css';` as their first line.

- [ ] **Step 1: Add the `--hero-peek` token**

In `resources/css/app.css`, inside the `@theme` block, directly after the `--sp-card-pad` line:

```css
    /* ── Hero ──────────────────────────────────────────────────
       How much of the next section stays visible under a subpage
       hero — the sliver that invites the scroll. Fluid, no px.   */
    --hero-peek: clamp(4rem, 11svh, 8rem);
```

- [ ] **Step 2: Create the hero component stylesheet**

Create `resources/css/components/page-hero.css` with the rules moved verbatim from `index.css` (lines 8–84 today) plus the new height rules and the responsive rules lifted out of the media queries at the bottom of `index.css`:

```css
/* ================================================================
   PAGE HERO — shared by every public page
   ================================================================
   Moved out of pages/index.css so the four public pages can share
   one hero. Class names are unchanged; @import-ed by index.css,
   about-me.css, experience.css and projects.css, so vite.config.js
   needs no new entry. */

.hero-page {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    /* Stop short of the fold so the next section's watermark peeks
       in. `svh` (not `vh`) so mobile browser chrome cannot make the
       hero jump. margin-bottom is dropped: the peek would otherwise
       be spent on the .portfolio-section gap instead of content. */
    min-height: calc(100svh - var(--hero-peek));
    margin-bottom: 0;
}

/* Home only: the full-viewport variant, with the normal section gap. */
.hero-page--full {
    min-height: 100svh;
    margin-bottom: var(--sp-section);
}

.hero-page-text .hero-suptitle {
    font-size: var(--fs-base);
    font-weight: var(--fw-regular);
    color: var(--c-muted);
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: 1.8rem;
}

/* Hero subtitle below h1 — small, muted, with gold accent on span */
.underh1 {
    font-size: var(--fs-h4);
    font-weight: var(--fw-light);
    color: var(--c-muted);
    letter-spacing: 0.04em;
    line-height: 1.1;
    margin-top: 1.2rem;
}

.underh1 span {
    color: var(--c-primary);
    font-weight: var(--fw-semibold);
}

.underh1 > span#hero-rotator {
    color: var(--c-fg);
    font-weight: var(--fw-semibold);
}

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

.hero-page-image img { width: 400px; }

/* ── Hero entrance (staggered fade-up on load) ── */
.hero-page-text > *,
.hero-page-image {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity var(--t-base), transform var(--t-base);
}

.hero-page.hero-loaded .hero-suptitle    { transition-delay: 0.05s; }
.hero-page.hero-loaded h1                { transition-delay: 0.15s; }
.hero-page.hero-loaded .underh1          { transition-delay: 0.25s; }
.hero-page.hero-loaded .hero-page-image  { transition-delay: 0.35s; }

.hero-page.hero-loaded .hero-page-text > *,
.hero-page.hero-loaded .hero-page-image {
    opacity: 1;
    transform: none;
}

/* ── Responsive ── */

/* Notebook: narrower column, smaller portrait */
@media (min-width: 993px) and (max-width: 1440px) {
    .hero-page-image img { width: 340px; }
}

/* Tablet + mobile: stack. The old `height: auto; margin-top: 10vh`
   is gone — min-height plus centring keeps the peek honest on a
   phone instead of pushing the next section off-screen. */
@media (max-width: 992px) {
    .hero-page {
        flex-direction: column;
        justify-content: center;
        gap: 2rem;
    }
    .hero-page--full { margin-bottom: var(--sp-section); }
}
```

- [ ] **Step 3: Strip the moved rules from `index.css`**

In `resources/css/pages/index.css`:

1. Add the import directly under the existing `project-row` import at line 1, so the file starts:

```css
@import '../components/project-row.css';
@import '../components/page-hero.css';
```

2. Delete the whole `/* ── Hero ── */` block (from `.hero-page {` through the `.hero-page.hero-loaded …{ opacity: 1; transform: none; }` rule, including `@keyframes caret-blink`), leaving the `/* ── Stats ── */` section as the first rule after the file header.
3. In the `@media (min-width: 993px) and (max-width: 1440px)` block, delete the `.hero-page-image img { width: 340px; }` line.
4. In the `@media (max-width: 992px)` block, delete the whole `.hero-page { … }` rule and the `.hero-page img { margin-top: 50px; }` line.

Nothing else in `index.css` changes.

- [ ] **Step 4: Add the import to the three subpage stylesheets**

Make `resources/css/pages/about-me.css` and `resources/css/pages/experience.css` start with:

```css
@import '../components/page-hero.css';
```

(as the very first line, above their existing header comment), and make `resources/css/pages/projects.css` start with:

```css
@import '../components/project-row.css';
@import '../components/page-hero.css';
```

CSS `@import` must precede all other rules, so it goes above the comment banner in each file.

- [ ] **Step 5: Opt the home hero into the full-viewport variant**

In `resources/views/welcome.blade.php`, line 7:

```blade
    <section class="hero-page hero-page--full portfolio-section">
```

- [ ] **Step 6: Rebuild and re-run the characterization test**

On the host:

```bash
npm run build
```

Then:

```bash
docker exec portfolio-app-1 php artisan test --compact tests/Browser/PageHeroTest.php
```

Expected: PASS (3 tests) — identical to Task 1. If the peek assertion now fails, the extraction dropped a rule; diff `page-hero.css` against `git show HEAD~1:resources/css/pages/index.css`.

- [ ] **Step 7: Commit**

```bash
git add resources/css/app.css resources/css/components/page-hero.css resources/css/pages resources/views/welcome.blade.php
git commit -m "refactor(css): extract the hero into a shared page-hero stylesheet"
```

---

### Task 3: Build the `x-portfolio.page-hero` component and adopt it on the home page

One shared Blade component, markup identical to today's home hero so the rotator JS (`#hero-rotator`) and the staggered entrance (`.hero-loaded`) keep working untouched.

**Files:**
- Create: `resources/views/components/portfolio/page-hero.blade.php`
- Modify: `resources/views/welcome.blade.php:6-18` (replace the inline hero)
- Modify: `config/portfolio.php` (add the `hero_images` map)
- Modify: `tests/Feature/HomePageTest.php` (the `hero_roles markup survives` test)
- Test: `tests/Feature/PageHeroTest.php` (new)

**Interfaces:**
- Consumes: `.hero-page` / `.hero-page--full` from Task 2.
- Produces: `<x-portfolio.page-hero />` with props
  - `title` — string, **required**, raw HTML, rendered inside `<h1>` with `{!! !!}`
  - `eyebrow` — string, default `''`, rendered as `.hero-suptitle`, block omitted when empty
  - `roles` — `array<int, string>`, default `[]`, raw HTML per line, block omitted when `count < 2`
  - `image` — string, default `''`, an `asset()` path; the image `<article>` is omitted when empty
  - `imageAlt` — string, default `''`
  - `full` — bool, default `false`
- Produces: `config('portfolio.hero_images')` — keys `home`, `about`, `experience`, `projects`.

- [ ] **Step 1: Write the failing feature test**

Create `tests/Feature/PageHeroTest.php`:

```php
<?php

use App\Models\Setting;

test('the home page renders exactly one h1', function () {
    $html = $this->get(route('home'))->assertOk()->getContent();

    expect(substr_count($html, '<h1'))->toBe(1);
});

test('the home hero renders through the shared component', function () {
    Setting::updateOrCreate(['key' => 'hero_suptitle'], ['value' => ['en' => 'Hello world', 'cs' => 'Ahoj']]);
    Setting::updateOrCreate(['key' => 'hero_title'], ['value' => ['en' => 'I am <span>someone</span>,', 'cs' => 'Jsem <span>někdo</span>,']]);
    Setting::updateOrCreate(['key' => 'hero_roles'], ['value' => [
        'en' => ['Chess <span>player</span>', 'Coffee <span>drinker</span>'],
        'cs' => ['<span>Šachista</span>', 'Pijan <span>kávy</span>'],
    ]]);

    $this->get(route('home'))
        ->assertSee('hero-page--full', false)
        ->assertSee('Hello world')
        ->assertSee('<h1>I am <span>someone</span>,</h1>', false)
        ->assertSee('Chess <span>player</span>', false)
        ->assertSee('id="hero-rotator"', false)
        ->assertSee('data-roles=', false);
});

test('the hero omits the rotator when there are fewer than two roles', function () {
    Setting::updateOrCreate(['key' => 'hero_roles'], ['value' => ['en' => ['Only one'], 'cs' => ['Jenom jedna']]]);

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('id="hero-rotator"', false);
});
```

- [ ] **Step 2: Run it to verify it fails**

```bash
docker exec portfolio-app-1 php artisan test --compact tests/Feature/PageHeroTest.php
```

Expected: FAIL — `hero-page--full` passes (Task 2 added it) but `assertDontSee('id="hero-rotator"')` fails, because the inline hero always renders the rotator.

- [ ] **Step 3: Create the component**

Create `resources/views/components/portfolio/page-hero.blade.php`:

```blade
@props([
    'title',
    'eyebrow' => '',
    'roles' => [],
    'image' => '',
    'imageAlt' => '',
    'full' => false,
])

{{--
    Shared opener for every public page. `--full` (home only) fills the
    viewport; everything else stops one --hero-peek short of the fold so
    the next section's watermark invites the scroll.

    The rotator keeps the `hero-rotator` id the JS binds to — one hero per
    page keeps that unique.
--}}
<section class="hero-page {{ $full ? 'hero-page--full' : '' }} portfolio-section">
    <article class="hero-page-text">
        @if ($eyebrow !== '')
            <p class="hero-suptitle">{{ $eyebrow }}</p>
        @endif
        <h1>{!! $title !!}</h1>
        @if (count($roles) > 1)
            <h4 class="underh1">
                <span id="hero-rotator" data-roles='@json($roles)' aria-live="polite">{!! $roles[0] !!}</span><span class="hero-caret" aria-hidden="true"></span>
            </h4>
        @endif
    </article>
    @if ($image !== '')
        <article class="hero-page-image">
            <img src="{{ asset($image) }}" alt="{{ $imageAlt }}">
        </article>
    @endif
</section>
```

- [ ] **Step 4: Add the hero image map to the config**

Append to the `return [ … ]` array in `config/portfolio.php`, after the `social` block:

```php
    /*
    |--------------------------------------------------------------------------
    | Hero artwork
    |--------------------------------------------------------------------------
    |
    | One image per public page hero. All four point at the same portrait
    | until page-specific artwork exists — swapping one in later is a
    | one-line change here, with no template edit.
    |
    */

    'hero_images' => [
        'home' => 'images/id-photo-portrait-businessman-suit-260nw-1505360618 1.png',
        'about' => 'images/id-photo-portrait-businessman-suit-260nw-1505360618 1.png',
        'experience' => 'images/id-photo-portrait-businessman-suit-260nw-1505360618 1.png',
        'projects' => 'images/id-photo-portrait-businessman-suit-260nw-1505360618 1.png',
    ],
```

- [ ] **Step 5: Adopt the component on the home page**

In `resources/views/welcome.blade.php`, replace lines 6–18 (the `{{-- Hero --}}` comment through the closing `</section>`) with:

```blade
    {{-- Hero --}}
    <x-portfolio.page-hero
        full
        :eyebrow="\App\Models\Setting::text('hero_suptitle', $locale)"
        :title="\App\Models\Setting::text('hero_title', $locale)"
        :roles="$heroRoles"
        :image="config('portfolio.hero_images.home')"
        image-alt=""
    />
```

The `@php $heroRoles = … @endphp` line above stays exactly as it is.

- [ ] **Step 6: Fix the one existing test that assumed a single role renders**

`tests/Feature/HomePageTest.php` — the `hero_roles markup survives to the page unescaped` test seeds a single role, which the new `count($roles) > 1` rule now omits. Give it two roles; the assertion it actually cares about (unescaped markup) is unchanged:

```php
test('hero_roles markup survives to the page unescaped', function () {
    Setting::updateOrCreate(['key' => 'hero_roles'], [
        'value' => [
            'en' => ['Full-stack <span>developer</span>', 'Chess <span>player</span>'],
            'cs' => ['Full-stack <span>vývojář</span>', '<span>Šachista</span>'],
        ],
    ]);

    $this->get(route('home'))
        ->assertSee('Full-stack <span>developer</span>', false)
        ->assertDontSee('Full-stack &lt;span&gt;developer&lt;/span&gt;', false);
});
```

- [ ] **Step 7: Run the tests**

```bash
docker exec portfolio-app-1 php artisan test --compact tests/Feature/PageHeroTest.php tests/Feature/HomePageTest.php
```

Expected: PASS.

- [ ] **Step 8: Verify the home page still looks right in a browser**

On the host: `npm run build`, then

```bash
docker exec portfolio-app-1 php artisan test --compact tests/Browser/PageHeroTest.php
```

Expected: PASS (3 tests).

- [ ] **Step 9: Pint and commit**

```bash
docker exec portfolio-app-1 vendor/bin/pint --dirty --format agent
git add resources/views/components/portfolio/page-hero.blade.php resources/views/welcome.blade.php config/portfolio.php tests/Feature/PageHeroTest.php tests/Feature/HomePageTest.php
git commit -m "feat(hero): extract the shared page-hero component"
```

---

### Task 4: Seed the nine subpage hero settings

All hero copy lives in `settings`, same as the home hero. The seeder's existing `whereNotIn(...)->delete()` already removes the four orphaned keys from the abandoned plan (`about_hero_subtitle`, `experience_hero_subtitle`, `projects_hero_subtitle`, `about_hero_meta`) once the canonical list grows — no extra code needed, but the test proves it.

**Files:**
- Modify: `database/seeders/SettingSeeder.php`
- Test: `tests/Feature/SettingTest.php` (append)

**Interfaces:**
- Consumes: `App\Models\Setting::text()` / `::list()`.
- Produces: setting keys
  `about_hero_suptitle`, `about_hero_title`, `about_hero_roles`,
  `experience_hero_suptitle`, `experience_hero_title`, `experience_hero_roles`,
  `projects_hero_suptitle`, `projects_hero_title`, `projects_hero_roles`.
  `*_suptitle` / `*_title` are `{en, cs}` strings; `*_roles` are `{en: [...], cs: [...]}` lists.

- [ ] **Step 1: Write the failing test**

Append to `tests/Feature/SettingTest.php`:

```php
test('the seeder installs the subpage hero copy and drops the orphaned keys', function () {
    Setting::updateOrCreate(['key' => 'about_hero_subtitle'], ['value' => ['en' => 'stale']]);
    Setting::updateOrCreate(['key' => 'about_hero_meta'], ['value' => ['en' => 'stale']]);

    $this->seed(\Database\Seeders\SettingSeeder::class);

    expect(Setting::text('about_hero_title', 'cs'))->toBe('Něco <span>o mně</span>,')
        ->and(Setting::text('experience_hero_suptitle', 'en'))->toBe('🗓️ Where I\'ve been')
        ->and(Setting::list('projects_hero_roles', 'cs'))->toHaveCount(4)
        ->and(Setting::whereIn('key', ['about_hero_subtitle', 'about_hero_meta'])->count())->toBe(0);
});
```

If `tests/Feature/SettingTest.php` does not already `use App\Models\Setting;`, add that import at the top.

- [ ] **Step 2: Run it to verify it fails**

```bash
docker exec portfolio-app-1 php artisan test --compact tests/Feature/SettingTest.php
```

Expected: FAIL — `about_hero_title` resolves to `''`.

- [ ] **Step 3: Add the nine keys to the seeder**

In `database/seeders/SettingSeeder.php`, inside the `$settings` array, after the `about_title` line:

```php
            'about_hero_suptitle' => ['en' => '👤 whoami', 'cs' => '👤 whoami'],
            'about_hero_title' => ['en' => 'A bit <span>about me</span>,', 'cs' => 'Něco <span>o mně</span>,'],
            'about_hero_roles' => [
                'en' => ['Student <span>by day</span>', 'Freelancer <span>by night</span>', '<span>Chess</span> player', 'Arch Linux <span>enjoyer</span>', 'Coffee → <span>code</span>'],
                'cs' => ['Ve dne <span>student</span>', 'V noci <span>freelancer</span>', '<span>Šachista</span>', 'Arch Linux <span>nadšenec</span>', 'Káva → <span>kód</span>'],
            ],
            'experience_hero_suptitle' => ['en' => '🗓️ Where I’ve been', 'cs' => '🗓️ Kudy jsem prošel'],
            'experience_hero_title' => ['en' => 'My <span>journey</span>,', 'cs' => 'Moje <span>cesta</span>,'],
            'experience_hero_roles' => [
                'en' => ['Certificates & <span>competitions</span>', 'Work that <span>shipped</span>', 'Life <span>outside code</span>', 'From <span>2021</span> to now'],
                'cs' => ['Certifikáty a <span>soutěže</span>', 'Práce, co <span>vyšla</span>', 'Život <span>mimo kód</span>', 'Od <span>2021</span> dodnes'],
            ],
            'projects_hero_suptitle' => ['en' => '🛠️ What I’ve built', 'cs' => '🛠️ Co jsem postavil'],
            'projects_hero_title' => ['en' => 'Things I’ve <span>shipped</span>,', 'cs' => 'Věci, co jsem <span>postavil</span>,'],
            'projects_hero_roles' => [
                'en' => ['Laravel <span>monoliths</span>', 'Spring Boot <span>APIs</span>', 'Side projects that <span>survived</span>', 'Deployed with <span>Docker</span>'],
                'cs' => ['Laravel <span>monolity</span>', 'Spring Boot <span>API</span>', 'Vedlejšáky, co <span>přežily</span>', 'Nasazeno přes <span>Docker</span>'],
            ],
```

Note the apostrophes: the existing seeder uses the typographic `’` (as in `I’m`), so `Where I’ve been`, `What I’ve built` and `Things I’ve shipped,` use `’` too. The test in Step 1 must match — if it was written with a straight `'`, fix the **test** to use `’`, not the seeder.

- [ ] **Step 4: Run the test to verify it passes**

```bash
docker exec portfolio-app-1 php artisan test --compact tests/Feature/SettingTest.php tests/Feature/DatabaseSeederTest.php
```

Expected: PASS.

- [ ] **Step 5: Seed the dev database**

```bash
docker exec portfolio-app-1 php artisan db:seed --class=SettingSeeder --no-interaction
```

- [ ] **Step 6: Pint and commit**

```bash
docker exec portfolio-app-1 vendor/bin/pint --dirty --format agent
git add database/seeders/SettingSeeder.php tests/Feature/SettingTest.php
git commit -m "feat(settings): seed the subpage hero copy in both locales"
```

---

### Task 5: Generalise the site-content admin page

Fifteen ungrouped inputs per locale tab would be unusable, so fields are grouped under per-page headings, and the single `$roles` property becomes `$roleLists` keyed by setting key.

**Files:**
- Modify: `resources/views/pages/manage/⚡site-content.blade.php`
- Test: `tests/Feature/SiteContentManagementTest.php:75-92` (rewrite the site-content test)

**Interfaces:**
- Consumes: the nine setting keys from Task 4.
- Produces: Livewire component `pages::manage.site-content` with public properties
  - `array $texts` — `key => ['en' => string, 'cs' => string]` (unchanged shape, more keys)
  - `array $roleLists` — `key => ['en' => string, 'cs' => string]`, newline-separated (**replaces** `array $roles`)
  - `array $textKeys` — flat list of plain-text keys
  - `array $roleListKeys` — `['hero_roles', 'about_hero_roles', 'experience_hero_roles', 'projects_hero_roles']`
  - `array $groups` — `heading => ordered field keys`, drawn from both lists
  - `save()`, `label(string $key): string`, private `splitLines(string $value): array`

- [ ] **Step 1: Write the failing test**

Replace the `site content editor persists settings and rotating roles` test in `tests/Feature/SiteContentManagementTest.php` with:

```php
test('site content editor persists settings and every rotating role list', function () {
    Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.site-content')
        ->set('texts.hero_suptitle', ['en' => 'Hello', 'cs' => 'Ahoj'])
        ->set('texts.hero_title', ['en' => 'I am X', 'cs' => 'Jsem X'])
        ->set('texts.stats_title', ['en' => 'Stats', 'cs' => 'Statistiky'])
        ->set('texts.tools_title', ['en' => 'Tools', 'cs' => 'Nástroje'])
        ->set('texts.reviews_title', ['en' => 'Reviews', 'cs' => 'Reference'])
        ->set('texts.about_title', ['en' => 'About', 'cs' => 'O mně'])
        ->set('texts.about_hero_suptitle', ['en' => 'whoami', 'cs' => 'whoami'])
        ->set('texts.about_hero_title', ['en' => 'About me', 'cs' => 'O mně'])
        ->set('texts.experience_hero_suptitle', ['en' => 'Where', 'cs' => 'Kudy'])
        ->set('texts.experience_hero_title', ['en' => 'My journey', 'cs' => 'Moje cesta'])
        ->set('texts.projects_hero_suptitle', ['en' => 'Built', 'cs' => 'Postaveno'])
        ->set('texts.projects_hero_title', ['en' => 'Shipped', 'cs' => 'Vydáno'])
        ->set('roleLists.hero_roles', ['en' => "Developer\nChess player", 'cs' => "Vývojář\nŠachista"])
        ->set('roleLists.about_hero_roles', ['en' => "Student\nFreelancer", 'cs' => "Student\nFreelancer"])
        ->set('roleLists.experience_hero_roles', ['en' => "Certificates\nWork", 'cs' => "Certifikáty\nPráce"])
        ->set('roleLists.projects_hero_roles', ['en' => "Laravel\nSpring Boot", 'cs' => "Laravel\nSpring Boot"])
        ->call('save')
        ->assertHasNoErrors();

    expect(Setting::text('hero_suptitle', 'cs'))->toBe('Ahoj')
        ->and(Setting::text('projects_hero_title', 'cs'))->toBe('Vydáno')
        ->and(Setting::list('hero_roles', 'en'))->toBe(['Developer', 'Chess player'])
        ->and(Setting::list('about_hero_roles', 'cs'))->toBe(['Student', 'Freelancer'])
        ->and(Setting::list('experience_hero_roles', 'en'))->toBe(['Certificates', 'Work'])
        ->and(Setting::list('projects_hero_roles', 'cs'))->toBe(['Laravel', 'Spring Boot']);
});

test('the czech hero copy falls back to english when left blank', function () {
    Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.site-content')
        ->set('texts.about_hero_title', ['en' => 'About me', 'cs' => ''])
        ->set('roleLists.about_hero_roles', ['en' => "Student\nFreelancer", 'cs' => ''])
        ->call('save')
        ->assertHasNoErrors();

    expect(Setting::text('about_hero_title', 'cs'))->toBe('About me')
        ->and(Setting::list('about_hero_roles', 'cs'))->toBe(['Student', 'Freelancer']);
});

test('every english hero field is required', function () {
    Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.site-content')
        ->set('texts.about_hero_title', ['en' => '', 'cs' => ''])
        ->set('roleLists.projects_hero_roles', ['en' => '', 'cs' => ''])
        ->call('save')
        ->assertHasErrors(['texts.about_hero_title.en', 'roleLists.projects_hero_roles.en']);
});
```

The second test relies on `mount()` pre-filling every other field from the seeded settings. `SiteContentManagementTest` runs against a seeded database (the suite seeds via `DatabaseSeeder`); if it does not, add `$this->seed(\Database\Seeders\SettingSeeder::class);` as the first line of that test.

- [ ] **Step 2: Run it to verify it fails**

```bash
docker exec portfolio-app-1 php artisan test --compact tests/Feature/SiteContentManagementTest.php
```

Expected: FAIL — `Unable to set component data. Public property [roleLists] not found`.

- [ ] **Step 3: Rewrite the component class**

Replace the PHP block at the top of `resources/views/pages/manage/⚡site-content.blade.php` (everything from `<?php` through `}; ?>`) with:

```php
<?php

use App\Models\Setting;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Manage Site content')] class extends Component {
    /** Plain translatable text settings: key => ['en'=>.., 'cs'=>..]. */
    public array $texts = [];

    /** Rotator role lists: key => ['en'=>.., 'cs'=>..], newline-separated. */
    public array $roleLists = [];

    /** @var array<int, string> Keys edited as simple {en,cs} text fields. */
    public array $textKeys = [
        'hero_suptitle',
        'hero_title',
        'about_hero_suptitle',
        'about_hero_title',
        'experience_hero_suptitle',
        'experience_hero_title',
        'projects_hero_suptitle',
        'projects_hero_title',
        'stats_title',
        'tools_title',
        'reviews_title',
        'about_title',
    ];

    /** @var array<int, string> Keys edited as a newline-separated textarea. */
    public array $roleListKeys = [
        'hero_roles',
        'about_hero_roles',
        'experience_hero_roles',
        'projects_hero_roles',
    ];

    /**
     * Rendering order: heading => field keys, drawn from both lists above.
     * Fifteen ungrouped inputs per locale tab would be unusable.
     *
     * @var array<string, array<int, string>>
     */
    public array $groups = [
        'Home hero' => ['hero_suptitle', 'hero_title', 'hero_roles'],
        'About me hero' => ['about_hero_suptitle', 'about_hero_title', 'about_hero_roles'],
        'Experience hero' => ['experience_hero_suptitle', 'experience_hero_title', 'experience_hero_roles'],
        'Projects hero' => ['projects_hero_suptitle', 'projects_hero_title', 'projects_hero_roles'],
        'Section titles' => ['stats_title', 'tools_title', 'reviews_title', 'about_title'],
    ];

    public function mount(): void
    {
        foreach ($this->textKeys as $key) {
            $this->texts[$key] = [
                'en' => Setting::text($key, 'en'),
                'cs' => Setting::text($key, 'cs'),
            ];
        }

        foreach ($this->roleListKeys as $key) {
            $this->roleLists[$key] = [
                'en' => implode("\n", Setting::list($key, 'en')),
                'cs' => implode("\n", Setting::list($key, 'cs')),
            ];
        }
    }

    public function save(): void
    {
        $rules = [];

        foreach ($this->textKeys as $key) {
            $rules["texts.{$key}.en"] = ['required', 'string', 'max:2000'];
            $rules["texts.{$key}.cs"] = ['nullable', 'string', 'max:2000'];
        }

        foreach ($this->roleListKeys as $key) {
            $rules["roleLists.{$key}.en"] = ['required', 'string'];
            $rules["roleLists.{$key}.cs"] = ['nullable', 'string'];
        }

        $this->validate($rules);

        foreach ($this->textKeys as $key) {
            Setting::updateOrCreate(['key' => $key], ['value' => [
                'en' => $this->texts[$key]['en'],
                'cs' => $this->texts[$key]['cs'] ?: $this->texts[$key]['en'],
            ]]);
        }

        foreach ($this->roleListKeys as $key) {
            Setting::updateOrCreate(['key' => $key], ['value' => [
                'en' => $this->splitLines($this->roleLists[$key]['en']),
                'cs' => $this->splitLines($this->roleLists[$key]['cs'] ?: $this->roleLists[$key]['en']),
            ]]);
        }

        Flux::toast(text: 'Site content saved.', variant: 'success');
    }

    /** @return array<int, string> */
    private function splitLines(string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $value))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    /** True when a key is edited as a newline-separated rotator list. */
    public function isRoleList(string $key): bool
    {
        return in_array($key, $this->roleListKeys, true);
    }

    /** Human label for a setting key, without the page prefix the group heading already carries. */
    public function label(string $key): string
    {
        $trimmed = preg_replace('/^(about|experience|projects)_hero_/', '', $key);

        return ucfirst(str_replace('_', ' ', $trimmed));
    }
}; ?>
```

- [ ] **Step 4: Rewrite the form markup**

Replace the `<form …>…</form>` element in the same file with:

```blade
    <form wire:submit="save" class="space-y-6 max-w-3xl">
        <x-manage.locale-tabs>
            <x-slot:en>
                @foreach ($groups as $heading => $keys)
                    <flux:heading size="lg" class="pt-2">{{ $heading }}</flux:heading>
                    @foreach ($keys as $key)
                        <flux:field>
                            <flux:label>{{ $this->label($key) }} <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
                            @if ($this->isRoleList($key))
                                <flux:textarea wire:model="roleLists.{{ $key }}.en" rows="5" placeholder="One role per line" />
                                <flux:description>One rotating role per line.</flux:description>
                                <flux:error name="roleLists.{{ $key }}.en" />
                            @else
                                <flux:input wire:model="texts.{{ $key }}.en" />
                                <flux:error name="texts.{{ $key }}.en" />
                            @endif
                        </flux:field>
                    @endforeach
                @endforeach
            </x-slot:en>
            <x-slot:cs>
                @foreach ($groups as $heading => $keys)
                    <flux:heading size="lg" class="pt-2">{{ $heading }}</flux:heading>
                    @foreach ($keys as $key)
                        <flux:field>
                            <flux:label>{{ $this->label($key) }}</flux:label>
                            @if ($this->isRoleList($key))
                                <flux:textarea wire:model="roleLists.{{ $key }}.cs" rows="5" placeholder="Jedna role na řádek" />
                                <flux:description>Jedna rotující role na řádek.</flux:description>
                                <flux:error name="roleLists.{{ $key }}.cs" />
                            @else
                                <flux:input wire:model="texts.{{ $key }}.cs" />
                                <flux:error name="texts.{{ $key }}.cs" />
                            @endif
                        </flux:field>
                    @endforeach
                @endforeach
            </x-slot:cs>
        </x-manage.locale-tabs>

        <div class="flex justify-end">
            <flux:button type="submit" variant="primary" class="btn-gold">Save</flux:button>
        </div>
    </form>
```

Also update the page header subtitle on the line above the form:

```blade
    <x-manage.page-header title="Site content" subtitle="Page hero copy, rotating roles and section titles" />
```

- [ ] **Step 5: Run the tests to verify they pass**

```bash
docker exec portfolio-app-1 php artisan test --compact tests/Feature/SiteContentManagementTest.php
```

Expected: PASS.

- [ ] **Step 6: Pint and commit**

```bash
docker exec portfolio-app-1 vendor/bin/pint --dirty --format agent
git add "resources/views/pages/manage/⚡site-content.blade.php" tests/Feature/SiteContentManagementTest.php
git commit -m "feat(admin): group site content by page and edit every hero role list"
```

---

### Task 6: Give the three subpages their heroes

Each subpage gains the shared hero and loses the inline `padding-top: var(--sp-section)` on its first content section — that padding would otherwise eat the whole peek, hiding the watermark it is meant to reveal.

**Files:**
- Modify: `resources/views/about-me.blade.php:5-6`
- Modify: `resources/views/experience.blade.php:5`
- Modify: `resources/views/projects.blade.php:4`
- Test: `tests/Feature/PageHeroTest.php` (append)

**Interfaces:**
- Consumes: `<x-portfolio.page-hero />` (Task 3), the nine setting keys (Task 4), `config('portfolio.hero_images.*')` (Task 3).
- Produces: one `<h1>` and one `#hero-rotator` per public page.

- [ ] **Step 1: Write the failing tests**

Append to `tests/Feature/PageHeroTest.php`:

```php
test('every public subpage renders exactly one h1', function (string $route) {
    $html = $this->get(route($route))->assertOk()->getContent();

    expect(substr_count($html, '<h1'))->toBe(1)
        ->and(substr_count($html, 'id="hero-rotator"'))->toBe(1);
})->with(['about-me', 'experience', 'projects']);

test('the about me hero renders its settings copy', function () {
    Setting::updateOrCreate(['key' => 'about_hero_suptitle'], ['value' => ['en' => '👤 whoami', 'cs' => '👤 whoami']]);
    Setting::updateOrCreate(['key' => 'about_hero_title'], ['value' => ['en' => 'A bit <span>about me</span>,', 'cs' => 'Něco <span>o mně</span>,']]);
    Setting::updateOrCreate(['key' => 'about_hero_roles'], ['value' => [
        'en' => ['Student <span>by day</span>', 'Freelancer <span>by night</span>'],
        'cs' => ['Ve dne <span>student</span>', 'V noci <span>freelancer</span>'],
    ]]);

    $this->get(route('about-me'))
        ->assertSee('👤 whoami')
        ->assertSee('<h1>A bit <span>about me</span>,</h1>', false)
        ->assertSee('Student <span>by day</span>', false)
        ->assertSee('Freelancer <span>by night</span>', false)
        ->assertDontSee('hero-page--full', false);
});

test('the subpage heroes render the czech copy under the cs locale', function () {
    Setting::updateOrCreate(['key' => 'experience_hero_title'], ['value' => ['en' => 'My <span>journey</span>,', 'cs' => 'Moje <span>cesta</span>,']]);
    Setting::updateOrCreate(['key' => 'projects_hero_title'], ['value' => ['en' => 'Things I’ve <span>shipped</span>,', 'cs' => 'Věci, co jsem <span>postavil</span>,']]);

    $this->withSession(['locale' => 'cs'])
        ->get(route('experience'))
        ->assertSee('Moje <span>cesta</span>,', false)
        ->assertDontSee('My <span>journey</span>,', false);

    $this->withSession(['locale' => 'cs'])
        ->get(route('projects'))
        ->assertSee('Věci, co jsem <span>postavil</span>,', false);
});

test('the subpage heroes carry the rotator role list in data-roles', function () {
    Setting::updateOrCreate(['key' => 'projects_hero_roles'], ['value' => [
        'en' => ['Laravel <span>monoliths</span>', 'Spring Boot <span>APIs</span>'],
        'cs' => ['Laravel <span>monolity</span>', 'Spring Boot <span>API</span>'],
    ]]);

    $this->get(route('projects'))
        ->assertSee('data-roles=', false)
        ->assertSee('Spring Boot &lt;span&gt;APIs&lt;\/span&gt;', false);
});
```

The last assertion matches `@json()` output inside a single-quoted HTML attribute: Blade's `@json` escapes `<` to `&lt;` and `/` to `\/`. If the exact escaping differs, dump the attribute once with `dd($this->get(route('projects'))->getContent())` and match what is actually emitted — do **not** relax the assertion to a bare `data-roles`.

- [ ] **Step 2: Run them to verify they fail**

```bash
docker exec portfolio-app-1 php artisan test --compact tests/Feature/PageHeroTest.php
```

Expected: FAIL — the subpages have no `<h1>` (`substr_count` returns 0).

- [ ] **Step 3: Add the hero to About me**

In `resources/views/about-me.blade.php`, replace lines 3–6 (the `@php` line through the opening `<section …>` tag) with:

```blade
    @php $locale = app()->getLocale(); @endphp

    {{-- Hero --}}
    <x-portfolio.page-hero
        :eyebrow="\App\Models\Setting::text('about_hero_suptitle', $locale)"
        :title="\App\Models\Setting::text('about_hero_title', $locale)"
        :roles="\App\Models\Setting::list('about_hero_roles', $locale)"
        :image="config('portfolio.hero_images.about')"
        image-alt=""
    />

    {{-- About Me --}}
    <section id="about-me" class="portfolio-section">
```

(The inline `style="padding-top: var(--sp-section)"` is gone.)

- [ ] **Step 4: Add the hero to Experience**

In `resources/views/experience.blade.php`, replace lines 3–5 with:

```blade
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
```

- [ ] **Step 5: Add the hero to Projects**

In `resources/views/projects.blade.php`, replace lines 3–4 with:

```blade
    @php $locale = app()->getLocale(); @endphp

    {{-- Hero --}}
    <x-portfolio.page-hero
        :eyebrow="\App\Models\Setting::text('projects_hero_suptitle', $locale)"
        :title="\App\Models\Setting::text('projects_hero_title', $locale)"
        :roles="\App\Models\Setting::list('projects_hero_roles', $locale)"
        :image="config('portfolio.hero_images.projects')"
        image-alt=""
    />

    <section id="projects" class="portfolio-section">
```

Note: the Projects page renders each year group's label as `<h2 class="projects-year-label">`, so the hero `<h1>` sits above a run of `h2`s — the hierarchy stays clean.

- [ ] **Step 6: Run the feature tests**

```bash
docker exec portfolio-app-1 php artisan test --compact tests/Feature/PageHeroTest.php tests/Feature/AboutMePageTest.php tests/Feature/ExperiencePageTest.php
```

Expected: PASS.

The dataset test needs at least two seeded roles per page for `#hero-rotator` to render; the suite seeds `SettingSeeder`, which supplies four or five per page. If the subpage tests report zero rotators, seed explicitly in the test with `$this->seed(\Database\Seeders\SettingSeeder::class);`.

- [ ] **Step 7: Commit**

```bash
git add resources/views/about-me.blade.php resources/views/experience.blade.php resources/views/projects.blade.php tests/Feature/PageHeroTest.php
git commit -m "feat(pages): open every public subpage with the shared hero"
```

---

### Task 7: Verify the peek geometry in a real browser and run the full suite

**Files:**
- Modify: `tests/Browser/PageHeroTest.php` (append)

**Interfaces:**
- Consumes: everything from Tasks 2–6.
- Produces: nothing new.

- [ ] **Step 1: Append the peek tests**

Add to `tests/Browser/PageHeroTest.php` (the `$heroPeekJs` variable defined at the top of the file is reused — pass it in with `use ($heroPeekJs)`):

```php
$nextSectionVisibleJs = <<<'JS'
    (() => {
        const hero = document.querySelector('.hero-page');
        const next = hero.nextElementSibling;
        return next.getBoundingClientRect().top < window.innerHeight;
    })()
JS;

test('a subpage hero stops short of the fold on desktop', function (string $path) use ($heroPeekJs, $nextSectionVisibleJs) {
    $page = visit($path)->resize(1440, 900);

    expect($page->script($heroPeekJs))->toBeGreaterThan(0)
        ->and($page->script($heroPeekJs))->toBeLessThan(200)
        ->and($page->script($nextSectionVisibleJs))->toBeTrue();
})->with(['/about-me', '/experience', '/projects']);

test('a subpage hero stops short of the fold on mobile', function (string $path) use ($heroPeekJs, $nextSectionVisibleJs) {
    $page = visit($path)->resize(390, 844);

    expect($page->script($heroPeekJs))->toBeGreaterThan(0)
        ->and($page->script($heroPeekJs))->toBeLessThan(200)
        ->and($page->script($nextSectionVisibleJs))->toBeTrue();
})->with(['/about-me', '/experience', '/projects']);

test('a subpage hero rotator cycles its roles', function () {
    $page = visit('/about-me')->resize(1440, 900);

    $read = "document.getElementById('hero-rotator').textContent";
    $first = $page->script($read);
    $page->wait(3);

    expect($page->script($read))->not->toBe($first);
});
```

- [ ] **Step 2: Build assets and run the browser suite**

On the host:

```bash
npm run build
```

Then:

```bash
docker exec portfolio-app-1 php artisan test --compact tests/Browser/PageHeroTest.php tests/Browser/PublicPagesTest.php
```

Expected: PASS.

If a peek measures **negative** on mobile, the stacked hero content is taller than `100svh - var(--hero-peek)`; the fix is in `page-hero.css` — shrink `.hero-page-image img` inside the `max-width: 992px` block (e.g. `width: min(70vw, 260px)`), not to loosen the assertion. If the peek measures far **above** 200px, an ancestor is adding top offset above the hero; check `.portfolio-main` and the first-child margin.

- [ ] **Step 3: Run the whole suite**

```bash
docker exec portfolio-app-1 php artisan test --compact
```

Expected: PASS, including `HomePageTest`, `AboutMePageTest`, `ExperiencePageTest`, `SiteContentManagementTest`, `DatabaseSeederTest`, `SettingTest` and `PublicPagesTest`.

- [ ] **Step 4: Mark the spec implemented**

In `docs/superpowers/specs/2026-08-12-page-heroes-design.md`, change the status line to:

```markdown
**Status:** implemented — see `docs/superpowers/plans/2026-08-12-page-heroes.md`
```

- [ ] **Step 5: Commit**

```bash
git add tests/Browser/PageHeroTest.php docs/superpowers/specs/2026-08-12-page-heroes-design.md
git commit -m "test(hero): verify the subpage peek geometry at desktop and mobile widths"
```

---

## Notes on deliberate deviations from the spec

1. **`margin-bottom: 0` on the non-full hero.** The spec gives only `min-height`. But `.portfolio-section` carries `margin-bottom: var(--sp-section)` (6.25rem ≈ 100px), which is larger than the peek — the peek would have shown 100px of empty margin and no watermark. The `--full` variant keeps the section gap.
2. **The `≤992px` media query is rewritten, not moved verbatim.** Today it sets `height: auto; margin-top: 10vh` on `.hero-page`; both defeat the peek on mobile (the offset pushes the next section off-screen). Replaced with `justify-content: center; gap: 2rem`.
3. **The existing `hero_roles markup survives to the page unescaped` test is edited** (Task 3, Step 6) because the spec's "omit the rotator below two roles" rule makes its single-role fixture render nothing.
4. **The seeder needs no explicit orphan deletion.** `SettingSeeder` already ends with `Setting::whereNotIn('key', array_keys($settings))->delete()`, which removes `about_hero_subtitle`, `experience_hero_subtitle`, `projects_hero_subtitle` and `about_hero_meta` the moment it runs. Task 4's test proves it.
