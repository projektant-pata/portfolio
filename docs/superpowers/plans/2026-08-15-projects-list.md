# Projects list Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Rebuild `/projects` as a year-grouped index — the year printed once per group as a stroked-gold divider, the project name as the row heading, a sticky kind/stack filter bar above the list, and per-row details that expand in place.

**Architecture:** Two Blade components (`x-portfolio.project-row`, `x-portfolio.project-year-head`) render a controller-grouped collection; one new stylesheet (`resources/css/components/project-list.css`) imported by `pages/projects.css`; one `<script>` block at the foot of `projects.blade.php` does the filtering client-side, mirroring `experience.blade.php`. Four new columns on `projects` and one on `links` carry the metadata the rail, the filters, and the expanded panel need.

**Tech Stack:** Laravel 13, PHP 8.5, Blade anonymous components, Livewire 4 + Flux free (admin only), Tailwind v4 + hand-written CSS with `--c-*` tokens, PostgreSQL 17, Pest 4 (feature + browser), Vite.

**Spec:** `docs/superpowers/specs/2026-08-15-projects-list-design.md`

**Design reference:** unzip `newProjects.zip` and open `design_handoff_projects/projects-standalone.html`. Reference only — never ship its HTML or `_deps/` CSS.

## Global Constraints

- **Every PHP/Artisan command runs in Docker:** `docker exec portfolio-app-1 …`. **Vite builds run on the host:** `npm run build`.
- **Pint before every commit touching PHP:** `docker exec portfolio-app-1 vendor/bin/pint --dirty --format agent`.
- **Tests run on PostgreSQL.** Translatable JSON fields are asserted with `toEqual`, never `toBe` — `badges.name` is `jsonb` and Postgres reorders its keys.
- **No new design tokens, no new colours.** Only `--c-primary`, `--c-primary-fade`, `--c-muted`, `--c-fg`, `--c-bg`, `--c-surface`, `--c-surface-sunken`, `--badge-*`, `--font-display`, `--font-mono`, `--fw-*`, `--r-card-sm`, `--r-pill`, `--border-w`, `--t-fast`. One literal is allowed: `#4ADE80`, the live dot.
- **Class prefix is `.proj-*`.** Nothing collides with `.exp-*` / `.ab-*` / `.dock-hero-*` / `.sechead-*`.
- **Every CSS rule is written with an ancestor class** (`.proj-list .proj-name`, `.proj-filters .proj-fchip`). The global reset `.portfolio-page *` (`app.css:272`) sets `color`, `font-family`, `margin`, and `padding` on every element at specificity `0,1,0`; a bare `.proj-name` ties with it and loses or wins by stylesheet order. Two classes (`0,2,0`) beat both the reset and the element rules `.portfolio-page h2` / `h3` / `p` (`0,1,1`, `app.css:347`, `:358`, `:376`).
- **The dock hero stays and keeps the page's only ghost wordmark.** Do not add a `.sechead`, do not add a second ghost, do not touch `docs/superpowers/plans/2026-08-15-section-head.md`.
- **No lazy loading.** No `IntersectionObserver` sentinel, no skeleton rows, no server-side filter endpoint, no live region for arrivals. Do not port `.proj-sk*` from the handoff CSS.
- **Kind values** are exactly `personal`, `client`, `school`. **Status values** are exactly `live`, `archived`, `wip` (or null). **Link kinds** are exactly `live`, `repo`, `article`.
- **Heading hierarchy:** `h1` is the dock hero title, `h2` is a year, `h3` is a project name. Never print a year inside a row.
- Public pages carry **no Livewire**. Filtering is vanilla JS in a `<script>` block, the way `experience.blade.php` does it.

## File Structure

| File | Responsibility |
|---|---|
| `database/migrations/2026_08_15_000001_add_metadata_to_projects_and_links_tables.php` | **Create.** `projects.kind/client/status/role`, `links.kind`. |
| `app/Models/Project.php` | **Modify.** Fillable, `role` cast, `KINDS` / `STATUSES` constants. |
| `app/Models/Link.php` | **Modify.** Fillable, `KINDS` constant. |
| `database/factories/ProjectFactory.php` | **Modify.** Defaults + `client()` / `archived()` states. |
| `database/factories/LinkFactory.php` | **Modify.** `kind` default. |
| `resources/views/pages/manage/⚡projects.blade.php` | **Modify.** Kind, client, status, role fields; link `kind`. |
| `resources/views/components/manage/link-repeater.blade.php` | **Modify.** Optional `with-kind` select. |
| `database/seeders/ProjectsSeeder.php` | **Modify.** Real metadata for the three projects; typed links. |
| `app/Http/Controllers/ProjectsController.php` | **Modify.** Year-first ordering; pass the stack badge list. |
| `resources/views/components/portfolio/project-year-head.blade.php` | **Create.** `h2` year, rule, count. |
| `resources/views/components/portfolio/project-row.blade.php` | **Rewrite.** Rail, shot, body, expand panel, toggle. |
| `resources/css/components/project-list.css` | **Create.** All `.proj-*` rules. |
| `resources/css/components/project-row.css` | **Delete.** Old zigzag. |
| `resources/css/pages/projects.css` | **Modify.** Imports; drop `.projects-year-*`. |
| `resources/views/projects.blade.php` | **Modify.** Filter bar, list, empty state, filter script. |
| `resources/lang/{en,cs}/pages/projects.php` | **Modify.** All new copy. |
| `tests/Feature/ProjectsPageTest.php` | **Create.** Ordering, grouping, headings, metadata, locale, empty. |
| `tests/Browser/ProjectsListTest.php` | **Create.** Filters, expand, no sideways scroll. |

---

### Task 1: Metadata columns, models, admin CRUD

The rail, the Kind filter, and the expanded panel are all reads of columns that do not exist yet. Nothing else can be built first.

**Files:**
- Create: `database/migrations/2026_08_15_000001_add_metadata_to_projects_and_links_tables.php`
- Modify: `app/Models/Project.php`, `app/Models/Link.php`
- Modify: `database/factories/ProjectFactory.php`, `database/factories/LinkFactory.php`
- Modify: `resources/views/pages/manage/⚡projects.blade.php`
- Modify: `resources/views/components/manage/link-repeater.blade.php`
- Test: `tests/Feature/ProjectsManagementTest.php` (append)

**Interfaces:**
- Consumes: nothing.
- Produces: `Project::KINDS` = `['personal','client','school']`, `Project::STATUSES` = `['live','archived','wip']`, `Link::KINDS` = `['live','repo','article']`. Columns `projects.kind` (string, not null, default `personal`), `projects.client` (string, nullable), `projects.status` (string, nullable), `projects.role` (json, nullable, cast `array`, read via `getTranslation('role', $locale)`), `links.kind` (string, not null, default `live`). Factory states `ProjectFactory::client()` and `ProjectFactory::archived()`. Tasks 2–5 read all of these.

- [ ] **Step 1: Write the failing tests**

Append to `tests/Feature/ProjectsManagementTest.php`:

```php
test('can create project with kind, client, status and role', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::manage.projects')
        ->set('header', ['en' => 'U Sladovny', 'cs' => ''])
        ->set('slug', 'u-sladovny')
        ->set('year', '2025')
        ->set('kind', 'client')
        ->set('client', 'PekneWeby')
        ->set('status', 'live')
        ->set('role', ['en' => 'Front-end and back-end', 'cs' => 'Front-end a back-end'])
        ->call('save')
        ->assertHasNoErrors();

    $project = Project::first();

    expect($project->kind)->toBe('client')
        ->and($project->client)->toBe('PekneWeby')
        ->and($project->status)->toBe('live')
        ->and($project->role)->toEqual(['en' => 'Front-end and back-end', 'cs' => 'Front-end a back-end']);
});

test('project kind must be one of the allowed values', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::manage.projects')
        ->set('header', ['en' => 'Bad kind', 'cs' => ''])
        ->set('slug', 'bad-kind')
        ->set('year', '2026')
        ->set('kind', 'freelance')
        ->call('save')
        ->assertHasErrors(['kind']);
});

test('project status may be left empty', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::manage.projects')
        ->set('header', ['en' => 'No status', 'cs' => ''])
        ->set('slug', 'no-status')
        ->set('year', '2026')
        ->set('status', '')
        ->call('save')
        ->assertHasNoErrors();

    expect(Project::first()->status)->toBeNull();
});

test('project defaults to the personal kind', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::manage.projects')
        ->set('header', ['en' => 'Default kind', 'cs' => ''])
        ->set('slug', 'default-kind')
        ->set('year', '2026')
        ->call('save')
        ->assertHasNoErrors();

    expect(Project::first()->kind)->toBe('personal');
});

test('links are saved with their kind', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::manage.projects')
        ->set('header', ['en' => 'With links', 'cs' => ''])
        ->set('slug', 'with-links')
        ->set('year', '2026')
        ->set('links', [
            ['url' => 'https://example.com', 'kind' => 'live', 'alt' => ['en' => '', 'cs' => ''], 'img_url' => ''],
            ['url' => 'https://github.com/a/b', 'kind' => 'repo', 'alt' => ['en' => '', 'cs' => ''], 'img_url' => ''],
        ])
        ->call('save')
        ->assertHasNoErrors();

    expect(Project::first()->links->pluck('kind')->all())->toBe(['live', 'repo']);
});

test('link kind must be one of the allowed values', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::manage.projects')
        ->set('header', ['en' => 'Bad link kind', 'cs' => ''])
        ->set('slug', 'bad-link-kind')
        ->set('year', '2026')
        ->set('links', [
            ['url' => 'https://example.com', 'kind' => 'video', 'alt' => ['en' => '', 'cs' => ''], 'img_url' => ''],
        ])
        ->call('save')
        ->assertHasErrors(['links.0.kind']);
});

test('editing a project loads its metadata into the form', function () {
    $user = User::factory()->create();
    $project = Project::factory()->client()->create(['status' => 'archived']);

    Livewire::actingAs($user)
        ->test('pages::manage.projects')
        ->call('openEdit', $project->id)
        ->assertSet('kind', 'client')
        ->assertSet('status', 'archived')
        ->assertSet('client', $project->client);
});
```

- [ ] **Step 2: Run the tests to verify they fail**

```bash
docker exec portfolio-app-1 php artisan test --compact --filter=ProjectsManagementTest
```

Expected: FAIL — `Unable to set component data. Public property [$kind] not found`.

- [ ] **Step 3: Create the migration**

```bash
docker exec portfolio-app-1 php artisan make:migration add_metadata_to_projects_and_links_tables --no-interaction
```

Rename the generated file to `2026_08_15_000001_add_metadata_to_projects_and_links_tables.php` so it sorts after the July/August batch, and write:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('kind', 20)->default('personal')->after('year');
            $table->string('client')->nullable()->after('kind');
            $table->string('status', 20)->nullable()->after('client');
            $table->json('role')->nullable()->after('description');
        });

        Schema::table('links', function (Blueprint $table) {
            $table->string('kind', 20)->default('live')->after('url');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['kind', 'client', 'status', 'role']);
        });

        Schema::table('links', function (Blueprint $table) {
            $table->dropColumn('kind');
        });
    }
};
```

`kind` and `status` are plain string columns with an application-level allow-list, matching `experiences.type`. No Postgres enum — adding a value must stay a code change, not a migration.

- [ ] **Step 4: Run the migration**

```bash
docker exec portfolio-app-1 php artisan migrate --no-interaction
```

Expected: `DONE`.

- [ ] **Step 5: Update the models**

`app/Models/Project.php` — replace the `$fillable` and `$casts` blocks and add the constants above them:

```php
    /** @var list<string> */
    public const KINDS = ['personal', 'client', 'school'];

    /** @var list<string> */
    public const STATUSES = ['live', 'archived', 'wip'];

    protected $fillable = [
        'year',
        'kind',
        'client',
        'status',
        'slug',
        'header',
        'description',
        'role',
        'img_url',
        'sort_order',
    ];

    protected $casts = [
        'year' => 'integer',
        'header' => 'array',
        'description' => 'array',
        'role' => 'array',
    ];
```

`app/Models/Link.php` — add the constant and `kind` to `$fillable`:

```php
    /** @var list<string> */
    public const KINDS = ['live', 'repo', 'article'];
```

Add `'kind',` to the existing `$fillable` array, after `'url'`.

- [ ] **Step 6: Update the factories**

`database/factories/ProjectFactory.php` — add `'kind' => 'personal'`, `'client' => null`, `'status' => 'live'`, `'role' => ['en' => 'Solo build']` to `definition()`, and append two states:

```php
    public function client(): static
    {
        return $this->state(fn () => [
            'kind' => 'client',
            'client' => $this->faker->company(),
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn () => ['status' => 'archived']);
    }
```

`database/factories/LinkFactory.php` — add `'kind' => 'live'` to `definition()`.

- [ ] **Step 7: Add the form state to the admin component**

In `resources/views/pages/manage/⚡projects.blade.php`, after `public string $year = '';` add:

```php
    public string $kind = 'personal';
    public string $client = '';
    public string $status = '';
    public array $role = ['en' => '', 'cs' => ''];
```

In `openEdit()`, after the `$this->year = …` line add:

```php
        $this->kind = $project->kind;
        $this->client = $project->client ?? '';
        $this->status = $project->status ?? '';
        $this->role = array_merge(['en' => '', 'cs' => ''], $project->role ?? []);
```

In `openEdit()`, the `$this->links = …` map gains the kind:

```php
        $this->links = $project->links->map(fn ($l) => [
            'url' => $l->url,
            'kind' => $l->kind,
            'alt' => array_merge(['en' => '', 'cs' => ''], $l->alt ?? []),
            'img_url' => $l->img_url ?? '',
        ])->toArray();
```

In `addLink()`:

```php
        $this->links[] = ['url' => '', 'kind' => 'live', 'alt' => ['en' => '', 'cs' => ''], 'img_url' => ''];
```

In `resetForm()`, after `$this->year = '';` add:

```php
        $this->kind = 'personal';
        $this->client = '';
        $this->status = '';
        $this->role = ['en' => '', 'cs' => ''];
```

- [ ] **Step 8: Validate and persist the new fields**

In `save()`, add to the `validate()` array — `Rule` is already imported fully-qualified in this file, keep that style:

```php
            'kind' => ['required', 'string', \Illuminate\Validation\Rule::in(Project::KINDS)],
            'client' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', \Illuminate\Validation\Rule::in(Project::STATUSES)],
            'role' => ['nullable', 'array'],
            'role.en' => ['nullable', 'string', 'max:255'],
            'role.cs' => ['nullable', 'string', 'max:255'],
            'links.*.kind' => ['nullable', 'string', \Illuminate\Validation\Rule::in(Link::KINDS)],
```

Extend the `$data` array:

```php
        $data = [
            'header' => array_filter($validated['header'], fn ($v) => filled($v)),
            'description' => array_filter($validated['description'] ?? [], fn ($v) => filled($v)) ?: null,
            'role' => array_filter($validated['role'] ?? [], fn ($v) => filled($v)) ?: null,
            'slug' => $validated['slug'],
            'year' => (int) $validated['year'],
            'kind' => $validated['kind'],
            'client' => $validated['kind'] === 'client' ? ($validated['client'] ?: null) : null,
            'status' => $validated['status'] ?: null,
            'img_url' => $this->img_url ?: null,
        ];
```

`client` is nulled unless the kind is `client`, so a kind switched back to `personal` cannot leave a stale company name printed in the rail.

Extend the link create call:

```php
        foreach ($filteredLinks as $linkData) {
            $project->links()->create([
                'url' => $linkData['url'],
                'kind' => $linkData['kind'] ?? 'live',
                'alt' => array_filter($linkData['alt'] ?? [], fn ($v) => filled($v)) ?: null,
                'img_url' => $linkData['img_url'] ?: null,
            ]);
        }
```

- [ ] **Step 9: Add the form fields**

In the same file's `<x-manage.locale-tabs>` block, add a Role field to each slot — inside `<x-slot:en>` after the Description field:

```blade
                    <flux:field>
                        <flux:label>Role</flux:label>
                        <flux:input wire:model="role.en" placeholder="e.g. Front-end and back-end" />
                        <flux:error name="role.en" />
                    </flux:field>
```

and inside `<x-slot:cs>` after its Description field:

```blade
                    <flux:field>
                        <flux:label>Role</flux:label>
                        <flux:input wire:model="role.cs" placeholder="např. Front-end a back-end" />
                        <flux:error name="role.cs" />
                    </flux:field>
```

In the non-translatable grid, after the Year field:

```blade
                <flux:field>
                    <flux:label>Kind <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
                    <flux:select wire:model.live="kind">
                        <flux:select.option value="personal">Personal</flux:select.option>
                        <flux:select.option value="client">Client</flux:select.option>
                        <flux:select.option value="school">School</flux:select.option>
                    </flux:select>
                    <flux:error name="kind" />
                </flux:field>

                <flux:field>
                    <flux:label>Status</flux:label>
                    <flux:select wire:model="status">
                        <flux:select.option value="">—</flux:select.option>
                        <flux:select.option value="live">Live</flux:select.option>
                        <flux:select.option value="archived">Archived</flux:select.option>
                        <flux:select.option value="wip">In progress</flux:select.option>
                    </flux:select>
                    <flux:error name="status" />
                </flux:field>

                @if ($kind === 'client')
                    <flux:field class="col-span-2">
                        <flux:label>Client name</flux:label>
                        <flux:input wire:model="client" placeholder="e.g. PekneWeby" />
                        <flux:error name="client" />
                    </flux:field>
                @endif
```

`wire:model.live` on `kind` is what makes the client field appear without a save.

Change the repeater call to opt into the kind select:

```blade
                <x-manage.link-repeater :links="$links" :translatable-alt="true" :with-kind="true" />
```

- [ ] **Step 10: Add the kind select to the link repeater**

`resources/views/components/manage/link-repeater.blade.php` — the props line becomes:

```blade
@props(['links' => [], 'translatableAlt' => false, 'withKind' => false])
```

and inside the `@foreach`, directly after the `<flux:error name="links.{{ $i }}.url" />` line:

```blade
            @if ($withKind)
                <flux:field>
                    <flux:label>Kind</flux:label>
                    <flux:select wire:model="links.{{ $i }}.kind">
                        <flux:select.option value="live">Live site</flux:select.option>
                        <flux:select.option value="repo">Source</flux:select.option>
                        <flux:select.option value="article">Write-up</flux:select.option>
                    </flux:select>
                    <flux:error name="links.{{ $i }}.kind" />
                </flux:field>
            @endif
```

`⚡experiences.blade.php` calls the repeater without `with-kind`, so its JSON links are untouched.

- [ ] **Step 11: Run the tests**

```bash
docker exec portfolio-app-1 php artisan test --compact --filter=ProjectsManagementTest
```

Expected: PASS, all tests including the seven new ones.

- [ ] **Step 12: Run the neighbouring suites for regressions**

```bash
docker exec portfolio-app-1 php artisan test --compact --filter="LinksManagementTest|ExperienceManagementTest|DatabaseSeederTest"
```

Expected: PASS. `LinksManagementTest` touches the same table; `ExperienceManagementTest` shares the repeater.

- [ ] **Step 13: Format and commit**

```bash
docker exec portfolio-app-1 vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat(projects): add kind, client, status, role and link kind"
```

---

### Task 2: Real content, test rows purged

The list is about to show three rows across three years. It must show real ones.

**Files:**
- Modify: `database/seeders/ProjectsSeeder.php`
- Test: `tests/Feature/DatabaseSeederTest.php` (append)

**Interfaces:**
- Consumes: `Project::KINDS`, `Project::STATUSES`, `Link::KINDS` from Task 1.
- Produces: exactly three projects — `spse-hub` (2022, school, archived), `u-sladovny` (2025, client `PekneWeby`, live), `portfolio` (2026, personal, live) — each with a `role` in both locales and every link carrying a `kind`. Task 3's tests assume nothing about seeded data; they build their own factories.

- [ ] **Step 1: Write the failing test**

Append to `tests/Feature/DatabaseSeederTest.php`:

```php
test('the projects seeder gives every project a kind, a status and a bilingual role', function () {
    $this->seed(\Database\Seeders\BadgesSeeder::class);
    $this->seed(\Database\Seeders\ProjectsSeeder::class);

    $projects = \App\Models\Project::all();

    expect($projects)->toHaveCount(3);

    $projects->each(function ($project) {
        expect($project->kind)->toBeIn(\App\Models\Project::KINDS)
            ->and($project->status)->toBeIn(\App\Models\Project::STATUSES)
            ->and($project->role)->toHaveKeys(['en', 'cs']);
    });

    expect($projects->firstWhere('slug', 'u-sladovny')->client)->toBe('PekneWeby');
});

test('the projects seeder types every link', function () {
    $this->seed(\Database\Seeders\BadgesSeeder::class);
    $this->seed(\Database\Seeders\ProjectsSeeder::class);

    $kinds = \App\Models\Link::pluck('kind')->unique()->values();

    expect($kinds->every(fn ($kind) => in_array($kind, \App\Models\Link::KINDS, true)))->toBeTrue();
    expect(\App\Models\Link::where('url', 'like', '%github.com%')->pluck('kind')->unique()->all())->toBe(['repo']);
});
```

- [ ] **Step 2: Run it to verify it fails**

```bash
docker exec portfolio-app-1 php artisan test --compact --filter=DatabaseSeederTest
```

Expected: FAIL — `role` is null, so `toHaveKeys` fails.

- [ ] **Step 3: Extend the seeder**

In `database/seeders/ProjectsSeeder.php`, add the metadata to each `Project::create([…])` call.

`$spsehub`:

```php
            'kind' => 'school',
            'status' => 'archived',
            'role' => [
                'en' => 'Everything — my first hand-written site',
                'cs' => 'Všechno — moje první ručně psané stránky',
            ],
```

`$usladovny`:

```php
            'kind' => 'client',
            'client' => 'PekneWeby',
            'status' => 'live',
            'role' => [
                'en' => 'Front-end and back-end',
                'cs' => 'Front-end a back-end',
            ],
```

`$portfolio`:

```php
            'kind' => 'personal',
            'status' => 'live',
            'role' => [
                'en' => 'Design and build, start to finish',
                'cs' => 'Návrh i realizace, od začátku do konce',
            ],
```

Add `'kind' => …` to each `Link::create([…])` call: `repo` for the two `github.com` URLs, `live` for `hyvlri22.llmp.spse-net.cz` and `usladovnychrudim.cz`.

The seeder already opens with `Project::query()->delete()`, which cascades to links and `project_badge` — running it is what removes `Test #1`–`Test #10`.

- [ ] **Step 4: Run the tests**

```bash
docker exec portfolio-app-1 php artisan test --compact --filter=DatabaseSeederTest
```

Expected: PASS.

- [ ] **Step 5: Reseed the dev database**

```bash
docker exec portfolio-app-1 php artisan db:seed --class=ProjectsSeeder --no-interaction
docker exec portfolio-app-1 php artisan tinker --execute 'echo App\Models\Project::count()." projects\n";'
```

Expected: `3 projects`. The ten `Test #n` rows are gone.

- [ ] **Step 6: Format and commit**

```bash
docker exec portfolio-app-1 vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat(projects): seed real kind, status, role and typed links"
```

---

### Task 3: Year-grouped list

Replaces the zigzag with the year-head + rail + shot + body row. No filters, no expand yet — this task is finished when the static list renders correctly at desktop width.

**Files:**
- Modify: `app/Http/Controllers/ProjectsController.php`
- Create: `resources/views/components/portfolio/project-year-head.blade.php`
- Rewrite: `resources/views/components/portfolio/project-row.blade.php`
- Create: `resources/css/components/project-list.css`
- Delete: `resources/css/components/project-row.css`
- Modify: `resources/css/pages/projects.css`, `resources/views/projects.blade.php`
- Modify: `resources/lang/en/pages/projects.php`, `resources/lang/cs/pages/projects.php`
- Test: `tests/Feature/ProjectsPageTest.php` (create)

**Interfaces:**
- Consumes: the columns and constants from Task 1.
- Produces: the view receives `$projects` (an `Illuminate\Support\Collection` keyed by year, descending) and `$stackBadges` (a `Collection` of `Badge` models attached to at least one project, ordered by English name). Components `<x-portfolio.project-year-head :year :count />` and `<x-portfolio.project-row :project :locale />`. Markup contract for Task 5: each row is `article.proj-item[data-kind][data-stack]`, each group is `section.proj-group` whose head is `.proj-yhead` with `.proj-ycount`. Task 4 adds `.proj-more` inside `.proj-body`.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/ProjectsPageTest.php`:

```php
<?php

use App\Models\Badge;
use App\Models\Link;
use App\Models\Project;

test('projects page returns 200', function () {
    Project::factory()->create();

    $this->get(route('projects'))->assertOk();
});

test('projects are grouped by year, newest first', function () {
    Project::factory()->create(['year' => 2022, 'header' => ['en' => 'Old one']]);
    Project::factory()->create(['year' => 2026, 'header' => ['en' => 'New one']]);

    $response = $this->get(route('projects'));

    expect($response->viewData('projects')->keys()->all())->toBe([2026, 2022]);
});

test('sort order breaks ties inside a year', function () {
    Project::factory()->create(['year' => 2026, 'sort_order' => 2, 'header' => ['en' => 'Second']]);
    Project::factory()->create(['year' => 2026, 'sort_order' => 1, 'header' => ['en' => 'First']]);

    $response = $this->get(route('projects'));

    $headers = $response->viewData('projects')[2026]->map(fn ($p) => $p->header['en'])->all();

    expect($headers)->toBe(['First', 'Second']);
});

test('each year prints one head carrying its project count', function () {
    Project::factory()->count(2)->create(['year' => 2026]);
    Project::factory()->create(['year' => 2022]);

    $html = $this->get(route('projects'))->getContent();

    expect(substr_count($html, 'class="proj-yhead"'))->toBe(2);
    expect($html)->toContain('2 projects')->toContain('1 project');
});

test('the project name is the row heading and the year is never printed inside a row', function () {
    Project::factory()->create(['year' => 2026, 'header' => ['en' => 'Portfolio']]);

    $html = $this->get(route('projects'))->getContent();

    expect($html)->toContain('<h3 class="proj-name">Portfolio</h3>');
    expect($html)->not->toContain('class="proj-year"');
});

test('the rail prints kind and status', function () {
    Project::factory()->client()->create(['client' => 'PekneWeby', 'status' => 'live']);

    $html = $this->get(route('projects'))->getContent();

    expect($html)->toContain('Client · PekneWeby')
        ->toContain('data-state="live"')
        ->toContain('Live');
});

test('a project without a status prints no status line', function () {
    Project::factory()->create(['status' => null]);

    $html = $this->get(route('projects'))->getContent();

    expect($html)->not->toContain('proj-status');
});

test('the page passes only badges attached to a project as stack filters', function () {
    $attached = Badge::factory()->create();
    $unattached = Badge::factory()->create();
    Project::factory()->create()->badges()->attach($attached);

    $stackBadges = $this->get(route('projects'))->viewData('stackBadges');

    expect($stackBadges->pluck('id')->all())->toBe([$attached->id])
        ->and($stackBadges->pluck('id')->all())->not->toContain($unattached->id);
});

test('the czech locale renders czech project copy', function () {
    Project::factory()->create([
        'header' => ['en' => 'Portfolio', 'cs' => 'Portfólio'],
        'description' => ['en' => 'English body', 'cs' => 'Český text'],
        'status' => 'archived',
    ]);

    $this->withSession(['locale' => 'cs'])
        ->get(route('projects'))
        ->assertSee('Portfólio')
        ->assertSee('Český text')
        ->assertSee('Archivováno');
});

test('a page with no projects renders the empty list message', function () {
    $this->get(route('projects'))->assertSee('No projects yet');
});
```

- [ ] **Step 2: Run it to verify it fails**

```bash
docker exec portfolio-app-1 php artisan test --compact --filter=ProjectsPageTest
```

Expected: FAIL — no `stackBadges` view data, no `.proj-*` markup.

- [ ] **Step 3: Add the English copy**

`resources/lang/en/pages/projects.php` — keep `hero_wordmark` and `hero_tags`, add:

```php
    'kind_personal' => 'Personal',
    'kind_client' => 'Client',
    'kind_school' => 'School',
    'status_live' => 'Live',
    'status_archived' => 'Archived',
    'status_wip' => 'In progress',
    'year_count_one' => ':count project',
    'year_count_few' => ':count projects',
    'year_count_many' => ':count projects',
    'empty_list' => 'No projects yet.',
```

- [ ] **Step 4: Add the Czech copy**

`resources/lang/cs/pages/projects.php` — keep `hero_wordmark` and `hero_tags`, add:

```php
    'kind_personal' => 'Vlastní',
    'kind_client' => 'Klientský',
    'kind_school' => 'Školní',
    'status_live' => 'Živé',
    'status_archived' => 'Archivováno',
    'status_wip' => 'Rozpracováno',
    'year_count_one' => ':count projekt',
    'year_count_few' => ':count projekty',
    'year_count_many' => ':count projektů',
    'empty_list' => 'Zatím žádné projekty.',
```

- [ ] **Step 5: Fix the controller**

Replace `app/Http/Controllers/ProjectsController.php` in full:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\Project;

class ProjectsController extends Controller
{
    public function __invoke()
    {
        $projects = Project::with(['badges', 'links'])
            ->orderBy('year', 'desc')
            ->orderBy('sort_order')
            ->orderByRaw("header->>'en'")
            ->get()
            ->groupBy('year');

        $stackBadges = Badge::whereHas('projects')
            ->orderByRaw("name->>'en'")
            ->get();

        return view('projects', compact('projects', 'stackBadges'));
    }
}
```

The old code ordered by `sort_order` **before** `year`, which a `groupBy('year')` cannot express — that is the ordering bug this fixes.

`whereHas('projects')` needs the inverse relation. Add it to `app/Models/Badge.php` if it is not already there:

```php
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_badge');
    }
```

with `use Illuminate\Database\Eloquent\Relations\BelongsToMany;` at the top.

- [ ] **Step 6: Create the year-head component**

Create `resources/views/components/portfolio/project-year-head.blade.php`:

```blade
@props(['year', 'count'])

@php
    $key = $count === 1 ? 'one' : ($count >= 2 && $count <= 4 ? 'few' : 'many');
@endphp

<div class="proj-yhead">
    <h2>{{ $year }}</h2>
    <span class="proj-yline" aria-hidden="true"></span>
    <span
        class="proj-ycount"
        data-one="{{ __('pages/projects.year_count_one') }}"
        data-few="{{ __('pages/projects.year_count_few') }}"
        data-many="{{ __('pages/projects.year_count_many') }}"
    >{{ __('pages/projects.year_count_'.$key, ['count' => $count]) }}</span>
</div>
```

The three `data-*` strings are what Task 5's filter script re-renders the count from; the server still prints the correct value for a no-JS visitor.

- [ ] **Step 7: Rewrite the row component**

Replace `resources/views/components/portfolio/project-row.blade.php` in full:

```blade
@props(['project', 'locale'])

@php
    $kindLabel = __('pages/projects.kind_'.$project->kind);
    $railKind = $project->kind === 'client' && $project->client
        ? $kindLabel.' · '.$project->client
        : $kindLabel;
    $stack = $project->badges->pluck('slug')->values();
@endphp

<article class="proj-item" data-kind="{{ $project->kind }}" data-stack='@json($stack)'>
    <div class="proj-rail">
        <p class="proj-kind">{{ $railKind }}</p>
        @if ($project->status)
            <p class="proj-status" data-state="{{ $project->status }}">{{ __('pages/projects.status_'.$project->status) }}</p>
        @endif
    </div>

    <div class="proj-shot">
        @if ($project->img_url)
            <img src="{{ asset($project->img_url) }}" alt="{{ $project->getTranslation('header', $locale) }}" loading="lazy">
        @endif
    </div>

    <div class="proj-body">
        <h3 class="proj-name">{{ $project->getTranslation('header', $locale) }}</h3>

        @if ($project->getTranslation('description', $locale))
            <p class="proj-desc">{{ $project->getTranslation('description', $locale) }}</p>
        @endif

        @if ($project->badges->isNotEmpty())
            <div class="proj-chips">
                @foreach ($project->badges as $badge)
                    <span class="proj-chip" style="--bc: {{ $badge->color }}">{{ $badge->getTranslation('name', $locale) }}</span>
                @endforeach
            </div>
        @endif
    </div>
</article>
```

The empty `.proj-shot` with no `img` is deliberate: the bordered sunken box holds the row's geometry whether or not there is a screenshot.

- [ ] **Step 8: Create the stylesheet**

Create `resources/css/components/project-list.css`:

```css
/* ── Projects list ─────────────────────────────────────────
   /projects: rows grouped under one year head each. The year
   is a group divider — printed once per year, never per row.
   Every selector carries an ancestor class so it clears the
   `.portfolio-page *` reset (0,1,0) and the element rules
   `.portfolio-page h2 / h3 / p` (0,1,1). No new tokens. */

.proj-list {
    display: grid;
    overflow-x: clip;
}

.proj-list .proj-group + .proj-group {
    margin-top: 1.5rem;
}

/* ── year head ── */
.proj-list .proj-yhead {
    position: sticky;
    top: 0;
    z-index: 3;
    display: grid;
    grid-template-columns: auto 1fr auto;
    align-items: center;
    gap: 1.1rem;
    padding: 1.1rem 0 0.9rem;
    background: var(--c-bg);
}

.proj-list .proj-yhead h2 {
    margin: 0;
    text-align: left;
    font-family: var(--font-display);
    font-size: 2.6rem;
    font-weight: var(--fw-bold);
    letter-spacing: -0.04em;
    line-height: 0.85;
    color: transparent;
    -webkit-text-stroke: 1px var(--c-primary);
}

.proj-list .proj-yline {
    height: 1px;
    background: linear-gradient(
        90deg,
        color-mix(in srgb, var(--c-primary-fade) 90%, transparent),
        color-mix(in srgb, var(--c-primary-fade) 15%, transparent)
    );
}

.proj-list .proj-ycount {
    font-family: var(--font-mono);
    font-size: 10px;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--c-muted);
    white-space: nowrap;
}

/* ── row ── */
.proj-list .proj-item {
    display: grid;
    grid-template-columns: 5.5rem 18rem 1fr 6rem;
    gap: 2rem;
    padding: 1.75rem 0;
    border-top: 1px solid color-mix(in srgb, var(--c-primary-fade) 55%, transparent);
    align-items: start;
}

/* The head already separates the group; its first row must not draw a second
   rule. `.is-first-visible` is set by the filter script when the real first
   row is filtered out. */
.proj-list .proj-group .proj-item:first-of-type,
.proj-list .proj-item.is-first-visible {
    border-top: 0;
}

.proj-list .proj-rail {
    display: grid;
    gap: 0.4rem;
    align-content: start;
    padding-top: 0.15rem;
}

.proj-list .proj-kind {
    font-family: var(--font-mono);
    font-size: 10px;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--c-muted);
    line-height: 1.5;
}

.proj-list .proj-status {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-family: var(--font-mono);
    font-size: 10px;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--c-muted);
}

.proj-list .proj-status::before {
    content: '';
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: var(--c-muted);
    flex-shrink: 0;
}

.proj-list .proj-status[data-state='live']::before {
    background: #4ade80;
}

/* Sized to the row: its bottom edge lands with the stack chips. */
.proj-list .proj-shot {
    height: 11.5rem;
    border-radius: var(--r-card-sm);
    overflow: hidden;
    background: var(--c-surface-sunken);
    border: var(--border-w) solid var(--c-primary-fade);
    transition: border-color var(--t-fast);
}

.proj-list .proj-item:hover .proj-shot {
    border-color: var(--c-primary);
}

.proj-list .proj-shot img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.proj-list .proj-body {
    display: grid;
    gap: 0.7rem;
    justify-items: start;
}

/* No gold accent — project names are proper nouns; the gold in this row
   belongs to the year and the chips. */
.proj-list .proj-name {
    font-family: var(--font-display);
    font-size: 1.4rem;
    font-weight: var(--fw-bold);
    letter-spacing: -0.02em;
    line-height: 1.1;
}

.proj-list .proj-desc {
    font-size: 0.92rem;
    font-weight: var(--fw-light);
    line-height: 1.6;
    color: var(--c-muted);
    max-width: 52ch;
    text-wrap: pretty;
}

.proj-list .proj-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 0.45rem;
    align-items: center;
}

.proj-list .proj-chip {
    font-size: 11px;
    font-weight: var(--fw-semibold);
    padding: 0.2rem 0.65rem;
    border-radius: var(--r-pill);
    border: 1px solid color-mix(in srgb, var(--bc, var(--c-primary)) 55%, transparent);
    color: var(--bc, var(--c-primary));
}

@media (max-width: 1100px) {
    .proj-list .proj-item {
        grid-template-columns: 5rem 13rem 1fr;
        gap: 1.5rem;
    }

    .proj-list .proj-shot {
        height: 9rem;
    }

    .proj-list .proj-yhead h2 {
        font-size: 2.1rem;
    }

    .proj-list .proj-desc {
        max-width: none;
    }
}

@media (max-width: 760px) {
    .proj-list .proj-item {
        grid-template-columns: 1fr;
        gap: 1rem;
        padding: 1.6rem 0;
    }

    .proj-list .proj-rail {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        padding-top: 0;
    }

    .proj-list .proj-shot {
        height: 12rem;
    }

    .proj-list .proj-yhead {
        padding: 0.9rem 0 0.7rem;
    }

    .proj-list .proj-yhead h2 {
        font-size: 1.9rem;
    }
}
```

`top: 0` on the year head is correct for this task; Task 5 moves it to `3.5rem` once the filter bar exists to sit above it.

- [ ] **Step 9: Load the stylesheet**

Replace `resources/css/pages/projects.css` in full:

```css
@import '../components/project-list.css';
@import '../components/dock-hero.css';

/* ================================================================
   PROJECTS PAGE STYLES
   ================================================================ */
```

Then delete the dead file:

```bash
git rm resources/css/components/project-row.css
```

- [ ] **Step 10: Rebuild the page view**

Replace the `<section>` in `resources/views/projects.blade.php` (keep the layout tag, the `@php $locale` line, and the whole `<x-portfolio.dock-hero>` block exactly as they are):

```blade
    {{-- No fade-up: the hero leaves this section's top edge inside the first
         viewport, so it must already be there when the page paints. --}}
    <section id="projects" class="portfolio-section portfolio-section--no-reveal">
        <div class="proj-list">
            @forelse ($projects as $year => $yearProjects)
                <section class="proj-group">
                    <x-portfolio.project-year-head :year="$year" :count="$yearProjects->count()" />

                    @foreach ($yearProjects as $project)
                        <x-portfolio.project-row :project="$project" :locale="$locale" />
                    @endforeach
                </section>
            @empty
                <p class="proj-none">{{ __('pages/projects.empty_list') }}</p>
            @endforelse
        </div>
    </section>
```

Add the one rule the empty paragraph needs to the end of `project-list.css`:

```css
.proj-list .proj-none {
    padding: 4rem 0;
    text-align: center;
    color: var(--c-muted);
}
```

- [ ] **Step 11: Run the tests**

```bash
docker exec portfolio-app-1 php artisan test --compact --filter=ProjectsPageTest
```

Expected: PASS, all eleven tests.

- [ ] **Step 12: Run the public-page suites for regressions**

```bash
docker exec portfolio-app-1 php artisan test --compact --filter="DockHeroPagesTest|PublicPagesTest|HomePageTest"
```

Expected: PASS.

- [ ] **Step 13: Build and eyeball**

On the **host**:

```bash
npm run build
```

Open http://localhost:8008/projects. Check: one year head per year, the year stroked gold and left-aligned (not the centred watermark — if it is centred, the `.proj-list .proj-yhead h2` double-class did not land), no year inside a row, the shot's bottom edge level with the chips, and the group's first row without a top rule.

- [ ] **Step 14: Format and commit**

```bash
docker exec portfolio-app-1 vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat(projects): group the list under one year head per year"
```

---

### Task 4: Details panel

**Files:**
- Modify: `resources/views/components/portfolio/project-row.blade.php`
- Modify: `resources/css/components/project-list.css`
- Modify: `resources/views/projects.blade.php` (toggle script)
- Modify: `resources/lang/en/pages/projects.php`, `resources/lang/cs/pages/projects.php`
- Test: `tests/Feature/ProjectsPageTest.php` (append)

**Interfaces:**
- Consumes: the row markup from Task 3.
- Produces: `.proj-act > button.proj-toggle[aria-expanded][aria-controls]` in column 4 of the row, `.proj-more#proj-more-{slug}` inside `.proj-body`, and the class `is-open` on `.proj-item`. Task 5's script must not clear `is-open` when filtering.

- [ ] **Step 1: Write the failing test**

Append to `tests/Feature/ProjectsPageTest.php`:

```php
test('each row carries a details toggle wired to its own panel', function () {
    Project::factory()->create(['slug' => 'portfolio']);

    $html = $this->get(route('projects'))->getContent();

    expect($html)->toContain('aria-controls="proj-more-portfolio"')
        ->toContain('aria-expanded="false"')
        ->toContain('id="proj-more-portfolio"');
});

test('the details panel lists role, client and stack', function () {
    $badge = Badge::factory()->create(['name' => ['en' => 'Laravel'], 'slug' => 'laravel']);
    $project = Project::factory()->client()->create([
        'client' => 'PekneWeby',
        'role' => ['en' => 'Front-end and back-end'],
    ]);
    $project->badges()->attach($badge);

    $html = $this->get(route('projects'))->getContent();

    expect($html)->toContain('Front-end and back-end')
        ->toContain('PekneWeby')
        ->toContain('Laravel');
});

test('a link renders as a labelled pill named by its kind', function () {
    $project = Project::factory()->create();
    Link::factory()->create(['project_id' => $project->id, 'url' => 'https://github.com/a/b', 'kind' => 'repo']);

    $html = $this->get(route('projects'))->getContent();

    expect($html)->toContain('https://github.com/a/b')->toContain('Source');
});

test('a project with no links says so instead of hiding the link row', function () {
    Project::factory()->create();

    $html = $this->get(route('projects'))->getContent();

    expect($html)->toContain('proj-link--none')->toContain('No public link');
});
```

- [ ] **Step 2: Run it to verify it fails**

```bash
docker exec portfolio-app-1 php artisan test --compact --filter=ProjectsPageTest
```

Expected: FAIL — no `aria-controls` in the markup.

- [ ] **Step 3: Add the copy**

`resources/lang/en/pages/projects.php`:

```php
    'details' => 'Details',
    'close' => 'Close',
    'fact_role' => 'Role',
    'fact_client' => 'Client',
    'fact_kind' => 'Kind',
    'fact_stack' => 'Stack',
    'link_live' => 'Live site',
    'link_repo' => 'Source',
    'link_article' => 'Write-up',
    'link_none' => 'No public link',
```

`resources/lang/cs/pages/projects.php`:

```php
    'details' => 'Detail',
    'close' => 'Zavřít',
    'fact_role' => 'Role',
    'fact_client' => 'Klient',
    'fact_kind' => 'Druh',
    'fact_stack' => 'Technologie',
    'link_live' => 'Živý web',
    'link_repo' => 'Zdrojový kód',
    'link_article' => 'Článek',
    'link_none' => 'Bez veřejného odkazu',
```

- [ ] **Step 4: Add the panel to the row**

In `resources/views/components/portfolio/project-row.blade.php`, inside `.proj-body` after the chips block:

```blade
        <div class="proj-more" id="proj-more-{{ $project->slug }}">
            <dl class="proj-facts">
                @if ($project->getTranslation('role', $locale))
                    <div class="proj-fact">
                        <dt>{{ __('pages/projects.fact_role') }}</dt>
                        <dd>{{ $project->getTranslation('role', $locale) }}</dd>
                    </div>
                @endif

                <div class="proj-fact">
                    @if ($project->kind === 'client' && $project->client)
                        <dt>{{ __('pages/projects.fact_client') }}</dt>
                        <dd>{{ $project->client }}</dd>
                    @else
                        <dt>{{ __('pages/projects.fact_kind') }}</dt>
                        <dd>{{ $kindLabel }}</dd>
                    @endif
                </div>

                @if ($project->badges->isNotEmpty())
                    <div class="proj-fact">
                        <dt>{{ __('pages/projects.fact_stack') }}</dt>
                        <dd>{{ $project->badges->map(fn ($b) => $b->getTranslation('name', $locale))->implode(', ') }}</dd>
                    </div>
                @endif
            </dl>

            <div class="proj-links">
                @forelse ($project->links as $link)
                    <a class="proj-link" href="{{ $link->url }}" target="_blank" rel="noopener noreferrer">
                        {{ __('pages/projects.link_'.$link->kind) }}
                    </a>
                @empty
                    <span class="proj-link proj-link--none">{{ __('pages/projects.link_none') }}</span>
                @endforelse
            </div>
        </div>
```

and after the closing `</div>` of `.proj-body`, still inside `<article>`:

```blade
    <div class="proj-act">
        <button
            type="button"
            class="proj-toggle"
            aria-expanded="false"
            aria-controls="proj-more-{{ $project->slug }}"
            data-label-open="{{ __('pages/projects.details') }}"
            data-label-close="{{ __('pages/projects.close') }}"
        >{{ __('pages/projects.details') }}</button>
    </div>
```

- [ ] **Step 5: Style the panel**

Append to `resources/css/components/project-list.css`, before the `@media` blocks:

```css
/* ── expand in place ── */
.proj-list .proj-act {
    justify-self: end;
    padding-top: 0.35rem;
}

.proj-list .proj-toggle {
    font-family: var(--font-mono);
    font-size: 10px;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--c-muted);
    background: none;
    border: 0;
    border-bottom: 1px solid var(--c-primary-fade);
    padding: 0 0 2px;
    cursor: pointer;
    transition: color var(--t-fast), border-color var(--t-fast);
}

.proj-list .proj-toggle:hover {
    color: var(--c-primary);
    border-color: var(--c-primary);
}

.proj-list .proj-more {
    display: none;
    gap: 1rem;
    padding-top: 0.4rem;
}

.proj-list .proj-item.is-open .proj-more {
    display: grid;
}

.proj-list .proj-item.is-open .proj-toggle {
    color: var(--c-primary);
    border-color: var(--c-primary);
}

.proj-list .proj-facts {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem 2rem;
    margin: 0;
}

.proj-list .proj-fact {
    display: grid;
    gap: 0.15rem;
}

.proj-list .proj-fact dt {
    font-family: var(--font-mono);
    font-size: 9.5px;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: color-mix(in srgb, var(--c-muted) 75%, transparent);
}

.proj-list .proj-fact dd {
    margin: 0;
    font-size: 0.88rem;
    font-weight: var(--fw-light);
    color: var(--c-fg);
}

.proj-list .proj-links {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.proj-list .proj-link {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 11px;
    font-weight: var(--fw-semibold);
    letter-spacing: 0.08em;
    text-transform: uppercase;
    padding: 0.35rem 0.8rem;
    border-radius: var(--r-pill);
    border: 1px solid color-mix(in srgb, var(--c-primary-fade) 80%, transparent);
    color: var(--c-fg);
    transition: color var(--t-fast), border-color var(--t-fast);
}

.proj-list .proj-link:hover {
    border-color: var(--c-primary);
    color: var(--c-primary);
}

.proj-list .proj-link--none {
    border-style: dashed;
    color: var(--c-muted);
    pointer-events: none;
}
```

and add to **both** existing `@media` blocks — inside `@media (max-width: 1100px)`:

```css
    .proj-list .proj-act {
        grid-column: 3;
        justify-self: start;
        padding-top: 0;
    }
```

inside `@media (max-width: 760px)`:

```css
    .proj-list .proj-act {
        grid-column: 1;
    }
```

- [ ] **Step 6: Wire the toggle**

Add a `<script>` block to `resources/views/projects.blade.php`, after the closing `</section>` and before `</x-portfolio-layout>`:

```blade
    <script>
    (function () {
        const list = document.querySelector('.proj-list');
        if (!list) { return; }

        list.addEventListener('click', function (e) {
            const btn = e.target.closest('.proj-toggle');
            if (!btn) { return; }

            const row = btn.closest('.proj-item');
            const open = btn.getAttribute('aria-expanded') !== 'true';

            row.classList.toggle('is-open', open);
            btn.setAttribute('aria-expanded', String(open));
            btn.textContent = open ? btn.dataset.labelClose : btn.dataset.labelOpen;
        });
    })();
    </script>
```

Rows are independent: opening one never closes another, and nothing moves focus.

- [ ] **Step 7: Run the tests**

```bash
docker exec portfolio-app-1 php artisan test --compact --filter=ProjectsPageTest
```

Expected: PASS, all fifteen tests.

- [ ] **Step 8: Build and eyeball**

On the **host**: `npm run build`. On http://localhost:8008/projects click `Details` on two rows — both stay open, the label flips to `Close`, the facts and link pills appear, and the row below does not jump.

- [ ] **Step 9: Commit**

```bash
git add -A
git commit -m "feat(projects): expand row details in place"
```

---

### Task 5: Filter bar

**Files:**
- Modify: `resources/views/projects.blade.php`
- Modify: `resources/css/components/project-list.css`
- Modify: `resources/lang/en/pages/projects.php`, `resources/lang/cs/pages/projects.php`
- Test: `tests/Feature/ProjectsPageTest.php` (append)

**Interfaces:**
- Consumes: `$stackBadges` from Task 3; the row `data-kind` / `data-stack` attributes; `.proj-yhead` + `.proj-ycount` and its three `data-*` plural strings; `is-open` from Task 4.
- Produces: `.proj-filters` above the list, `.proj-empty` after it. Nothing later depends on this.

- [ ] **Step 1: Write the failing test**

Append to `tests/Feature/ProjectsPageTest.php`:

```php
test('the filter bar offers one kind button per kind plus all', function () {
    Project::factory()->create();

    $html = $this->get(route('projects'))->getContent();

    expect($html)->toContain('data-kind-filter="all"')
        ->toContain('data-kind-filter="personal"')
        ->toContain('data-kind-filter="client"')
        ->toContain('data-kind-filter="school"');
});

test('the filter bar offers a stack chip per attached badge only', function () {
    $attached = Badge::factory()->create(['slug' => 'laravel', 'name' => ['en' => 'Laravel']]);
    Badge::factory()->create(['slug' => 'svelte', 'name' => ['en' => 'Svelte']]);
    Project::factory()->create()->badges()->attach($attached);

    $html = $this->get(route('projects'))->getContent();

    expect($html)->toContain('data-stack-filter="laravel"')
        ->not->toContain('data-stack-filter="svelte"');
});

test('the count starts at every project shown', function () {
    Project::factory()->count(3)->create();

    $html = $this->get(route('projects'))->getContent();

    expect($html)->toContain('<b>3</b>')->toContain('3 total');
});

test('the empty state ships hidden with a clear-filters action', function () {
    Project::factory()->create();

    $html = $this->get(route('projects'))->getContent();

    expect($html)->toContain('class="proj-empty"')
        ->toContain('hidden')
        ->toContain('Clear filters');
});
```

- [ ] **Step 2: Run it to verify it fails**

```bash
docker exec portfolio-app-1 php artisan test --compact --filter=ProjectsPageTest
```

Expected: FAIL — no `data-kind-filter` in the markup.

- [ ] **Step 3: Add the copy**

`resources/lang/en/pages/projects.php`:

```php
    'filter_kind' => 'Kind',
    'filter_stack' => 'Stack',
    'kind_all' => 'All',
    'count_one' => ':count shown · :total total',
    'count_few' => ':count shown · :total total',
    'count_many' => ':count shown · :total total',
    'clear_filters' => 'Clear filters',
    'empty_title' => 'Nothing matches',
    'empty_body' => 'No project matches :filters.',
```

`resources/lang/cs/pages/projects.php`:

```php
    'filter_kind' => 'Druh',
    'filter_stack' => 'Technologie',
    'kind_all' => 'Vše',
    'count_one' => 'zobrazen :count z :total',
    'count_few' => 'zobrazeny :count z :total',
    'count_many' => 'zobrazeno :count z :total',
    'clear_filters' => 'Zrušit filtry',
    'empty_title' => 'Nic neodpovídá',
    'empty_body' => 'Filtru :filters neodpovídá žádný projekt.',
```

- [ ] **Step 4: Add the bar and the empty state**

In `resources/views/projects.blade.php`, inside the `<section id="projects">`, directly **above** `<div class="proj-list">`:

```blade
        <div class="proj-filters" id="proj-filters">
            <div class="proj-fgroup" role="group" aria-labelledby="proj-flabel-kind">
                <span class="proj-flabel" id="proj-flabel-kind">{{ __('pages/projects.filter_kind') }}</span>
                <div class="proj-seg" id="proj-seg">
                    <span class="proj-seg-thumb" id="proj-seg-thumb" aria-hidden="true"></span>
                    <button type="button" data-kind-filter="all" aria-pressed="true">{{ __('pages/projects.kind_all') }}</button>
                    <button type="button" data-kind-filter="personal" aria-pressed="false">{{ __('pages/projects.kind_personal') }}</button>
                    <button type="button" data-kind-filter="client" aria-pressed="false">{{ __('pages/projects.kind_client') }}</button>
                    <button type="button" data-kind-filter="school" aria-pressed="false">{{ __('pages/projects.kind_school') }}</button>
                </div>
            </div>

            @if ($stackBadges->isNotEmpty())
                <div class="proj-fgroup" role="group" aria-labelledby="proj-flabel-stack" id="proj-stack">
                    <span class="proj-flabel" id="proj-flabel-stack">{{ __('pages/projects.filter_stack') }}</span>
                    @foreach ($stackBadges as $badge)
                        <button
                            type="button"
                            class="proj-fchip"
                            aria-pressed="false"
                            data-stack-filter="{{ $badge->slug }}"
                            style="--bc: {{ $badge->color }}"
                        >{{ $badge->getTranslation('name', $locale) }}</button>
                    @endforeach
                </div>
            @endif

            <button type="button" class="proj-fclear" id="proj-clear" hidden>{{ __('pages/projects.clear_filters') }}</button>

            <div
                class="proj-fcount"
                id="proj-count"
                aria-live="polite"
                data-one="{{ __('pages/projects.count_one') }}"
                data-few="{{ __('pages/projects.count_few') }}"
                data-many="{{ __('pages/projects.count_many') }}"
            >{!! __('pages/projects.count_many', ['count' => '<b>'.$projects->flatten(1)->count().'</b>', 'total' => $projects->flatten(1)->count()]) !!}</div>
        </div>
```

and directly **below** the closing `</div>` of `.proj-list`:

```blade
        <div class="proj-empty" id="proj-empty" hidden>
            <h3 class="proj-etitle">{{ __('pages/projects.empty_title') }}</h3>
            <p class="proj-ebody" id="proj-empty-body" data-template="{{ __('pages/projects.empty_body') }}"></p>
            <button type="button" class="proj-fclear" id="proj-empty-clear">{{ __('pages/projects.clear_filters') }}</button>
        </div>
```

- [ ] **Step 5: Style the bar**

Append to `resources/css/components/project-list.css`, before the `@media` blocks:

```css
/* ── filter bar ── */
.proj-filters {
    position: sticky;
    top: 0;
    z-index: 5;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.55rem 1.6rem;
    padding: 1rem 0;
    background: var(--c-bg);
    border-top: 1px solid color-mix(in srgb, var(--c-primary-fade) 55%, transparent);
    border-bottom: 1px solid color-mix(in srgb, var(--c-primary-fade) 55%, transparent);
}

/* Rows dissolve under the bar rather than colliding with it. */
.proj-filters::after {
    content: '';
    position: absolute;
    left: 0;
    right: 0;
    top: 100%;
    height: 1.25rem;
    background: linear-gradient(var(--c-bg), transparent);
    pointer-events: none;
}

.proj-filters .proj-fgroup {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    flex-wrap: wrap;
}

.proj-filters .proj-flabel {
    font-family: var(--font-mono);
    font-size: 10px;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: color-mix(in srgb, var(--c-muted) 80%, transparent);
    margin-right: 0.15rem;
}

/* Single-select gets one object, not four buttons that can all read as off. */
.proj-filters .proj-seg {
    position: relative;
    display: inline-flex;
    gap: 0.1rem;
    padding: 0.2rem;
    border-radius: var(--r-pill);
    background: var(--c-surface-sunken);
    border: 1px solid color-mix(in srgb, var(--c-primary-fade) 60%, transparent);
}

.proj-filters .proj-seg-thumb {
    position: absolute;
    top: 0.2rem;
    bottom: 0.2rem;
    left: 0.2rem;
    width: 0;
    border-radius: var(--r-pill);
    background: var(--c-primary);
    transition: transform var(--t-fast), width var(--t-fast);
}

.proj-filters .proj-seg button {
    position: relative;
    font-size: 11.5px;
    font-weight: var(--fw-semibold);
    letter-spacing: 0.06em;
    text-transform: uppercase;
    padding: 0.34rem 0.95rem;
    border: 0;
    border-radius: var(--r-pill);
    background: transparent;
    color: var(--c-muted);
    cursor: pointer;
    transition: color var(--t-fast);
}

.proj-filters .proj-seg button:hover {
    color: var(--c-fg);
}

.proj-filters .proj-seg button[aria-pressed='true'] {
    color: var(--c-bg);
}

.proj-filters .proj-fchip {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    font-size: 11.5px;
    font-weight: var(--fw-semibold);
    padding: 0.28rem 0.75rem 0.28rem 0.6rem;
    border-radius: var(--r-pill);
    border: 1px solid color-mix(in srgb, var(--c-primary-fade) 75%, transparent);
    background: none;
    color: var(--c-muted);
    cursor: pointer;
    transition: color var(--t-fast), border-color var(--t-fast), background var(--t-fast);
}

.proj-filters .proj-fchip::before {
    content: '';
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--bc, var(--c-primary));
    transition: box-shadow var(--t-fast);
}

.proj-filters .proj-fchip:hover {
    color: var(--c-fg);
    border-color: color-mix(in srgb, var(--bc, var(--c-primary)) 55%, transparent);
}

.proj-filters .proj-fchip[aria-pressed='true'] {
    color: var(--bc, var(--c-primary));
    border-color: color-mix(in srgb, var(--bc, var(--c-primary)) 60%, transparent);
    background: color-mix(in srgb, var(--bc, var(--c-primary)) 14%, transparent);
}

.proj-filters .proj-fchip[aria-pressed='true']::before {
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--bc, var(--c-primary)) 28%, transparent);
}

.proj-filters .proj-fcount {
    margin-left: auto;
    font-family: var(--font-mono);
    font-size: 10.5px;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--c-muted);
}

.proj-filters .proj-fcount b {
    color: var(--c-fg);
    font-weight: var(--fw-semibold);
}

.proj-filters .proj-fclear,
.proj-empty .proj-fclear {
    font-family: var(--font-mono);
    font-size: 10px;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--c-muted);
    background: none;
    border: 0;
    border-bottom: 1px solid var(--c-primary-fade);
    padding: 0 0 2px;
    cursor: pointer;
}

.proj-filters .proj-fclear:hover,
.proj-empty .proj-fclear:hover {
    color: var(--c-primary);
    border-color: var(--c-primary);
}

/* ── empty state ── */
.proj-empty {
    display: grid;
    gap: 0.9rem;
    justify-items: start;
    padding: 3.5rem 0 4rem;
    border-top: 1px solid color-mix(in srgb, var(--c-primary-fade) 55%, transparent);
}

.proj-empty .proj-etitle {
    font-family: var(--font-display);
    font-size: 1.25rem;
    font-weight: var(--fw-bold);
}

.proj-empty .proj-ebody {
    font-size: 0.92rem;
    font-weight: var(--fw-light);
    color: var(--c-muted);
    max-width: 44ch;
}

/* Filtered-out rows and their emptied year groups. */
.proj-list .proj-item[hidden],
.proj-list .proj-group[hidden] {
    display: none;
}
```

Change the year head's sticky offset now that the bar exists — in the base `.proj-list .proj-yhead` rule replace `top: 0;` with `top: 3.5rem;`, and inside `@media (max-width: 760px)` add to the existing `.proj-list .proj-yhead` block:

```css
        top: 0;
```

Also add to `@media (max-width: 760px)`:

```css
    .proj-filters {
        gap: 0.5rem 0.9rem;
        padding: 0.85rem 0;
    }

    .proj-filters .proj-fcount {
        margin-left: 0;
        flex-basis: 100%;
    }

    /* A segmented control must never break onto two lines. */
    .proj-filters .proj-seg {
        max-width: 100%;
        overflow-x: auto;
        scrollbar-width: none;
    }
```

- [ ] **Step 6: Write the filter script**

In `resources/views/projects.blade.php`, replace the whole `<script>` block from Task 4 with:

```blade
    <script>
    (function () {
        const list = document.querySelector('.proj-list');
        if (!list) { return; }

        /* ── details toggle ── */
        list.addEventListener('click', function (e) {
            const btn = e.target.closest('.proj-toggle');
            if (!btn) { return; }

            const row = btn.closest('.proj-item');
            const open = btn.getAttribute('aria-expanded') !== 'true';

            row.classList.toggle('is-open', open);
            btn.setAttribute('aria-expanded', String(open));
            btn.textContent = open ? btn.dataset.labelClose : btn.dataset.labelOpen;
        });

        /* ── filters ── */
        const segEl = document.getElementById('proj-seg');
        const thumb = document.getElementById('proj-seg-thumb');
        const stackEl = document.getElementById('proj-stack');       // null when no badges
        const countEl = document.getElementById('proj-count');
        const clearBtn = document.getElementById('proj-clear');
        const emptyEl = document.getElementById('proj-empty');
        const emptyBody = document.getElementById('proj-empty-body');
        const emptyClear = document.getElementById('proj-empty-clear');

        const rows = Array.from(list.querySelectorAll('.proj-item'));
        const groups = Array.from(list.querySelectorAll('.proj-group'));
        const total = rows.length;

        let kind = 'all';
        const activeStack = new Set();

        function matches(row) {
            if (kind !== 'all' && row.dataset.kind !== kind) { return false; }

            if (activeStack.size) {
                const slugs = JSON.parse(row.dataset.stack || '[]');
                // union: a row matches if it carries ANY pressed badge
                if (!slugs.some(function (slug) { return activeStack.has(slug); })) { return false; }
            }

            return true;
        }

        function moveThumb() {
            const pressed = segEl.querySelector('button[aria-pressed="true"]');
            if (!pressed) { return; }
            thumb.style.width = pressed.offsetWidth + 'px';
            thumb.style.transform = 'translateX(' + (pressed.offsetLeft - thumb.offsetLeft) + 'px)';
        }

        function plural(n) {
            // Czech needs three forms (1 / 2-4 / 5+); English reuses one of them.
            return n === 1 ? 'one' : (n >= 2 && n <= 4 ? 'few' : 'many');
        }

        function activeLabels() {
            const labels = [];
            if (kind !== 'all') {
                labels.push(segEl.querySelector('button[aria-pressed="true"]').textContent.trim());
            }
            if (stackEl) {
                stackEl.querySelectorAll('.proj-fchip[aria-pressed="true"]').forEach(function (chip) {
                    labels.push(chip.textContent.trim());
                });
            }
            return labels;
        }

        function syncUrl() {
            const params = new URLSearchParams(window.location.search);

            if (kind === 'all') { params.delete('kind'); } else { params.set('kind', kind); }
            if (activeStack.size === 0) {
                params.delete('stack');
            } else {
                params.set('stack', Array.from(activeStack).join(','));
            }

            const query = params.toString();
            history.replaceState(null, '', query ? '?' + query : window.location.pathname);
        }

        function apply() {
            let visible = 0;

            rows.forEach(function (row) {
                const show = matches(row);
                row.hidden = !show;
                row.classList.remove('is-first-visible');
                if (show) { visible++; }
            });

            groups.forEach(function (group) {
                const shown = Array.from(group.querySelectorAll('.proj-item')).filter(function (r) { return !r.hidden; });

                group.hidden = shown.length === 0;

                if (shown.length) {
                    // The head already separates the group — whichever row now
                    // leads it must not draw a second rule.
                    shown[0].classList.add('is-first-visible');

                    const countNode = group.querySelector('.proj-ycount');
                    countNode.textContent = countNode.dataset[plural(shown.length)]
                        .replace(':count', shown.length);
                }
            });

            countEl.innerHTML = countEl.dataset[plural(visible)]
                .replace(':count', '<b>' + visible + '</b>')
                .replace(':total', total);

            const labels = activeLabels();
            clearBtn.hidden = labels.length === 0;
            emptyEl.hidden = visible !== 0;
            emptyBody.textContent = emptyBody.dataset.template.replace(':filters', labels.join(' + '));

            syncUrl();
        }

        segEl.addEventListener('click', function (e) {
            const btn = e.target.closest('button[data-kind-filter]');
            if (!btn) { return; }

            segEl.querySelectorAll('button[data-kind-filter]').forEach(function (b) {
                b.setAttribute('aria-pressed', String(b === btn));
            });
            kind = btn.dataset.kindFilter;
            moveThumb();
            apply();
        });

        if (stackEl) {
            stackEl.addEventListener('click', function (e) {
                const btn = e.target.closest('.proj-fchip');
                if (!btn) { return; }

                const on = btn.getAttribute('aria-pressed') !== 'true';
                btn.setAttribute('aria-pressed', String(on));
                if (on) { activeStack.add(btn.dataset.stackFilter); } else { activeStack.delete(btn.dataset.stackFilter); }
                apply();
            });
        }

        function clearFilters() {
            kind = 'all';
            activeStack.clear();
            segEl.querySelectorAll('button[data-kind-filter]').forEach(function (b) {
                b.setAttribute('aria-pressed', String(b.dataset.kindFilter === 'all'));
            });
            if (stackEl) {
                stackEl.querySelectorAll('.proj-fchip').forEach(function (c) { c.setAttribute('aria-pressed', 'false'); });
            }
            moveThumb();
            apply();
        }

        clearBtn.addEventListener('click', clearFilters);
        emptyClear.addEventListener('click', clearFilters);

        // Restore state from ?kind=&stack= so a filtered list can be shared and
        // the back button behaves.
        (function readUrl() {
            const params = new URLSearchParams(window.location.search);
            const urlKind = params.get('kind');
            const urlStack = (params.get('stack') || '').split(',').filter(Boolean);

            const kindBtn = urlKind && segEl.querySelector('button[data-kind-filter="' + CSS.escape(urlKind) + '"]');
            if (kindBtn) {
                segEl.querySelectorAll('button[data-kind-filter]').forEach(function (b) {
                    b.setAttribute('aria-pressed', String(b === kindBtn));
                });
                kind = urlKind;
            }

            if (stackEl) {
                urlStack.forEach(function (slug) {
                    const chip = stackEl.querySelector('.proj-fchip[data-stack-filter="' + CSS.escape(slug) + '"]');
                    if (chip) {
                        chip.setAttribute('aria-pressed', 'true');
                        activeStack.add(slug);
                    }
                });
            }
        })();

        let resizeTimer;
        window.addEventListener('resize', function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(moveThumb, 150);
        });

        moveThumb();
        apply();

        // Label widths change once the webfont swaps in.
        if (document.fonts) { document.fonts.ready.then(moveThumb); }
    })();
    </script>
```

- [ ] **Step 7: Run the tests**

```bash
docker exec portfolio-app-1 php artisan test --compact --filter=ProjectsPageTest
```

Expected: PASS, all nineteen tests.

- [ ] **Step 8: Build and eyeball**

On the **host**: `npm run build`. On http://localhost:8008/projects:
- The gold thumb sits under `All` and slides to `Client` on click.
- Picking `Client` leaves one row and hides the 2022 and 2026 year heads entirely.
- The URL becomes `?kind=client`; reloading keeps the filter and the thumb position.
- The count reads `1 shown · 3 total`; `Clear filters` appears.
- Two stack chips together show the union, not the intersection.
- Filtering to nothing shows the empty state naming the filters.

- [ ] **Step 9: Commit**

```bash
git add -A
git commit -m "feat(projects): filter the list by kind and stack"
```

---

### Task 6: Responsive and browser verification

**Files:**
- Create: `tests/Browser/ProjectsListTest.php`
- Modify: `resources/css/components/project-list.css` (only if a test fails)

**Interfaces:**
- Consumes: everything from Tasks 1–5.
- Produces: nothing.

- [ ] **Step 1: Write the browser test**

Create `tests/Browser/ProjectsListTest.php`:

```php
<?php

use App\Models\Badge;
use App\Models\Project;

/**
 * Browser tests run inside the container against the seeded settings the dock
 * hero needs; the first visit compiles the page, so keep the timeout generous.
 */
beforeEach(function () {
    $this->seed(\Database\Seeders\SettingSeeder::class);

    $laravel = Badge::factory()->create(['slug' => 'laravel', 'name' => ['en' => 'Laravel'], 'color' => '#FF2D20']);
    $php = Badge::factory()->create(['slug' => 'php', 'name' => ['en' => 'PHP'], 'color' => '#777BB4']);

    Project::factory()->create(['year' => 2026, 'slug' => 'portfolio', 'header' => ['en' => 'Portfolio'], 'kind' => 'personal'])
        ->badges()->attach($laravel);
    Project::factory()->client()->create(['year' => 2025, 'slug' => 'u-sladovny', 'header' => ['en' => 'U Sladovny'], 'client' => 'PekneWeby'])
        ->badges()->attach($php);
    Project::factory()->create(['year' => 2022, 'slug' => 'spse-hub', 'header' => ['en' => 'SPSE Hub'], 'kind' => 'school']);
});

test('the kind segment filters the list and hides emptied year groups', function () {
    $page = visit('/projects')->resize(1440, 900);

    $page->click('[data-kind-filter="client"]');

    $visibleRows = <<<'JS'
        document.querySelectorAll('.proj-item:not([hidden])').length
    JS;
    $visibleGroups = <<<'JS'
        document.querySelectorAll('.proj-group:not([hidden])').length
    JS;

    expect($page->script($visibleRows))->toBe(1)
        ->and($page->script($visibleGroups))->toBe(1);
});

test('two stack chips union rather than intersect', function () {
    $page = visit('/projects')->resize(1440, 900);

    $page->click('[data-stack-filter="laravel"]');
    $page->click('[data-stack-filter="php"]');

    expect($page->script('document.querySelectorAll(".proj-item:not([hidden])").length'))->toBe(2);
});

test('the segment thumb moves to the pressed button', function () {
    $page = visit('/projects')->resize(1440, 900);

    $before = $page->script('document.getElementById("proj-seg-thumb").getBoundingClientRect().left');

    $page->click('[data-kind-filter="school"]');

    $after = $page->script('document.getElementById("proj-seg-thumb").getBoundingClientRect().left');

    expect($after)->toBeGreaterThan($before);
});

test('filtering to nothing shows the empty state', function () {
    $page = visit('/projects')->resize(1440, 900);

    $page->click('[data-kind-filter="client"]');
    $page->click('[data-stack-filter="laravel"]');

    expect($page->script('document.getElementById("proj-empty").hidden'))->toBeFalse();
});

test('two rows open at once', function () {
    $page = visit('/projects')->resize(1440, 900);

    $page->click('.proj-group:nth-of-type(1) .proj-item:nth-of-type(1) .proj-toggle');
    $page->click('.proj-group:nth-of-type(2) .proj-item:nth-of-type(1) .proj-toggle');

    expect($page->script('document.querySelectorAll(".proj-item.is-open").length'))->toBe(2);
});

test('the projects list never scrolls sideways', function (int $width) {
    $page = visit('/projects')->resize($width, 900);

    $overflow = <<<'JS'
        Math.round(document.documentElement.scrollWidth - document.documentElement.clientWidth)
    JS;

    expect($page->script($overflow))->toBeLessThanOrEqual(0);
})->with([1440, 1100, 760, 390]);
```

- [ ] **Step 2: Run the browser tests**

```bash
docker exec portfolio-app-1 php artisan test --compact --filter=ProjectsListTest
```

Expected: PASS. If the first test times out, the page is still compiling — run it a second time before treating it as a failure.

- [ ] **Step 3: Fix whatever the widths caught**

If the sideways-scroll test fails at a width, the usual causes in order: the `.proj-seg` overflowing at 390 (its `overflow-x: auto` rule from Task 5 is missing or was written without the ancestor class), `52ch` on `.proj-desc` not being released at 1100, or the 2rem grid gap surviving into the single-column layout at 760. Fix in `project-list.css` and re-run this task's tests. Do not widen the page column or add `overflow: hidden` to an ancestor — that would break the sticky filter bar and the sticky year heads.

- [ ] **Step 4: Check the light theme**

On the **host**: `npm run build`. Open http://localhost:8008/projects, switch the theme toggle to light, and confirm the stroked year head, the seg thumb's pressed label, the chip hue fills, and the shot border are all legible. `--c-bg` on the sticky bar and heads must paint opaque in both themes — a translucent head lets rows scroll through it.

- [ ] **Step 5: Run the full suite**

```bash
docker exec portfolio-app-1 php artisan test --compact
```

Expected: PASS. `ProjectsManagementTest`, `LinksManagementTest`, `DatabaseSeederTest`, `ProjectsPageTest`, `LightThemeTest`, and `PublicPagesTest` all touch what this plan changed.

- [ ] **Step 6: Commit**

```bash
docker exec portfolio-app-1 vendor/bin/pint --dirty --format agent
git add -A
git commit -m "test(projects): verify the filters, the expand and four widths"
```

---

## Verification

The feature is done when:

1. `docker exec portfolio-app-1 php artisan test --compact` passes in full.
2. http://localhost:8008/projects shows three rows under three year heads, each year printed once, each row led by its project name.
3. Filtering by `Client` leaves one row, hides the other two year heads, updates the count, and writes `?kind=client` into the URL.
4. Clearing the filters restores all three rows and empties the query string.
5. Nothing scrolls sideways at 1440 / 1100 / 760 / 390, in both themes.

## Out of Scope

- The `.sechead` page head — the dock hero owns the page's head and its only ghost wordmark.
- Lazy loading, skeleton rows, the sentinel observer, the polite live region for arrivals, and any server-side filter endpoint.
- Handoff open decisions 2–4: whether Stack filters on curated chips or a repo's full tech list; where hackathon write-up links point; folding thin years into a combined `2022 · 2019` divider.
