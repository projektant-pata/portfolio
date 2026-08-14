# Experience Dock Hero Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the shared page hero on the Experience page with the design handoff's three-column bordered composition (dock / copy / photo), built as a reusable `<x-portfolio.dock-hero>` component.

**Architecture:** A new Blade component plus one new CSS partial, imported by `resources/css/pages/experience.css`. Copy stays in the existing `Setting` model and the `⚡site-content` admin page; two structural words (wordmark, dock label) move into a new lang file. The rotator/caret CSS shared by the old and new hero is extracted into its own partial so both import one definition. No new JS, no migrations, no new dependencies.

**Tech Stack:** Laravel 13, Blade components, Tailwind v4 + hand-written CSS partials, Livewire 4 (admin page only), Pest 4 (feature + browser tests), Vite, Docker.

**Source spec:** `docs/superpowers/specs/2026-08-14-experience-dock-hero-design.md`
**Design handoff:** `projektant-pata Design System.zip` → `design_handoff_experience_hero/`

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

### Task 1: Extract the rotator CSS into its own partial

Pure refactor. The blinking-caret and rotator rules currently live inside `components/page-hero.css`; the new hero needs them too. Move them once so there is a single definition.

**Files:**
- Create: `resources/css/components/hero-rotator.css`
- Modify: `resources/css/components/page-hero.css:57-74`
- Test: `tests/Browser/PageHeroTest.php` (existing, unchanged — it is the regression net)

**Interfaces:**
- Consumes: nothing.
- Produces: `resources/css/components/hero-rotator.css`, holding `#hero-rotator span`, `.hero-caret`, `@keyframes caret-blink`. Task 3 imports it from `dock-hero.css`.

- [ ] **Step 1: Create the new partial**

Create `resources/css/components/hero-rotator.css`:

```css
/* ================================================================
   HERO ROTATOR
   ================================================================
   The rotating role line and its blinking caret, shared by the
   full-width page hero and the Experience dock hero. Imported by
   both component stylesheets; the JS that drives it lives in
   resources/js/app.js (initHeroRotator).
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

- [ ] **Step 2: Remove the moved rules from `page-hero.css` and import the partial**

In `resources/css/components/page-hero.css`, delete exactly these blocks (lines 57-74 in the current file):

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

Keep `.underh1`, `.underh1 span` and `.underh1 > span#hero-rotator` where they are — they style the old hero's layout, not the rotator itself.

Add this as the **first line** of `resources/css/components/page-hero.css`:

```css
@import './hero-rotator.css';
```

- [ ] **Step 3: Rebuild assets and verify the caret still animates**

Run on the **host** (not in Docker):

```bash
npm run build
```

Expected: build succeeds, no "Failed to resolve import" for `hero-rotator.css`.

- [ ] **Step 4: Run the browser hero tests**

```bash
docker exec portfolio-app-1 php artisan test --compact --filter=PageHeroTest
```

Expected: PASS — same test count as before this task. The rotator tests ("the home hero rotator cycles its roles", "a subpage hero rotator cycles its roles") prove the extracted CSS is still loaded.

- [ ] **Step 5: Commit**

```bash
git add resources/css/components/hero-rotator.css resources/css/components/page-hero.css
git commit -m "refactor(css): extract the hero rotator rules into their own partial"
```

---

### Task 2: Settings, seeder and admin fields for the new hero copy

Adds the two new setting keys the dock hero needs (`experience_hero_tags`, `experience_hero_photo_caption`) and the lang file for the two structural words. Both new keys are **optional** — the hero must render with them empty — so the admin page grows a notion of optional fields, which it does not have today.

**Files:**
- Modify: `database/seeders/SettingSeeder.php:12-42`
- Modify: `resources/views/pages/manage/⚡site-content.blade.php`
- Create: `resources/lang/en/pages/experience.php`
- Create: `resources/lang/cs/pages/experience.php`
- Test: `tests/Feature/SiteContentDockHeroTest.php` (create)

**Interfaces:**
- Consumes: `App\Models\Setting::text()`, `Setting::list()` (existing static helpers).
- Produces:
  - `Setting` key `experience_hero_tags` → `['en' => string[], 'cs' => string[]]`, read with `Setting::list('experience_hero_tags', $locale)`.
  - `Setting` key `experience_hero_photo_caption` → `['en' => string, 'cs' => string]`, read with `Setting::text('experience_hero_photo_caption', $locale)`; may be `''`.
  - Lang keys `pages/experience.hero_wordmark` and `pages/experience.hero_dock_label`.
  - Livewire public property `array $optionalKeys` on the site-content component.

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

test('the experience lang file carries the wordmark and dock label in both locales', function () {
    expect(__('pages/experience.hero_wordmark', [], 'en'))->toBe('Experience')
        ->and(__('pages/experience.hero_wordmark', [], 'cs'))->toBe('Zkušenosti')
        ->and(__('pages/experience.hero_dock_label', [], 'en'))->toBe('Navigate')
        ->and(__('pages/experience.hero_dock_label', [], 'cs'))->toBe('Navigace');
});
```

Note on the Volt component name: the manage pages are registered in `routes/web.php`; if `Volt::test('pages.manage.site-content')` cannot resolve the component, check how the sibling tests in `tests/Feature/` address other `⚡` pages and copy that exact string.

- [ ] **Step 2: Run it to make sure it fails**

```bash
docker exec portfolio-app-1 php artisan test --compact --filter=SiteContentDockHeroTest
```

Expected: FAIL — the tags/caption keys do not exist, `pages/experience` lang file is missing.

- [ ] **Step 3: Add the new keys to the seeder**

In `database/seeders/SettingSeeder.php`, inside `$settings`, directly after the `experience_hero_roles` entry, add:

```php
            'experience_hero_tags' => [
                'en' => ['Backend', 'Hardware', 'Competitions', 'Erasmus', 'Speaking'],
                'cs' => ['Backend', 'Hardware', 'Soutěže', 'Erasmus', 'Přednášky'],
            ],
            'experience_hero_photo_caption' => ['en' => '', 'cs' => ''],
```

- [ ] **Step 4: Create the lang files**

Create `resources/lang/en/pages/experience.php`:

```php
<?php

return [
    'hero_wordmark' => 'Experience',
    'hero_dock_label' => 'Navigate',
];
```

Create `resources/lang/cs/pages/experience.php`:

```php
<?php

return [
    'hero_wordmark' => 'Zkušenosti',
    'hero_dock_label' => 'Navigace',
];
```

- [ ] **Step 5: Teach the admin page about optional fields**

In `resources/views/pages/manage/⚡site-content.blade.php`:

Add `'experience_hero_photo_caption'` to the end of the `$textKeys` array, and `'experience_hero_tags'` to the end of the `$roleListKeys` array.

Add this property directly below `$roleListKeys`:

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
            $required = $this->isOptional($key) ? 'nullable' : 'required';
            $rules["texts.{$key}.en"] = [$required, 'string', 'max:2000'];
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

In the Blade half, in the **`en` slot only**, replace the label line:

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

The `cs` slot's label line already carries no badge — leave it alone.

Nothing else changes: `save()`'s existing `?:` fallback (`cs` falls back to `en`) is correct for the optional keys too, since both being empty yields empty.

- [ ] **Step 6: Run the tests**

```bash
docker exec portfolio-app-1 php artisan test --compact --filter=SiteContentDockHeroTest
```

Expected: PASS, 5 tests.

- [ ] **Step 7: Seed the dev database and format**

```bash
docker exec portfolio-app-1 php artisan db:seed --class=SettingSeeder --no-interaction
docker exec portfolio-app-1 vendor/bin/pint --dirty --format agent
```

- [ ] **Step 8: Commit**

```bash
git add database/seeders/SettingSeeder.php "resources/views/pages/manage/⚡site-content.blade.php" resources/lang/en/pages/experience.php resources/lang/cs/pages/experience.php tests/Feature/SiteContentDockHeroTest.php
git commit -m "feat(settings): add the dock hero tags and photo caption fields"
```

---

### Task 3: The dock hero component, its desktop CSS, and the Experience page swap

The deliverable: `/experience` opens with the new three-column hero instead of `<x-portfolio.page-hero>`. Desktop layout only — Task 4 handles the breakpoints.

**Files:**
- Create: `resources/views/components/portfolio/dock-hero.blade.php`
- Create: `resources/css/components/dock-hero.css`
- Modify: `resources/css/pages/experience.css:1`
- Modify: `resources/views/experience.blade.php:5-12`
- Modify: `config/portfolio.php:37-42`
- Modify: `tests/Feature/PageHeroTest.php` (datasets + one assertion)
- Modify: `tests/Browser/PageHeroTest.php` (datasets)
- Test: `tests/Feature/DockHeroTest.php` (create)

**Interfaces:**
- Consumes: `experience_hero_suptitle`, `experience_hero_title`, `experience_hero_roles`, `experience_hero_tags`, `experience_hero_photo_caption` settings and the `pages/experience` lang keys from Task 2; `hero-rotator.css` from Task 1.
- Produces:
  - Blade component `<x-portfolio.dock-hero>` with props `title`, `eyebrow`, `roles`, `tags`, `wordmark`, `dockLabel`, `dockImage`, `dockImageAlt`, `photo`, `photoAlt`, `caption` (all strings except `roles`/`tags`, which are arrays; all optional except `title`).
  - Config key `portfolio.hero_images.experience_dock` (string, `''` until the asset exists).
  - CSS classes `.dock-hero`, `-dock`, `-dock-label`, `-copy`, `-ghost`, `-eyebrow`, `-title`, `-roles`, `-tags`, `-tag`, `-photo`, `-cap`. Task 4 adds media queries for these exact names.

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
    Setting::updateOrCreate(['key' => 'experience_hero_tags'], ['value' => [
        'en' => ['Backend', 'Erasmus'],
        'cs' => ['Backend', 'Erasmus'],
    ]]);

    $this->get(route('experience'))
        ->assertSee('🗓️ Where I have been')
        ->assertSee('<h1 class="dock-hero-title">My <span>journey</span>,</h1>', false)
        ->assertSee('>Backend</li>', false)
        ->assertSee('>Erasmus</li>', false)
        ->assertSee('id="hero-rotator"', false)
        ->assertSee('data-roles=', false);
});

test('the dock hero renders the wordmark and the dock label from the lang files', function () {
    $this->get(route('experience'))
        ->assertSee('aria-hidden="true">Experience<', false)
        ->assertSee('Navigate');

    $this->withSession(['locale' => 'cs'])
        ->get(route('experience'))
        ->assertSee('aria-hidden="true">Zkušenosti<', false)
        ->assertSee('Navigace');
});

test('the experience page still renders exactly one h1 and one rotator', function () {
    $html = $this->get(route('experience'))->assertOk()->getContent();

    expect(substr_count($html, '<h1'))->toBe(1)
        ->and(substr_count($html, 'id="hero-rotator"'))->toBe(1);
});

test('the dock hero omits the caption markup when the setting is empty', function () {
    $this->get(route('experience'))
        ->assertDontSee('dock-hero-cap', false);

    Setting::updateOrCreate(['key' => 'experience_hero_photo_caption'], ['value' => [
        'en' => '<b>Tour de App, 2024</b>Regional finals jury.',
        'cs' => '<b>Tour de App, 2024</b>Krajské finále.',
    ]]);

    $this->get(route('experience'))
        ->assertSee('dock-hero-cap', false)
        ->assertSee('<b>Tour de App, 2024</b>Regional finals jury.', false);
});

test('the dock column renders label-only while the dock asset is missing', function () {
    config()->set('portfolio.hero_images.experience_dock', '');

    $html = $this->get(route('experience'))->assertOk()->getContent();

    $dock = str($html)->between('<div class="dock-hero-dock">', '</div>')->toString();

    expect($dock)->not->toContain('<img');
});

test('the dock column renders the image once the asset is configured', function () {
    config()->set('portfolio.hero_images.experience_dock', 'images/experience-dock.webp');

    $this->get(route('experience'))
        ->assertSee('images/experience-dock.webp', false);
});

test('the dock hero omits the tag row when the tags setting is empty', function () {
    Setting::updateOrCreate(['key' => 'experience_hero_tags'], ['value' => ['en' => [], 'cs' => []]]);

    $this->get(route('experience'))
        ->assertOk()
        ->assertDontSee('dock-hero-tags', false);
});
```

- [ ] **Step 2: Run it to make sure it fails**

```bash
docker exec portfolio-app-1 php artisan test --compact --filter=DockHeroTest
```

Expected: FAIL — `class="dock-hero"` is not in the page.

- [ ] **Step 3: Create the Blade component**

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

- [ ] **Step 4: Create the desktop CSS**

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

- [ ] **Step 5: Swap the imports and the page markup**

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
        :tags="\App\Models\Setting::list('experience_hero_tags', $locale)"
        :caption="\App\Models\Setting::text('experience_hero_photo_caption', $locale)"
        :wordmark="__('pages/experience.hero_wordmark')"
        :dock-label="__('pages/experience.hero_dock_label')"
        :dock-image="config('portfolio.hero_images.experience_dock')"
        dock-image-alt=""
        :photo="config('portfolio.hero_images.experience')"
        photo-alt=""
    />
```

Both images are decorative next to the copy that names them, so both alts stay empty.

- [ ] **Step 6: Update the shared-hero tests to stop expecting a page hero on Experience**

In `tests/Feature/PageHeroTest.php`:

- In `'every public subpage renders exactly one h1'`, change the dataset from `['about-me', 'experience', 'projects']` to `['about-me', 'projects']`.
- In `'the subpage heroes render the czech copy under the cs locale'`, delete the `experience` block (the `Setting::updateOrCreate` for `experience_hero_title` and the `route('experience')` request), keeping the `projects` half. The cs coverage for Experience now lives in `DockHeroTest`.

In `tests/Browser/PageHeroTest.php`, change all four `->with([...])` datasets that list `/experience` to drop it:

- `'a subpage hero fills the first screen and still lets the next section peek in'` → `['/about-me', '/projects']`
- `'a subpage hero fills the first screen and still lets the next section peek in on mobile'` → `['/about-me', '/projects']`
- `'every hero centres its text on the same line as the home hero'` → `['/', '/about-me', '/projects']`
- `'the section after a subpage hero fades in on scroll like every other section'` → `['/about-me', '/projects']`

- [ ] **Step 7: Build and run the tests**

Host:

```bash
npm run build
```

Then:

```bash
docker exec portfolio-app-1 php artisan test --compact --filter=DockHeroTest
docker exec portfolio-app-1 php artisan test --compact --filter=PageHeroTest
```

Expected: both PASS. If `DockHeroTest`'s "renders label-only" test is brittle because `str()->between()` catches a different `</div>`, assert on the absence of `experience-dock` in the page HTML instead — the intent is "no dock `<img>` while the config value is empty".

- [ ] **Step 8: Format and commit**

```bash
docker exec portfolio-app-1 vendor/bin/pint --dirty --format agent
git add resources/views/components/portfolio/dock-hero.blade.php resources/css/components/dock-hero.css resources/css/pages/experience.css resources/views/experience.blade.php config/portfolio.php tests/Feature/DockHeroTest.php tests/Feature/PageHeroTest.php tests/Browser/PageHeroTest.php
git commit -m "feat(experience): open the page with the dock hero"
```

---

### Task 4: Responsive breakpoints and light theme

Desktop is done; this task makes the hero stack and verifies both themes.

**Files:**
- Modify: `resources/css/components/dock-hero.css` (append media queries)
- Test: `tests/Browser/DockHeroTest.php` (create)

**Interfaces:**
- Consumes: every `.dock-hero*` class from Task 3.
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

test('the hero hands off directly to the filter bar', function () {
    $page = visit('/experience')->resize(1440, 900);

    $gap = <<<'JS'
        (() => {
            const hero = document.querySelector('.dock-hero').getBoundingClientRect();
            const bar = document.querySelector('.exp-filterbar').getBoundingClientRect();
            return Math.round(bar.top - hero.bottom);
        })()
    JS;

    // 2.25rem = 36px, plus whatever the section heading above the bar occupies.
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

Expected: the desktop, wordmark-at-1440, handoff, rotator and light-theme tests PASS (Task 3 delivered those); the stacking test and the wordmark tests at 1100/520 FAIL — no media queries yet.

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

Expected: PASS, all tests in both `tests/Feature/DockHeroTest.php` and `tests/Browser/DockHeroTest.php`.

- [ ] **Step 5: Commit**

```bash
git add resources/css/components/dock-hero.css tests/Browser/DockHeroTest.php
git commit -m "feat(experience): stack the dock hero below 1200px"
```

---

### Task 5: Visual verification and crop tuning

The tests prove geometry, not looks. This task looks at the thing in both themes and tunes the one value that cannot be decided from a spec: how the reused portrait crops inside the 470px photo column.

**Files:**
- Modify: `resources/css/components/dock-hero.css` (`object-position` only, if the crop is wrong)
- Screenshots: write to the session scratchpad, not the repo

**Interfaces:**
- Consumes: everything from Tasks 1-4.
- Produces: nothing new.

- [ ] **Step 1: Confirm the app serves the page**

```bash
curl -s -o /dev/null -w '%{http_code}\n' http://localhost:8008/experience
```

Expected: `200`. A 500 usually means the Vite manifest is stale — run `npm run build` on the host.

- [ ] **Step 2: Screenshot both themes at three widths**

Drive Playwright from the **host** — faster than a browser test for a look-see. Write this to the session scratchpad as `shots.mjs` and run it there with `node shots.mjs`:

```js
import { chromium } from 'playwright';

const sizes = [[1440, 900], [1100, 900], [390, 844]];
const browser = await chromium.launch();

for (const [w, h] of sizes) {
    for (const theme of ['dark', 'light']) {
        const page = await browser.newPage({ viewport: { width: w, height: h } });
        await page.goto('http://localhost:8008/experience', { waitUntil: 'networkidle' });
        await page.evaluate((t) => {
            document.documentElement.classList.toggle('dark', t === 'dark');
        }, theme);
        await page.waitForTimeout(400);
        await page.screenshot({ path: `hero-${w}-${theme}.png` });
        await page.close();
    }
}

await browser.close();
```

If `playwright` does not resolve from the scratchpad, run node from the repo root instead — the package lives in the bind-mounted `node_modules` there.

- [ ] **Step 3: Judge the crop and the seam**

Look for three things:
1. The portrait's subject inside the photo column — if the head is cut or drifts off-centre, change `object-position: 52% 22%` in `.dock-hero-photo img` and re-shoot. This is the only value in the file meant to be tuned by eye.
2. The left gradient wash where the photo meets the copy column: it should soften the seam, not wash the picture out. In the light theme it uses `--c-bg` (`#F3F1EC`), so verify it does not look like a white smear.
3. The wordmark stroke against `--c-bg` in the light theme — `--c-primary-fade` is `#FDE68A` there and must still be visible without shouting.

- [ ] **Step 4: Run the whole suite**

```bash
docker exec portfolio-app-1 php artisan test --compact
```

Expected: PASS. Any failure elsewhere means something depended on Experience rendering `.hero-page` — fix it here rather than leaving it for later.

- [ ] **Step 5: Commit any tuning**

```bash
docker exec portfolio-app-1 vendor/bin/pint --dirty --format agent
git add resources/css/components/dock-hero.css
git commit -m "fix(experience): retune the dock hero photo crop"
```

Skip this commit if step 3 changed nothing.

- [ ] **Step 6: Report what still needs the owner**

State plainly in the final message: the dock column stays empty until a clean transparent device export lands at `public/images/experience-dock.webp` and `config('portfolio.hero_images.experience_dock')` is pointed at it, and the photo caption stays hidden until it is filled in on the admin's Site content page.

---

## Notes for whoever executes this

- Tasks are strictly ordered. Task 3 is the only one that changes what a visitor sees; Tasks 1 and 2 are safe to land on their own.
- If a test in Task 3 or 4 fails for a reason the plan did not predict, use `superpowers:systematic-debugging` — do not loosen the assertion to make it pass.
- The design handoff (`README.md` in the zip) is the authority on any value this plan does not quote. Its metric strip section is deliberately not implemented.
