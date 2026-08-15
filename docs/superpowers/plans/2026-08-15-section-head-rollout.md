# Section head rollout — finishing touches — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Finish the section-head rollout: ghost every home section, reposition the hero's ghost wordmark to sit above the title (not behind it), give the footer wordmark a solid big-font treatment instead of the outlined watermark, and bring `x-portfolio.section-head` to `about-me`, `experience`, `projects`.

**Architecture:** No new components. `x-portfolio.section-head` (already built) gets three more consumers. `dock-hero.css` and `app.css` get targeted, surgical rule changes — no restructuring. All new copy is static lang-file strings; every `Setting`-backed title (`about_title`, `stats_title`) keeps reading from `Setting::text()` exactly as today so the admin `⚡site-content` page keeps working.

**Tech Stack:** Laravel 13, Blade, Tailwind v4 + hand-written CSS (`--c-*` tokens), Pest 4 (feature + browser), Vite. Every Artisan/PHP command runs via `docker exec portfolio-app-1 …`; `npm run build` runs on the **host**.

**Spec:** `docs/superpowers/specs/2026-08-15-section-head-rollout-design.md`

## Global Constraints

- **No new design tokens, no new colours.** Only `--c-primary`, `--c-primary-fade`, `--c-muted`, `--c-fg`, `--c-watermark`, `--font-display`, `--fw-*`, `--fs-h2`, `--sp-section`, `--t-fast`, `--border-w`, `--sidebar-w`, `--content-gap`.
- **`Setting::text('about_title', …)` and `Setting::text('stats_title', …)` keep driving the `title` prop on about-me's two sechead instances** — do not switch them to static lang strings. The `⚡site-content` "Section titles" admin fields must keep having a visible effect.
- **`.projects-year-label` is out of scope.** It keeps depending on the global `.portfolio-page h2` watermark rule (`app.css:348`) — do not touch it, do not delete that global rule.
- **Every command runs in Docker:** `docker exec portfolio-app-1 …`. `npm run build` runs on the **host**.
- **Pint before every commit that touches PHP:** `docker exec portfolio-app-1 vendor/bin/pint --dirty --format agent`.
- **CSS `rem` values for the hero ghost reposition (Task 3) are starting points, not final.** Verify visually (host `npm run build`, then http://localhost:8008 at 1440/1100/760/360, both themes) and adjust before committing if the ghost overlaps text or the gap looks wrong.

## File Structure

| File | Responsibility |
|---|---|
| `resources/views/welcome.blade.php` | **Modify.** Add `ghost` prop to the Work & Life and Tools sections. |
| `tests/Feature/SectionHeadTest.php` | **Modify.** Fix the now-wrong "no ghost" test; add coverage for about-me/experience/projects heads. |
| `resources/css/app.css` | **Modify.** `.portfolio-footer-watermark` gets its own solid-fill rule, no longer inheriting `.portfolio-page h2`. |
| `resources/css/components/dock-hero.css` | **Modify.** `.dock-hero-ghost` descender fix + reposition; `.dock-hero-copy` padding-top. |
| `resources/views/about-me.blade.php` | **Modify.** Both `<h2>`s become `<x-portfolio.section-head>`. |
| `resources/views/experience.blade.php` | **Modify.** The one `<h2>` becomes `<x-portfolio.section-head>`. |
| `resources/views/projects.blade.php` | **Modify.** New `<x-portfolio.section-head>` added before the year loop. |
| `resources/css/pages/{about-me,experience,projects}.css` | **Modify.** Add `@import '../components/section-head.css';`. |
| `resources/lang/{en,cs}/pages/about-me.php` | **Modify.** Add `head_eyebrow`, `stats_head_eyebrow`. |
| `resources/lang/{en,cs}/pages/experience.php` | **Modify.** Add `head_eyebrow`. |
| `resources/lang/{en,cs}/pages/projects.php` | **Modify.** Add `head_eyebrow`. |
| `tests/Browser/SectionHeadTest.php` | **Modify.** Extend the no-sideways-scroll check to about-me/experience/projects. |

---

### Task 1: Home ghosts on Work & Life and Tools

**Files:**
- Modify: `resources/views/welcome.blade.php:36-41` (Work & Life), `:81-86` (Tools)
- Modify: `tests/Feature/SectionHeadTest.php`

**Interfaces:**
- Consumes: `<x-portfolio.section-head :ghost :eyebrow :title :note />` (existing component, unchanged).
- Produces: nothing new — reuses `home/experience.title` ("Experience" / "Zkušenosti") and `home/tools.title` ("Tools" / "Nástroje"), both already present in the lang files.

- [ ] **Step 1: Update the now-wrong test first**

The existing test `the work and tools heads render without a ghost wordmark` in `tests/Feature/SectionHeadTest.php` asserts the opposite of what we're about to build. Replace it:

```php
test('the work and tools heads render without a ghost wordmark', function () {
```

becomes

```php
test('the work and tools heads render with a ghost wordmark', function () {
    $response = $this->get(route('home'));

    $response
        ->assertSee('Track record')
        ->assertSee("Where I've <em>been</em> since 2021", false)
        ->assertSee('<a href="'.route('experience').'">Experience page</a>', false)
        ->assertSee('Daily drivers')
        ->assertSee('What I actually <em>open</em> every day', false)
        ->assertSee('<div class="sechead-ghost" aria-hidden="true">Experience</div>', false)
        ->assertSee('<div class="sechead-ghost" aria-hidden="true">Tools</div>', false);
});
```

- [ ] **Step 2: Run it to verify it fails**

```bash
docker exec portfolio-app-1 php artisan test --compact --filter="work and tools heads render with a ghost wordmark" tests/Feature/SectionHeadTest.php
```

Expected: FAIL — neither ghost div exists yet.

- [ ] **Step 3: Add the ghosts**

In `resources/views/welcome.blade.php`, Work & Life section:

```blade
    <section class="work portfolio-section">
        <x-portfolio.section-head
            :eyebrow="__('home/experience.head_eyebrow')"
            :title="__('home/experience.head_title')"
            :note="__('home/experience.head_note', ['url' => route('experience')])"
        />
```

becomes

```blade
    <section class="work portfolio-section">
        <x-portfolio.section-head
            :ghost="__('home/experience.title')"
            :eyebrow="__('home/experience.head_eyebrow')"
            :title="__('home/experience.head_title')"
            :note="__('home/experience.head_note', ['url' => route('experience')])"
        />
```

Tools section:

```blade
    <section id="tools" class="portfolio-section">
        <x-portfolio.section-head
            :eyebrow="__('home/tools.head_eyebrow')"
            :title="__('home/tools.head_title')"
            :note="__('home/tools.head_note')"
        />
```

becomes

```blade
    <section id="tools" class="portfolio-section">
        <x-portfolio.section-head
            :ghost="__('home/tools.title')"
            :eyebrow="__('home/tools.head_eyebrow')"
            :title="__('home/tools.head_title')"
            :note="__('home/tools.head_note')"
        />
```

- [ ] **Step 4: Run the test to verify it passes**

```bash
docker exec portfolio-app-1 php artisan test --compact tests/Feature/SectionHeadTest.php
```

Expected: PASS, all tests including the rewritten one.

- [ ] **Step 5: Commit**

```bash
docker exec portfolio-app-1 vendor/bin/pint --dirty --format agent
git add resources/views/welcome.blade.php tests/Feature/SectionHeadTest.php
git commit -m "feat(section-head): ghost the Work & Life and Tools sections on home"
```

---

### Task 2: Footer wordmark — solid big font, not the outlined watermark

**Files:**
- Modify: `resources/css/app.css` (`.portfolio-footer-watermark`, currently `:532-537`)
- Test: `tests/Feature/SectionHeadTest.php`

**Interfaces:**
- Consumes: nothing.
- Produces: nothing later tasks depend on.

- [ ] **Step 1: Write the failing test**

CSS rules aren't visible in rendered HTML, so this asserts against the source file directly rather than a `get()` response — simpler than depending on Vite's build output. Append to `tests/Feature/SectionHeadTest.php`:

```php
test('the footer wordmark uses a solid fill, not the outlined watermark', function () {
    $css = file_get_contents(resource_path('css/app.css'));

    preg_match('/\.portfolio-footer-watermark\s*\{([^}]*)\}/s', $css, $match);

    expect($match)->not->toBeEmpty()
        ->and($match[1])->toContain('color: var(--c-fg)')
        ->and($match[1])->not->toContain('-webkit-text-stroke')
        ->and($match[1])->not->toContain('color-mix');
});
```

- [ ] **Step 2: Run it to verify it fails**

```bash
docker exec portfolio-app-1 php artisan test --compact --filter="footer wordmark uses a solid fill" tests/Feature/SectionHeadTest.php
```

Expected: FAIL — the current rule has no `color: var(--c-fg)` declaration (it inherits color from the global `.portfolio-page h2` rule instead).

- [ ] **Step 3: Rewrite the rule**

In `resources/css/app.css`, replace:

```css
.portfolio-footer-watermark {
    /* generic .portfolio-page h2 already gives the outlined watermark look,
       centring, display font and the -0.45em overlap that pulls the footer up
       over the letters. Just drop it under the footer in the stack. */
    z-index: 0;
}
```

with:

```css
.portfolio-footer-watermark {
    /* No longer inherits .portfolio-page h2's outline treatment — own rule,
       solid fill. The -0.45em overlap is unrelated to outline-vs-solid: it's
       what pulls the footer band up over the letters' lower half, same as
       every section watermark, and stays regardless of fill style. */
    z-index: 0;
    text-align: center;
    margin-bottom: -0.45em;
    font-size: var(--fs-h2);
    font-family: var(--font-display);
    font-weight: var(--fw-bold);
    color: var(--c-fg);
    overflow-wrap: anywhere;
}
```

- [ ] **Step 4: Run the test to verify it passes**

```bash
docker exec portfolio-app-1 php artisan test --compact tests/Feature/SectionHeadTest.php
```

Expected: PASS.

- [ ] **Step 5: Build and eyeball**

On the **host**:

```bash
npm run build
```

Open http://localhost:8008, scroll to the footer. "projektant-pata" should now read as solid, filled text in `--c-fg`, not an outlined/hollow watermark — same size and centring as before, still overlapped by the footer band below it. Check both `≤992px` (full-bleed band) and `≥993px` (card footer) widths — both still have their own `.portfolio-footer-watermark` `margin-left` override at `app.css:669` and should still line up with the footer card.

- [ ] **Step 6: Commit**

```bash
git add resources/css/app.css tests/Feature/SectionHeadTest.php
git commit -m "fix(footer): give the wordmark its own solid-fill rule, drop the outline"
```

---

### Task 3: Hero ghost — reposition above the title, fix the descender clip

**Files:**
- Modify: `resources/css/components/dock-hero.css` (`.dock-hero-ghost:119-133`, `.dock-hero-copy:100-114`)
- Modify: `tests/Browser/DockHeroTest.php` is NOT touched — its assertions are about the outer band, unaffected by this change, but it must still pass (see Step 5).

**Interfaces:**
- Consumes: nothing.
- Produces: nothing later tasks depend on. `about-me`, `experience`, `projects`, `home` all share this one file — the change applies to all four automatically.

**Context:** Only the *desktop* (base, no media query) rule overlaps the ghost behind the copy — `top: -0.5rem` against `padding-top: 3.4rem` isn't enough clearance for a 132px ghost. The `≤1200px` stacked layout and `≤560px` rules already give the ghost its own line (their `padding-top` already clears the ghost's height by design — see the existing comment at `dock-hero.css:341-344`) and do **not** need their `top`/`padding-top` values touched. The `line-height`/`padding-bottom` descender fix, however, lives in the base rule and applies everywhere.

- [ ] **Step 1: Write the failing browser test**

Append to `tests/Browser/DockHeroTest.php`:

```php
test('the hero ghost sits above the title as its own line, not behind it', function (string $path) {
    $this->seed(\Database\Seeders\SettingSeeder::class);

    $page = visit($path)->resize(1440, 900);

    $noOverlapJs = <<<'JS'
        (() => {
            const ghost = document.querySelector('.dock-hero-ghost');
            const eyebrow = document.querySelector('.dock-hero-eyebrow') || document.querySelector('.dock-hero-title');
            if (!ghost || !eyebrow) { return true; }
            const g = ghost.getBoundingClientRect();
            const e = eyebrow.getBoundingClientRect();
            return g.bottom <= e.top;
        })()
    JS;

    expect($page->script($noOverlapJs))->toBeTrue();
})->with(['/', '/about-me', '/projects', '/experience']);
```

- [ ] **Step 2: Run it to verify it fails**

```bash
docker exec portfolio-app-1 php artisan test --compact --filter="hero ghost sits above the title" tests/Browser/DockHeroTest.php
```

Expected: FAIL at 1440px — the ghost's bounding box currently overlaps the eyebrow/title.

- [ ] **Step 3: Fix the descender clip (applies at every breakpoint)**

In `resources/css/components/dock-hero.css`, `.dock-hero-ghost`:

```css
.dock-hero .dock-hero-ghost {
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
```

becomes (only `top`, `line-height`, and the new `padding-bottom` change):

```css
.dock-hero .dock-hero-ghost {
    position: absolute;
    left: 0;
    top: 0;
    z-index: 0;
    font-family: var(--font-display);
    font-size: 132px;
    font-weight: 700;
    letter-spacing: -0.04em;
    /* line-height 1 + no padding-bottom clips descenders — the same `Mu Stats`
       bug .sechead-ghost had to fix. Same fix here. */
    line-height: 1.12;
    padding-bottom: 0.1em;
    white-space: nowrap;
    pointer-events: none;
    color: transparent;
    -webkit-text-stroke: 1px color-mix(in srgb, var(--c-primary-fade) 80%, transparent);
}
```

- [ ] **Step 4: Give the copy column room to clear the ghost (desktop only)**

`.dock-hero-copy`:

```css
.dock-hero-copy {
    position: relative;
    z-index: 2;
    container-type: inline-size;
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 1.5rem;
    padding: 3.4rem 3.4rem 3.4rem 0;
}
```

becomes:

```css
.dock-hero-copy {
    position: relative;
    z-index: 2;
    container-type: inline-size;
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 1.5rem;
    /* Top padding cleared to fit the ghost's own line above (font-size 132px
       at line-height 1.12 + padding-bottom .1em ≈ 10.1rem tall, ghost starts
       at top: 0) plus a breathing gap before the eyebrow. Tune during the
       visual pass if the gap reads too tight or too loose. */
    padding: 10.5rem 3.4rem 3.4rem 0;
}
```

- [ ] **Step 5: Run the browser test to verify it passes, then run the full DockHeroTest suite for regressions**

```bash
docker exec portfolio-app-1 php artisan test --compact tests/Browser/DockHeroTest.php
```

Expected: PASS, all tests — the new one and every existing one (band centring, peek, stacking, entrance opacity are all keyed off the outer `.dock-hero`/`.dock-hero-inner` box or other elements, not `.dock-hero-copy`'s internal padding).

If the pre-existing tests fail because `.dock-hero-copy`'s taller top padding now pushes content close to or past the fixed 700px column height on some page, reduce the `10.5rem` value (not the ghost's `top`/`line-height`) until they pass again, and re-run this step.

- [ ] **Step 6: Build and eyeball, all four dock-hero pages**

On the **host**:

```bash
npm run build
```

At http://localhost:8008, check `/`, `/about-me`, `/experience`, `/projects` at 1440/1100/760/360, both themes:
- Ghost reads as a clean line above the eyebrow, no glyph overlap, no clipped descenders (check "Projects" — has a descender-free word, so also spot-check "About me" and "Experience" for their `g`/`j`-free strings — the real check is any future wordmark with a descender, so trust the CSS fix over eyeballing this specific set).
- Gap between ghost and eyebrow isn't so large it reads as disconnected, isn't so small it touches.
- ≤1200px and ≤560px layouts are unchanged (those rules weren't touched).

Adjust `padding-top` (Step 4) and/or `top` (Step 3) if needed — re-run Step 5's test after any change.

- [ ] **Step 7: Commit**

```bash
git add resources/css/components/dock-hero.css tests/Browser/DockHeroTest.php
git commit -m "fix(dock-hero): move the ghost wordmark above the title, fix its descender clip"
```

---

### Task 4: `about-me` — both sections get a section head

**Files:**
- Modify: `resources/views/about-me.blade.php:20` (About Me), `:33` (Stats)
- Modify: `resources/css/pages/about-me.css:1` (add import)
- Modify: `resources/lang/en/pages/about-me.php`, `resources/lang/cs/pages/about-me.php`
- Test: `tests/Feature/SectionHeadTest.php`

**Interfaces:**
- Consumes: `<x-portfolio.section-head>` (existing). `home/stats.head_ghost` (existing, "My stats" / "Statistiky").
- Produces: `pages/about-me.head_eyebrow`, `pages/about-me.stats_head_eyebrow` — new lang keys, en+cs.

- [ ] **Step 1: Write the failing test**

Append to `tests/Feature/SectionHeadTest.php`:

```php
test('the about-me page introduces both sections with a section head', function () {
    $this->get(route('about-me'))
        ->assertSee('The short version')
        ->assertSee('By the numbers')
        ->assertSee('<div class="sechead-ghost" aria-hidden="true">My stats</div>', false);
});

test('the about-me heads render in Czech', function () {
    $this->withSession(['locale' => 'cs'])
        ->get(route('about-me'))
        ->assertSee('Ve zkratce')
        ->assertSee('V číslech');
});

test('the about-me section titles still read from the site-content settings', function () {
    \App\Models\Setting::updateOrCreate(['key' => 'about_title'], ['value' => ['en' => 'Custom About Title']]);
    \App\Models\Setting::updateOrCreate(['key' => 'stats_title'], ['value' => ['en' => 'Custom Stats Title']]);

    $this->get(route('about-me'))
        ->assertSee('Custom About Title')
        ->assertSee('Custom Stats Title');
});
```

- [ ] **Step 2: Run it to verify it fails**

```bash
docker exec portfolio-app-1 php artisan test --compact --filter="about-me" tests/Feature/SectionHeadTest.php
```

Expected: FAIL — none of the eyebrow strings exist yet, and the page still renders plain `<h2>`.

- [ ] **Step 3: Add the copy**

`resources/lang/en/pages/about-me.php` — add near the top, after `'title' => 'About me',`:

```php
    'head_eyebrow' => 'The short version',
    'stats_head_eyebrow' => 'By the numbers',
```

`resources/lang/cs/pages/about-me.php` — same position:

```php
    'head_eyebrow' => 'Ve zkratce',
    'stats_head_eyebrow' => 'V číslech',
```

(If the Czech file doesn't already have a `'title'` key in the same shape as the English one, add these two keys at the top of the returned array regardless — position doesn't matter functionally.)

- [ ] **Step 4: Load the section-head stylesheet**

`resources/css/pages/about-me.css` currently starts with:

```css
@import '../components/dock-hero.css';
```

becomes:

```css
@import '../components/dock-hero.css';
@import '../components/section-head.css';
```

- [ ] **Step 5: Swap both heads**

In `resources/views/about-me.blade.php`, replace:

```blade
    <section id="about-me" class="portfolio-section portfolio-section--no-reveal">
        <h2>{!! \App\Models\Setting::text('about_title', $locale) !!}</h2>
```

with:

```blade
    <section id="about-me" class="portfolio-section portfolio-section--no-reveal">
        <x-portfolio.section-head
            :eyebrow="__('pages/about-me.head_eyebrow')"
            :title="\App\Models\Setting::text('about_title', $locale)"
        />
```

Replace:

```blade
    <section id="about-me-stats" class="portfolio-section">
        <h2>{{ \App\Models\Setting::text('stats_title', $locale) }}</h2>
```

with:

```blade
    <section id="about-me-stats" class="portfolio-section">
        <x-portfolio.section-head
            :ghost="__('home/stats.head_ghost')"
            :eyebrow="__('pages/about-me.stats_head_eyebrow')"
            :title="\App\Models\Setting::text('stats_title', $locale)"
        />
```

- [ ] **Step 6: Run the tests**

```bash
docker exec portfolio-app-1 php artisan test --compact tests/Feature/SectionHeadTest.php tests/Feature/AboutMePageTest.php tests/Feature/SiteContentManagementTest.php
```

Expected: PASS, all three files. `AboutMePageTest` and `SiteContentManagementTest` were not modified — they must still pass unchanged, proving the `about-me-content`/`about-me-stats-cards` markup and the settings-editing flow both survived the swap.

- [ ] **Step 7: Build and eyeball**

On the **host**: `npm run build`. At http://localhost:8008/about-me — both heads render with eyebrow + title, the Stats section shows the "My stats" ghost, the About Me section does not.

- [ ] **Step 8: Commit**

```bash
docker exec portfolio-app-1 vendor/bin/pint --dirty --format agent
git add resources/views/about-me.blade.php resources/css/pages/about-me.css resources/lang/en/pages/about-me.php resources/lang/cs/pages/about-me.php tests/Feature/SectionHeadTest.php
git commit -m "feat(section-head): open About Me and Stats on the about-me page"
```

---

### Task 5: `experience` — the page head

**Files:**
- Modify: `resources/views/experience.blade.php:22`
- Modify: `resources/css/pages/experience.css:1` (add import)
- Modify: `resources/lang/en/pages/experience.php`, `resources/lang/cs/pages/experience.php`
- Test: `tests/Feature/SectionHeadTest.php`

**Interfaces:**
- Consumes: `<x-portfolio.section-head>` (existing). `home/experience.title` (existing, "Experience" / "Zkušenosti").
- Produces: `pages/experience.head_eyebrow` — new lang key, en+cs.

- [ ] **Step 1: Write the failing test**

Append to `tests/Feature/SectionHeadTest.php`:

```php
test('the experience page introduces its section with a head and no ghost', function () {
    \App\Models\Experience::factory()->create();

    $this->get(route('experience'))
        ->assertSee('Full record')
        ->assertSee('<p class="sechead-eyebrow">Full record</p>', false)
        ->assertDontSee('sechead-ghost', false);
});

test('the experience page head renders in Czech', function () {
    \App\Models\Experience::factory()->create();

    $this->withSession(['locale' => 'cs'])
        ->get(route('experience'))
        ->assertSee('Celý přehled');
});
```

- [ ] **Step 2: Run it to verify it fails**

```bash
docker exec portfolio-app-1 php artisan test --compact --filter="experience page" tests/Feature/SectionHeadTest.php
```

Expected: FAIL — "Full record" doesn't exist on the page yet.

- [ ] **Step 3: Add the copy**

`resources/lang/en/pages/experience.php` — add after `'hero_photo_caption' => …,`:

```php
    'head_eyebrow' => 'Full record',
```

`resources/lang/cs/pages/experience.php` — same position:

```php
    'head_eyebrow' => 'Celý přehled',
```

- [ ] **Step 4: Load the section-head stylesheet**

`resources/css/pages/experience.css` currently starts with:

```css
@import '../components/dock-hero.css';
```

becomes:

```css
@import '../components/dock-hero.css';
@import '../components/section-head.css';
```

- [ ] **Step 5: Swap the head**

In `resources/views/experience.blade.php`, replace:

```blade
    <section id="experience" class="portfolio-section portfolio-section--no-reveal">
        <h2>{{ __('home/experience.title') }}</h2>
```

with:

```blade
    <section id="experience" class="portfolio-section portfolio-section--no-reveal">
        <x-portfolio.section-head
            :eyebrow="__('pages/experience.head_eyebrow')"
            :title="__('home/experience.title')"
        />
```

- [ ] **Step 6: Run the tests**

```bash
docker exec portfolio-app-1 php artisan test --compact tests/Feature/SectionHeadTest.php tests/Feature/ExperiencePageTest.php
```

Expected: PASS, both files. `ExperiencePageTest` was not modified — the filter bar, masonry grid, and badge filters must all still render.

- [ ] **Step 7: Build and eyeball**

On the **host**: `npm run build`. At http://localhost:8008/experience — head renders above the filter bar with no ghost, filter bar is not crowded.

- [ ] **Step 8: Commit**

```bash
docker exec portfolio-app-1 vendor/bin/pint --dirty --format agent
git add resources/views/experience.blade.php resources/css/pages/experience.css resources/lang/en/pages/experience.php resources/lang/cs/pages/experience.php tests/Feature/SectionHeadTest.php
git commit -m "feat(section-head): open the Experience page"
```

---

### Task 6: `projects` — a new page head where none existed

**Files:**
- Modify: `resources/views/projects.blade.php`
- Modify: `resources/css/pages/projects.css:1-2` (add import)
- Modify: `resources/lang/en/pages/projects.php`, `resources/lang/cs/pages/projects.php`
- Test: `tests/Feature/SectionHeadTest.php`

**Interfaces:**
- Consumes: `<x-portfolio.section-head>` (existing). `home/projects.title` (existing, "Projects" / "Projekty").
- Produces: `pages/projects.head_eyebrow` — new lang key, en+cs.

- [ ] **Step 1: Write the failing test**

Append to `tests/Feature/SectionHeadTest.php`:

```php
test('the projects page introduces its list with a head and no ghost', function () {
    $this->get(route('projects'))
        ->assertSee('By year')
        ->assertSee('<p class="sechead-eyebrow">By year</p>', false)
        ->assertDontSee('sechead-ghost', false);
});

test('the projects page head renders in Czech', function () {
    $this->withSession(['locale' => 'cs'])
        ->get(route('projects'))
        ->assertSee('Podle roku');
});
```

- [ ] **Step 2: Run it to verify it fails**

```bash
docker exec portfolio-app-1 php artisan test --compact --filter="projects page" tests/Feature/SectionHeadTest.php
```

Expected: FAIL — the projects page has no head at all today.

- [ ] **Step 3: Add the copy**

`resources/lang/en/pages/projects.php` — add after `'hero_tags' => […],`:

```php
    'head_eyebrow' => 'By year',
```

`resources/lang/cs/pages/projects.php` — same position:

```php
    'head_eyebrow' => 'Podle roku',
```

- [ ] **Step 4: Load the section-head stylesheet**

`resources/css/pages/projects.css` currently starts with:

```css
@import '../components/project-row.css';
@import '../components/dock-hero.css';
```

becomes:

```css
@import '../components/project-row.css';
@import '../components/dock-hero.css';
@import '../components/section-head.css';
```

- [ ] **Step 5: Add the head**

In `resources/views/projects.blade.php`, replace:

```blade
    <section id="projects" class="portfolio-section portfolio-section--no-reveal">
        @forelse ($projects as $year => $yearProjects)
```

with:

```blade
    <section id="projects" class="portfolio-section portfolio-section--no-reveal">
        <x-portfolio.section-head
            :eyebrow="__('pages/projects.head_eyebrow')"
            :title="__('home/projects.title')"
        />
        @forelse ($projects as $year => $yearProjects)
```

- [ ] **Step 6: Run the tests**

```bash
docker exec portfolio-app-1 php artisan test --compact tests/Feature/SectionHeadTest.php
```

Expected: PASS.

- [ ] **Step 7: Build and eyeball**

On the **host**: `npm run build`. At http://localhost:8008/projects — head renders above the first year group, no ghost, `.projects-year-label` per-year headings are unchanged.

- [ ] **Step 8: Commit**

```bash
docker exec portfolio-app-1 vendor/bin/pint --dirty --format agent
git add resources/views/projects.blade.php resources/css/pages/projects.css resources/lang/en/pages/projects.php resources/lang/cs/pages/projects.php tests/Feature/SectionHeadTest.php
git commit -m "feat(section-head): open the Projects page list"
```

---

### Task 7: Full verification pass

**Files:**
- Modify: `tests/Browser/SectionHeadTest.php` (extend width coverage to the three new pages)
- No other source changes expected. Any fix found here is committed with its own message.

**Interfaces:**
- Consumes: everything from Tasks 1–6.
- Produces: the go/no-go on the whole rollout.

- [ ] **Step 1: Extend the sideways-scroll browser test to every section-head page**

`tests/Browser/SectionHeadTest.php` currently only checks `/`. Replace:

```php
test('the home page never scrolls sideways at any width', function (int $width) use ($overflowJs) {
    $this->seed(\Database\Seeders\SettingSeeder::class);

    $page = visit('/')->resize($width, 900);

    expect($page->script($overflowJs))->toBeLessThanOrEqual(0);
})->with([360, 760, 1100, 1440]);
```

with:

```php
test('no section-head page scrolls sideways at any width', function (string $path, int $width) use ($overflowJs) {
    $this->seed(\Database\Seeders\SettingSeeder::class);

    $page = visit($path)->resize($width, 900);

    expect($page->script($overflowJs))->toBeLessThanOrEqual(0);
})->with([
    ['/', 360], ['/', 760], ['/', 1100], ['/', 1440],
    ['/about-me', 360], ['/about-me', 760], ['/about-me', 1100], ['/about-me', 1440],
    ['/experience', 360], ['/experience', 760], ['/experience', 1100], ['/experience', 1440],
    ['/projects', 360], ['/projects', 760], ['/projects', 1100], ['/projects', 1440],
]);
```

- [ ] **Step 2: Run it**

```bash
docker exec portfolio-app-1 php artisan test --compact tests/Browser/SectionHeadTest.php
```

Expected: PASS, 16 cases. If a page overflows at a narrow width, the fix is the same one the original rollout used — check `.portfolio-col { overflow-x: clip; }` (`app.css`) is still in place and not being defeated by the taller `.dock-hero-copy` padding-top from Task 3.

- [ ] **Step 3: Run the whole suite**

```bash
docker exec portfolio-app-1 php artisan test --compact
```

Expected: PASS. Pay particular attention to `SiteContentManagementTest`, `AboutMePageTest`, `ExperiencePageTest`, `DockHeroTest`, `DockHeroPagesTest` — none of these were meant to change behavior, only markup internals.

- [ ] **Step 4: Rebuild assets**

On the **host**:

```bash
npm run build
```

- [ ] **Step 5: Visual pass, dark theme**

http://localhost:8008 at 1440, 1100, 760, 360 — home, about-me, experience, projects. Confirm:
- home: all five sections show a ghost;
- every hero's ghost sits above the title, no overlap, no clipped descenders;
- footer wordmark is solid, not outlined;
- about-me: About Me has no ghost, Stats has the "My stats" ghost;
- experience and projects: heads render, no ghost, filter bar / year list not crowded.

- [ ] **Step 6: Visual pass, light theme**

Toggle the theme, repeat at 1440 and 360. The hero ghost is `color-mix` off `--c-primary-fade` in dark and a solid `--c-watermark` fill in light (`dock-hero.css:137-140`, untouched by this plan) — confirm it still reads correctly at its new position. The footer wordmark's new `color: var(--c-fg)` should read as normal foreground text in both themes — no separate light-mode override needed since it's no longer an outline.

- [ ] **Step 7: Screen-reader sanity check**

DevTools accessibility tree on about-me, experience, projects. Expected: no unlabelled/duplicate heading noise from the new heads, no node for any ghost word (all `aria-hidden="true"`).

- [ ] **Step 8: Commit any fixes**

```bash
docker exec portfolio-app-1 vendor/bin/pint --dirty --format agent
git add -A
git commit -m "fix(section-head): <what the pass turned up>"
```

If nothing turned up, skip this commit.
