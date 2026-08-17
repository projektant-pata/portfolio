# Section Head Rollout Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Give every public page section the `<x-portfolio.section-head>` opener from the *Section start* handoff, and retire the legacy centred watermark `h2` that still dresses About me, Experience, Blog and the footer.

**Architecture:** The component and its CSS already exist and ship on the home page (`resources/views/components/portfolio/section-head.blade.php`, `resources/css/components/section-head.css`). No component changes are needed for the rollout — each task swaps one page's bare `<h2>` for the component, adds the head copy to `resources/lang/{en,cs}/pages/*.php`, and asserts the rendered markup with a Pest feature test. The last task removes the now-unused global `.portfolio-page h2` watermark rule and re-homes the two things that still depend on it (the footer wordmark, the projects year heads).

**Tech Stack:** Laravel 13 / Blade, Pest 4 feature tests, Tailwind v4 + hand-written page CSS, Vite. Everything runs in Docker (`docker exec portfolio-2-app-1 …`); Vite builds run on the host.

**Spec:** `design_handoff_section_start/README.md` from `projektantpata_Design_System.zip` (reference copy of the rules quoted inline below; the handoff HTML/CSS is design reference, not code to lift).

## Global Constraints

- **No new design tokens, no new colours.** Only `--c-primary`, `--c-primary-fade`, `--c-muted`, `--c-fg`, `--font-display`, `--font-sans`, `--fw-*`, `--sp-section`, `--t-fast`, `--border-w`.
- **Ghost = the section's plain label, 1–2 words. It must never repeat the title string** — a ghost echoing the h2 reads as a rendering bug.
- **No ghost directly under a hero.** Every subpage's dock hero already carries a wordmark (`About me`, `Experience`, `Projects`, `Blog`); a second outlined word right below it collides. Those sections use the `--noghost` default (pass no `ghost` prop).
- **Two or three ghosts per page, never on consecutive sections.**
- **Title = a sentence with a point of view**, not a label. One gold `<em>` per title, never two, never italic.
- **Note = one aside** (a caveat, a count, a link out), two lines maximum. Leave it out when there is nothing honest to say.
- **Eyebrow is a `p`, not a heading.** The `h2` carries the accessible name; the ghost is `aria-hidden="true"`.
- Every copy string lands in **both** `resources/lang/en/…` and `resources/lang/cs/…`. Czech is not optional.
- Admin-editable strings stay admin-editable: `about_title` and `stats_title` come from `App\Models\Setting` and remain the `title` prop; do not move them into lang files.
- Run tests with `docker exec portfolio-2-app-1 php artisan test --compact --filter=…`; format PHP with `docker exec portfolio-2-app-1 vendor/bin/pint --dirty --format agent` before each commit.
- Do not delete existing tests. Update assertions when markup changes.

---

## Already shipped (do not redo)

- The component itself, all four parts, `--noghost` / `--center` variants, and the responsive rules — `section-head.blade.php` + `section-head.css`.
- The home page migration (five heads, ghosts `aria-hidden`, drafted copy in `resources/lang/{en,cs}/home/*.php`), covered by `tests/Feature/SectionHeadTest.php`.
- The handoff's `.sechead-page` wrapper (`overflow-x: clip` so a `nowrap` ghost cannot cause sideways scroll) — implemented as `overflow-x: clip` on `.portfolio-col` in `app.css:433`, deliberately not on `.portfolio-section` (clipping there cuts the ghost).
- The handoff's `--behind` variant was ported and then removed: the ghost is now always a hat on the h2 in flow, which is what `--behind` existed to approximate. Do not reintroduce it.
- The ghost/eyebrow geometry was retuned against the handoff's rendered reference (eyebrow at ~0.73 of the ghost's cap height, ghost-baseline-to-title gap 0.267 × ghost size). The dock hero was matched to the same numbers. Do not "fix" those magic numbers back to the handoff's `top: -4.6rem`.

## File Structure

| File | Responsibility | Tasks |
|---|---|---|
| `resources/views/about-me.blade.php` | About page: two section heads | 1, 2 |
| `resources/lang/{en,cs}/pages/about-me.php` | About page head copy | 1, 2 |
| `resources/views/experience.blade.php` | Experience page head | 3 |
| `resources/lang/{en,cs}/pages/experience.php` | Experience head copy | 3 |
| `resources/views/projects.blade.php` | Projects page head (new — the page has none today) | 4 |
| `resources/lang/{en,cs}/pages/projects.php` | Projects head copy | 4 |
| `resources/views/blog.blade.php` | Blog archive head, count moves into the note | 5 |
| `resources/views/article.blade.php` | "Read next" head | 5 |
| `resources/lang/{en,cs}/pages/blog.php` | Blog + read-next head copy | 5 |
| `resources/css/pages/blog.css` | Delete `.blog-head` / `.blog-head h2` / `.blog-head-count` once unused | 5 |
| `resources/css/app.css` | Retire the global watermark `h2` rule; fold what the footer needs into `.portfolio-footer-watermark` | 6 |
| `resources/css/components/section-head.css` | Drop the four reset declarations that only exist to beat the global rule | 6 |
| `resources/css/components/project-list.css` | Year heads get the declarations they were inheriting | 6 |
| `tests/Feature/AboutMePageTest.php`, `ExperiencePageTest.php`, `ProjectsPageTest.php`, `BlogIndexTest.php`, `ArticleReadNextTest.php` | Per-page head assertions | 1–5 |
| `tests/Browser/SectionHeadTest.php` | Sideways-scroll guard extended to the subpages | 6 |

Copy in this plan is **drafted, not signed off** — see *Open decisions* at the end. Ship it as written; adjust wording afterwards in a copy-only commit if the owner wants different words.

---

### Task 1: About me — intro section head

The page's first section currently opens with a bare watermark `h2` fed from `Setting::text('about_title')`. It sits directly under the dock hero, whose wordmark is already `About me` → **no ghost**.

**Files:**
- Modify: `resources/views/about-me.blade.php:19-20`
- Modify: `resources/lang/en/pages/about-me.php` (add head keys)
- Modify: `resources/lang/cs/pages/about-me.php` (add head keys)
- Test: `tests/Feature/AboutMePageTest.php`

**Interfaces:**
- Consumes: `<x-portfolio.section-head>` props `ghost` (optional), `eyebrow`, `title`, `note` (optional), `variant` (optional) — already implemented.
- Produces: lang keys `pages/about-me.head_eyebrow`, `pages/about-me.head_note` for Task 2's file edit to sit beside.

- [ ] **Step 1: Write the failing test**

Append to `tests/Feature/AboutMePageTest.php`:

```php
test('the about me intro is introduced by a section head', function () {
    $this->seed(\Database\Seeders\SettingSeeder::class);

    $this->get(route('about-me'))
        ->assertSee('<p class="sechead-eyebrow">Who I am</p>', false)
        ->assertSee('Longer version of the hero', false);
});

test('the about me intro head carries no ghost under the hero', function () {
    $this->seed(\Database\Seeders\SettingSeeder::class);

    $html = $this->get(route('about-me'))->getContent();

    expect($html)->toContain('sechead--noghost')
        ->and($html)->not->toContain('<div class="sechead-ghost" aria-hidden="true">About me</div>');
});
```

- [ ] **Step 2: Run the test and watch it fail**

```bash
docker exec portfolio-2-app-1 php artisan test --compact --filter='about me intro'
```

Expected: FAIL — the response does not contain `sechead-eyebrow`.

- [ ] **Step 3: Add the copy, both locales**

`resources/lang/en/pages/about-me.php` — add above `'title' => 'About me',`:

```php
    'head_eyebrow' => 'Who I am',
    'head_note' => "Longer version of the hero. In a hurry? The numbers are right below.",
```

`resources/lang/cs/pages/about-me.php` — same position:

```php
    'head_eyebrow' => 'Kdo jsem',
    'head_note' => 'Delší verze úvodu. Spěcháš? Čísla jsou hned pod tím.',
```

- [ ] **Step 4: Swap the heading for the component**

`resources/views/about-me.blade.php` — replace line 20:

```blade
        <h2>{!! \App\Models\Setting::text('about_title', $locale) !!}</h2>
```

with:

```blade
        <x-portfolio.section-head
            :eyebrow="__('pages/about-me.head_eyebrow')"
            :title="\App\Models\Setting::text('about_title', $locale)"
            :note="__('pages/about-me.head_note')"
        />
```

- [ ] **Step 5: Run the tests and watch them pass**

```bash
docker exec portfolio-2-app-1 php artisan test --compact --filter=AboutMePageTest
```

Expected: PASS, including the three pre-existing tests in that file.

- [ ] **Step 6: Format and commit**

```bash
docker exec portfolio-2-app-1 vendor/bin/pint --dirty --format agent
git add resources/views/about-me.blade.php resources/lang/en/pages/about-me.php resources/lang/cs/pages/about-me.php tests/Feature/AboutMePageTest.php
git commit -m "feat(about-me): open the intro section with the section head"
```

---

### Task 2: About me — stats section head

The second section is far enough from the hero to carry a ghost, and it is not consecutive with a ghosted section (Task 1's is `--noghost`), so the two-per-page rule holds. `stats_title` renders `My Stats`, so the ghost must be a *different* word: `Numbers` / `Čísla`.

**Files:**
- Modify: `resources/views/about-me.blade.php:32-33`
- Modify: `resources/lang/en/pages/about-me.php`
- Modify: `resources/lang/cs/pages/about-me.php`
- Test: `tests/Feature/AboutMePageTest.php`

**Interfaces:**
- Consumes: the same component; lang keys added in Task 1 live in the same files.
- Produces: nothing later tasks depend on.

- [ ] **Step 1: Write the failing test**

Append to `tests/Feature/AboutMePageTest.php`:

```php
test('the about me stats section is introduced by a ghosted section head', function () {
    $this->seed(\Database\Seeders\SettingSeeder::class);

    $this->get(route('about-me'))
        ->assertSee('<div class="sechead-ghost" aria-hidden="true">Numbers</div>', false)
        ->assertSee('<p class="sechead-eyebrow">By the numbers</p>', false);
});

test('the about me page carries exactly one ghost wordmark', function () {
    $this->seed(\Database\Seeders\SettingSeeder::class);

    $html = $this->get(route('about-me'))->getContent();

    expect(preg_match_all('/<div class="sechead-ghost"/', $html))->toBe(1);
});
```

- [ ] **Step 2: Run the test and watch it fail**

```bash
docker exec portfolio-2-app-1 php artisan test --compact --filter='ghosted section head'
```

Expected: FAIL — no `sechead-ghost` on the page yet.

- [ ] **Step 3: Add the copy, both locales**

`resources/lang/en/pages/about-me.php`:

```php
    'stats_head_ghost' => 'Numbers',
    'stats_head_eyebrow' => 'By the numbers',
    'stats_head_note' => 'The full set — the home page only shows the first four.',
```

`resources/lang/cs/pages/about-me.php`:

```php
    'stats_head_ghost' => 'Čísla',
    'stats_head_eyebrow' => 'V číslech',
    'stats_head_note' => 'Celá sada — na úvodní stránce jsou jen první čtyři.',
```

- [ ] **Step 4: Swap the heading for the component**

`resources/views/about-me.blade.php` — replace line 33:

```blade
        <h2>{{ \App\Models\Setting::text('stats_title', $locale) }}</h2>
```

with:

```blade
        <x-portfolio.section-head
            :ghost="__('pages/about-me.stats_head_ghost')"
            :eyebrow="__('pages/about-me.stats_head_eyebrow')"
            :title="\App\Models\Setting::text('stats_title', $locale)"
            :note="__('pages/about-me.stats_head_note')"
        />
```

- [ ] **Step 5: Run the tests and watch them pass**

```bash
docker exec portfolio-2-app-1 php artisan test --compact --filter=AboutMePageTest
docker exec portfolio-2-app-1 php artisan test --compact --filter=SiteContentManagement
```

Expected: both PASS. `SiteContentManagementTest` is included because it edits `about_title` / `stats_title`; the title prop keeps rendering those settings, so it must stay green.

- [ ] **Step 6: Build assets and eyeball the page**

```bash
npm run build
```

Open http://localhost:8008/about-me and confirm: ghost sits above the h2 with the eyebrow lying across its lower half, nothing clipped, note in the right column.

- [ ] **Step 7: Format and commit**

```bash
docker exec portfolio-2-app-1 vendor/bin/pint --dirty --format agent
git add resources/views/about-me.blade.php resources/lang/en/pages/about-me.php resources/lang/cs/pages/about-me.php tests/Feature/AboutMePageTest.php
git commit -m "feat(about-me): open the stats section with a ghosted section head"
```

---

### Task 3: Experience page head

`resources/views/experience.blade.php:22` renders `<h2>{{ __('home/experience.title') }}</h2>` — the word `Experience`, identical to the hero wordmark right above it. Replace with a `--noghost` head whose title says something.

**Files:**
- Modify: `resources/views/experience.blade.php:21-22`
- Modify: `resources/lang/en/pages/experience.php`
- Modify: `resources/lang/cs/pages/experience.php`
- Test: `tests/Feature/ExperiencePageTest.php`

**Interfaces:**
- Consumes: `<x-portfolio.section-head>`.
- Produces: nothing later tasks depend on.

- [ ] **Step 1: Write the failing test**

Append to `tests/Feature/ExperiencePageTest.php`:

```php
test('the experience list is introduced by a section head', function () {
    $this->get(route('experience'))
        ->assertSee('<p class="sechead-eyebrow">Full record</p>', false)
        ->assertSee('Everything, <em>filterable</em>', false)
        ->assertSee('Filter by scope, badge or search', false);
});

test('the experience page does not repeat its hero wordmark as a ghost', function () {
    $html = $this->get(route('experience'))->getContent();

    expect($html)->not->toContain('<div class="sechead-ghost"');
});
```

- [ ] **Step 2: Run the test and watch it fail**

```bash
docker exec portfolio-2-app-1 php artisan test --compact --filter='experience list is introduced'
```

Expected: FAIL — no `sechead-eyebrow` in the response.

- [ ] **Step 3: Add the copy, both locales**

`resources/lang/en/pages/experience.php`:

```php
    'head_eyebrow' => 'Full record',
    'head_title' => 'Everything, <em>filterable</em>',
    'head_note' => 'Filter by scope, badge or search. One card per thing that actually happened.',
```

`resources/lang/cs/pages/experience.php`:

```php
    'head_eyebrow' => 'Celý přehled',
    'head_title' => 'Všechno, <em>filtrovatelné</em>',
    'head_note' => 'Filtruj podle rozsahu, štítku nebo hledej. Jedna karta = jedna věc, která se stala.',
```

- [ ] **Step 4: Swap the heading for the component**

`resources/views/experience.blade.php` — replace line 22:

```blade
        <h2>{{ __('home/experience.title') }}</h2>
```

with:

```blade
        <x-portfolio.section-head
            :eyebrow="__('pages/experience.head_eyebrow')"
            :title="__('pages/experience.head_title')"
            :note="__('pages/experience.head_note')"
        />
```

- [ ] **Step 5: Run the tests and watch them pass**

```bash
docker exec portfolio-2-app-1 php artisan test --compact --filter=ExperiencePage
```

Expected: PASS. If an older assertion in that file looked for the bare `Experience` heading, update it to the new head — do not delete the test.

- [ ] **Step 6: Check the sticky filter bar still clears the head**

```bash
npm run build
```

Open http://localhost:8008/experience, scroll: `.exp-filterbar` sticks to the top as before and does not overlap the head on the way past. If it does, that is a real finding — record it and fix it in this task, not later.

- [ ] **Step 7: Format and commit**

```bash
docker exec portfolio-2-app-1 vendor/bin/pint --dirty --format agent
git add resources/views/experience.blade.php resources/lang/en/pages/experience.php resources/lang/cs/pages/experience.php tests/Feature/ExperiencePageTest.php
git commit -m "feat(experience): open the list with the section head"
```

---

### Task 4: Projects page head

`resources/views/projects.blade.php:18` drops the visitor straight into the filter bar — the page has no heading at all between hero and controls. Add a `--noghost` head above `.proj-filters` (the hero wordmark is already `Projects`).

**Files:**
- Modify: `resources/views/projects.blade.php:18-19`
- Modify: `resources/lang/en/pages/projects.php`
- Modify: `resources/lang/cs/pages/projects.php`
- Test: `tests/Feature/ProjectsPageTest.php`

**Interfaces:**
- Consumes: `<x-portfolio.section-head>`.
- Produces: nothing later tasks depend on.

- [ ] **Step 1: Write the failing test**

Append to `tests/Feature/ProjectsPageTest.php`:

```php
test('the projects list is introduced by a section head', function () {
    $this->get(route('projects'))
        ->assertSee('<p class="sechead-eyebrow">Selected work</p>', false)
        ->assertSee('Everything worth <em>showing</em>', false);
});

test('the projects head sits above the filter bar', function () {
    $html = $this->get(route('projects'))->getContent();

    expect(strpos($html, 'sechead-eyebrow'))->toBeLessThan(strpos($html, 'proj-filters'));
});
```

- [ ] **Step 2: Run the test and watch it fail**

```bash
docker exec portfolio-2-app-1 php artisan test --compact --filter='projects list is introduced'
```

Expected: FAIL — no `sechead-eyebrow` in the response.

- [ ] **Step 3: Add the copy, both locales**

`resources/lang/en/pages/projects.php`:

```php
    'head_eyebrow' => 'Selected work',
    'head_title' => 'Everything worth <em>showing</em>',
    'head_note' => 'Filter by kind or stack. The list is split by year, newest first.',
```

`resources/lang/cs/pages/projects.php`:

```php
    'head_eyebrow' => 'Vybraná práce',
    'head_title' => 'Všechno, co stojí za <em>ukázání</em>',
    'head_note' => 'Filtruj podle typu nebo technologie. Seznam je dělený po letech, od nejnovějších.',
```

- [ ] **Step 4: Add the component above the filter bar**

`resources/views/projects.blade.php` — insert directly after the opening `<section id="projects" …>` tag, before `<div class="proj-filters" …>`:

```blade
        <x-portfolio.section-head
            :eyebrow="__('pages/projects.head_eyebrow')"
            :title="__('pages/projects.head_title')"
            :note="__('pages/projects.head_note')"
        />
```

- [ ] **Step 5: Run the tests and watch them pass**

```bash
docker exec portfolio-2-app-1 php artisan test --compact --filter=ProjectsPage
```

Expected: PASS.

- [ ] **Step 6: Check the sticky filter bar**

```bash
npm run build
```

`.proj-filters` is `position: sticky; top: 0; z-index: 5` (`project-list.css:321`). Open http://localhost:8008/projects and scroll past the head: the bar must stick over the head cleanly, and the head must not be visible through it (the bar has its own background). Fix here if not.

- [ ] **Step 7: Format and commit**

```bash
docker exec portfolio-2-app-1 vendor/bin/pint --dirty --format agent
git add resources/views/projects.blade.php resources/lang/en/pages/projects.php resources/lang/cs/pages/projects.php tests/Feature/ProjectsPageTest.php
git commit -m "feat(projects): give the list a section head"
```

---

### Task 5: Blog archive head and "Read next" head

Both blog surfaces use `.blog-head` — a small uppercase label plus a mono count. Replace both with the component: the archive's dynamic count becomes the head's `note`, and the read-next label becomes a plain head. `.blog-head`, `.blog-head h2` and `.blog-head-count` in `blog.css` are then dead and go with them.

**Files:**
- Modify: `resources/views/blog.blade.php:27-35`
- Modify: `resources/views/article.blade.php:62-65`
- Modify: `resources/lang/en/pages/blog.php`
- Modify: `resources/lang/cs/pages/blog.php`
- Modify: `resources/css/pages/blog.css:32-60` (delete the three `.blog-head*` rules)
- Test: `tests/Feature/BlogIndexTest.php`, `tests/Feature/ArticleReadNextTest.php`

**Interfaces:**
- Consumes: `<x-portfolio.section-head>`; the existing `$countKey` / `$total` locals in `blog.blade.php`.
- Produces: nothing later tasks depend on.

- [ ] **Step 1: Write the failing tests**

Append to `tests/Feature/BlogIndexTest.php`:

```php
test('the blog archive is introduced by a section head carrying the count', function () {
    \App\Models\Article::factory()->create(['published_at' => now()->subDay()]);

    $this->get(route('blog'))
        ->assertSee('<p class="sechead-eyebrow">Archive</p>', false)
        ->assertSee('Everything I <em>published</em>', false)
        ->assertSee('sechead-note', false)
        ->assertSee('of 1 post', false);
});
```

Append to `tests/Feature/ArticleReadNextTest.php`:

```php
test('the read next block is introduced by a section head', function () {
    $articles = \App\Models\Article::factory()->count(3)->create(['published_at' => now()->subDay()]);

    $this->get(route('blog.show', $articles->first()->slug))
        ->assertSee('<p class="sechead-eyebrow">Keep reading</p>', false)
        ->assertSee('Read next', false);
});
```

> Check the factory calls against the top of each existing test file first and copy whatever setup those files already use (published article creation differs between them); the assertions above are what matters.

- [ ] **Step 2: Run the tests and watch them fail**

```bash
docker exec portfolio-2-app-1 php artisan test --compact --filter='section head'
```

Expected: FAIL on both new tests — `sechead-eyebrow` is absent from both pages.

- [ ] **Step 3: Add the copy, both locales**

`resources/lang/en/pages/blog.php`:

```php
    'head_eyebrow' => 'Archive',
    'head_title' => 'Everything I <em>published</em>',
    'read_next_eyebrow' => 'Keep reading',
```

`resources/lang/cs/pages/blog.php`:

```php
    'head_eyebrow' => 'Archiv',
    'head_title' => 'Všechno, co jsem <em>vydal</em>',
    'read_next_eyebrow' => 'Čti dál',
```

- [ ] **Step 4: Swap the archive head**

`resources/views/blog.blade.php` — replace the whole `<div class="blog-head">…</div>` block:

```blade
        <div class="blog-head">
            <h2>{{ __('pages/blog.list_title') }}</h2>
            <span class="blog-head-count">{!! str_replace(
                [':count', ':total'],
                ['<b>'.$articles->count().'</b>', $total],
                __('pages/blog.'.$countKey)
            ) !!}</span>
        </div>
```

with:

```blade
        <x-portfolio.section-head
            :eyebrow="__('pages/blog.head_eyebrow')"
            :title="__('pages/blog.head_title')"
            :note="str_replace(
                [':count', ':total'],
                ['<b>'.$articles->count().'</b>', $total],
                __('pages/blog.'.$countKey)
            )"
        />
```

The `note` prop already renders unescaped (`{!! $note !!}`), so the `<b>` survives; `$countKey` and `$total` are the same locals the old markup used.

- [ ] **Step 5: Swap the read-next head**

`resources/views/article.blade.php` — replace:

```blade
            <div class="blog-head">
                <h2>{{ __('pages/blog.read_next') }}</h2>
            </div>
```

with:

```blade
            <x-portfolio.section-head
                :eyebrow="__('pages/blog.read_next_eyebrow')"
                :title="__('pages/blog.read_next')"
            />
```

- [ ] **Step 6: Delete the dead CSS**

`resources/css/pages/blog.css` — remove the `/* ── Section head ── */` comment and the `.blog-head`, `.blog-head h2`, `.blog-head-count`, `.blog-head-count b` rules (lines ~32–60). Confirm nothing else references them:

```bash
grep -rn "blog-head" resources/
```

Expected: no hits.

- [ ] **Step 7: Run the tests and watch them pass**

```bash
docker exec portfolio-2-app-1 php artisan test --compact --filter='Blog'
docker exec portfolio-2-app-1 php artisan test --compact --filter='Article'
```

Expected: PASS. `tests/Browser/BlogTest.php` also touches this page — run `docker exec portfolio-2-app-1 php artisan test --compact tests/Browser/BlogTest.php` and update any assertion that looked for `.blog-head`.

- [ ] **Step 8: Format and commit**

```bash
npm run build
docker exec portfolio-2-app-1 vendor/bin/pint --dirty --format agent
git add resources/views/blog.blade.php resources/views/article.blade.php resources/lang/en/pages/blog.php resources/lang/cs/pages/blog.php resources/css/pages/blog.css tests/Feature/BlogIndexTest.php tests/Feature/ArticleReadNextTest.php
git commit -m "feat(blog): open the archive and read next with the section head"
```

---

### Task 6: Retire the global watermark `h2`

With every page migrated, `.portfolio-page h2` (`app.css:346`) no longer dresses a section heading — but two things still lean on it and must be made self-sufficient **in the same commit**: `.portfolio-footer-watermark` (its comment at `app.css:531` says outright that it inherits the look) and `.proj-list .proj-yhead h2` (year numerals, which inherit `color`/stroke behaviour and are re-asserted in light mode at `project-list.css:47`). `.blog-prose h2` (article body markdown) currently inherits the gold stroke it never wanted — removing the global rule fixes that.

**Files:**
- Modify: `resources/css/app.css:344-356` (delete the rule), `:530-535` (fold the watermark declarations into the footer class), plus the light-theme reset `html:not(.dark) .portfolio-page h2` — `grep -n 'portfolio-page h2' resources/css/app.css` for every occurrence
- Modify: `resources/css/components/section-head.css:112-116` (drop `text-align`, `margin-bottom`, `color`, `-webkit-text-stroke` resets — the comment there already says to)
- Modify: `resources/css/components/project-list.css:30-49` (make the year-head rule stand alone)
- Test: `tests/Browser/SectionHeadTest.php`

- [ ] **Step 1: Inventory every remaining `h2`**

```bash
grep -rn "<h2" resources/views/
grep -rn "portfolio-page h2" resources/css/
```

Write the list down. Expected survivors: `.portfolio-footer-watermark`, `.proj-list .proj-yhead h2`, `.blog-prose h2` (from markdown), and the `h2` inside `section-head.blade.php`. Anything else means a page was missed — go back and finish it before continuing.

- [ ] **Step 2: Extend the browser guard to the subpages**

`tests/Browser/SectionHeadTest.php` — append (reusing the `$overflowJs` already defined at the top of that file):

```php
test('the subpages never scroll sideways at any width', function (string $path, int $width) use ($overflowJs) {
    $this->seed(\Database\Seeders\SettingSeeder::class);

    $page = visit($path)->resize($width, 900);

    expect($page->script($overflowJs))->toBeLessThanOrEqual(0);
})->with(['/about-me', '/experience', '/projects', '/blog'])->with([360, 760, 1100, 1440]);
```

- [ ] **Step 3: Run it against the current (pre-deletion) CSS**

```bash
docker exec portfolio-2-app-1 php artisan test --compact tests/Browser/SectionHeadTest.php
```

Expected: PASS. This is the baseline — it proves the guard works before the CSS moves under it. If it fails now, that is a real overflow bug from Tasks 1–5; fix it before deleting anything.

- [ ] **Step 4: Make the footer watermark self-sufficient**

`resources/css/app.css` — replace the `.portfolio-footer-watermark` rule with the declarations it was inheriting:

```css
.portfolio-footer-watermark {
    /* Was inherited from the global `.portfolio-page h2` watermark rule, which
       the section-head component replaced everywhere else. The -0.45em overlap
       is what pulls the footer up over the letters. */
    z-index: 0;
    text-align: center;
    margin-bottom: -0.45em;
    font-size: var(--fs-h2);
    font-weight: 500;
    font-family: var(--font-display);
    color: color-mix(in srgb, var(--c-watermark) 12%, transparent);
    -webkit-text-stroke: 1px var(--c-watermark);
    overflow-wrap: anywhere;
}
```

If the light-theme reset (`html:not(.dark) .portfolio-page h2`) sets anything, mirror it as `html:not(.dark) .portfolio-footer-watermark` with the same declarations.

- [ ] **Step 5: Make the year heads self-sufficient**

`resources/css/components/project-list.css` — the `.proj-list .proj-yhead h2` rule already sets font, size, weight, letter-spacing, line-height, colour and stroke; add whatever the inventory in Step 1 showed it was inheriting (at minimum `overflow-wrap: anywhere` is safe to drop, `margin`/`text-align` are already declared). Then simplify the light-theme override at `:47` — with the global rule gone it no longer needs `.portfolio-page` in the selector to win, but leaving it is harmless; prefer deleting the now-stale comment over rewriting the selector.

- [ ] **Step 6: Delete the global rule and the component's resets**

`resources/css/app.css` — delete the whole `.portfolio-page h2 { … }` block and its light-theme counterpart.

`resources/css/components/section-head.css` — in `.sechead .sechead-row h2`, delete `text-align: left;`, `margin-bottom: 0;`, `color: var(--c-fg);` and `-webkit-text-stroke: 0;`, and rewrite the comment above it to say the resets are gone because the global rule is.

- [ ] **Step 7: Rebuild and run everything**

```bash
npm run build
docker exec portfolio-2-app-1 php artisan test --compact
```

Expected: full suite green (313 passing at the time of writing, plus the tests added by Tasks 1–5).

- [ ] **Step 8: Visual sweep, both themes**

Open all five public pages at 1440px and 390px in dark **and** light:
`/`, `/about-me`, `/experience`, `/projects`, `/blog`, and one article.
Confirm: footer wordmark unchanged, project year numerals still gold-outlined in both themes, article body `h2`s now solid (no stray stroke), every section head intact.

- [ ] **Step 9: Commit**

```bash
docker exec portfolio-2-app-1 vendor/bin/pint --dirty --format agent
git add resources/css/app.css resources/css/components/section-head.css resources/css/components/project-list.css tests/Browser/SectionHeadTest.php
git commit -m "refactor(css): retire the global watermark h2 rule"
```

---

### Task 7: Update the docs

`resources/css/components/section-head.css:98` promises "when the component reaches those pages, delete it there", and `docs/design-upgrade-ideas.md` §2 lists the watermark legibility bugs this rollout closes. Leave the record straight.

**Files:**
- Modify: `docs/design-upgrade-ideas.md`
- Modify: `CLAUDE.md` (the "Frontend" section mentions page-CSS loading patterns; add one line about the section-head component being site-wide)

- [ ] **Step 1: Mark the design-upgrade items done**

In `docs/design-upgrade-ideas.md` §2, append after the "My Stats reads as Mu Stats" bullet:

```markdown
> **Closed 2026-08-17** — every section heading now renders through
> `<x-portfolio.section-head>`; the ghost wordmark is `aria-hidden` and no longer
> the heading itself, and the global `.portfolio-page h2` watermark rule is gone.
> The footer wordmark keeps the old treatment by design.
```

- [ ] **Step 2: Add the component to CLAUDE.md**

Under `## Frontend`, add:

```markdown
- Every public section opens with `<x-portfolio.section-head>` (ghost wordmark, eyebrow, h2, optional note). Ghost is decorative and `aria-hidden`; copy lives in `resources/lang/{en,cs}/pages/*.php`. Rules: `resources/css/components/section-head.css`.
```

- [ ] **Step 3: Commit**

```bash
git add docs/design-upgrade-ideas.md CLAUDE.md
git commit -m "docs: record the section-head rollout"
```

---

## Open decisions

1. **Copy sign-off.** Every eyebrow/title/note above is drafted, not verified — the handoff flags the same thing about its own home-page drafts. The Czech is a translation of the English, not independently written; a native pass would tighten it.
2. **About-me titles stay admin-editable** (`about_title`, `stats_title` from `Setting`), which means the h2 there is a label (`About me`, `My Stats`), not the "sentence with a point of view" the handoff asks for. Alternative: move those titles into lang files and drop them from the site-content admin. That is a product decision about who owns the copy, so this plan keeps the admin's ownership.
3. **Ghost budget.** After this rollout only About me carries a ghost (one), plus home's five. The handoff says two-to-three per page and warns that five is wallpaper — trimming home from five to three is a separate change and is deliberately not in this plan.
4. **Blog's small-label head.** `.blog-head` was intentionally a quiet 14px label, not a big opener. Task 5 replaces it with the full component for consistency; if the archive should stay quiet, skip Task 5's blog half and keep the read-next half.
