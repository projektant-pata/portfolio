# i18n Manage Pages Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make `year` in Experience i18n, and build full CRUD manage pages with language tabs for Articles, Projects, Badges, and Links.

**Architecture:** All translatable fields are stored as PostgreSQL JSON columns, cast to `array` in models with the same `getTranslation(field, locale)` helper pattern established in `Experience`. Each entity gets a Volt SFC manage page (⚡blade file) with Alpine.js language tabs matching the experience form pattern. Routes and sidebar links wired up for all entities.

**Tech Stack:** Laravel 13, Livewire 4 (Volt SFC), Flux UI v2, Alpine.js, PostgreSQL 17, Pest 4

---

## File Map

**Migrations (new):**
- `database/migrations/..._convert_experience_year_to_json.php`
- `database/migrations/..._convert_articles_columns_to_json.php`
- `database/migrations/..._convert_projects_columns_to_json.php`
- `database/migrations/..._convert_badges_name_to_json.php`
- `database/migrations/..._convert_links_alt_to_json.php`

**Models (new):**
- `app/Models/Article.php`
- `app/Models/Project.php`
- `app/Models/Link.php`

**Models (modified):**
- `app/Models/Experience.php` — add `year` to `$casts` as `array`
- `app/Models/Badge.php` — add `name` to `$casts` as `array`

**Factories (new):**
- `database/factories/ArticleFactory.php`
- `database/factories/ProjectFactory.php`
- `database/factories/LinkFactory.php`

**Factories (modified):**
- `database/factories/ExperienceFactory.php` — `year` becomes `['en' => '...']`

**Manage pages (new):**
- `resources/views/pages/manage/⚡articles.blade.php`
- `resources/views/pages/manage/⚡projects.blade.php`
- `resources/views/pages/manage/⚡badges.blade.php`
- `resources/views/pages/manage/⚡links.blade.php`

**Manage pages (modified):**
- `resources/views/pages/manage/⚡experiences.blade.php` — `year` becomes `array`, moved into language tabs

**Infrastructure (modified):**
- `routes/web.php` — add routes for articles, projects, badges, links
- `resources/views/layouts/app/sidebar.blade.php` — replace `href="#"` with real routes

**Tests:**
- `tests/Feature/ExperienceManagementTest.php` — update `year` references from string to array
- `tests/Feature/ArticlesManagementTest.php`
- `tests/Feature/ProjectsManagementTest.php`
- `tests/Feature/BadgesManagementTest.php`
- `tests/Feature/LinksManagementTest.php`

---

## Task 1: Experience — `year` → i18n

**Files:**
- Create: `database/migrations/2026_04_07_000001_convert_experience_year_to_json.php`
- Modify: `app/Models/Experience.php`
- Modify: `database/factories/ExperienceFactory.php`
- Modify: `resources/views/pages/manage/⚡experiences.blade.php`
- Modify: `tests/Feature/ExperienceManagementTest.php`

- [ ] **Step 1: Write a failing test for year as array**

```php
// Add to tests/Feature/ExperienceManagementTest.php
test('experience year is stored as json with locale keys', function () {
    $experience = Experience::factory()->create([
        'year' => ['en' => '2022 – present', 'cs' => '2022 – nyní'],
    ]);

    expect($experience->fresh()->year)->toBe(['en' => '2022 – present', 'cs' => '2022 – nyní']);
});

test('can create experience with i18n year', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::manage.experiences')
        ->set('type', 'work')
        ->set('title', ['en' => 'Developer', 'cs' => ''])
        ->set('year', ['en' => '2024 – present', 'cs' => '2024 – nyní'])
        ->call('save')
        ->assertHasNoErrors();

    expect(Experience::first()->year)->toBe(['en' => '2024 – present', 'cs' => '2024 – nyní']);
});
```

- [ ] **Step 2: Run tests to confirm they fail**

```bash
docker exec portfolio-2-app-1 php artisan test --compact --filter="experience year is stored as json"
```

Expected: FAIL — `year` column is still a string, model does not cast it as array.

- [ ] **Step 3: Create migration**

```bash
docker exec portfolio-2-app-1 php artisan make:migration convert_experience_year_to_json --no-interaction
```

File content (fill in the generated file):
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Wrap existing string values as {"en": "value"} JSON
        DB::statement("
            UPDATE experiences
            SET year = json_build_object('en', year)::jsonb
            WHERE year IS NOT NULL
        ");

        Schema::table('experiences', function (Blueprint $table) {
            $table->json('year')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Unwrap JSON back to the EN string value
        DB::statement("
            UPDATE experiences
            SET year = year->>'en'
            WHERE year IS NOT NULL
        ");

        Schema::table('experiences', function (Blueprint $table) {
            $table->string('year', 50)->nullable()->change();
        });
    }
};
```

- [ ] **Step 4: Update `Experience` model — add `year` cast**

In `app/Models/Experience.php`, change `$casts`:
```php
protected $casts = [
    'sort_order' => 'integer',
    'is_special' => 'boolean',
    'links' => 'array',
    'title' => 'array',
    'subtitle' => 'array',
    'content' => 'array',
    'year' => 'array',
];
```

- [ ] **Step 5: Update `ExperienceFactory` — year as array**

In `database/factories/ExperienceFactory.php`, change `definition()`:
```php
'year' => ['en' => (string) $this->faker->year()],
```

Add a `withTranslatedYear()` state:
```php
public function withTranslatedYear(): static
{
    return $this->state(fn () => [
        'year' => ['en' => '2022 – present', 'cs' => '2022 – nyní'],
    ]);
}
```

- [ ] **Step 6: Update the existing test that sets `year` as a plain string**

In `tests/Feature/ExperienceManagementTest.php`, find the test `can create experience with english title`:
```php
->set('year', ['en' => '2024', 'cs' => ''])
```

- [ ] **Step 7: Update `⚡experiences.blade.php` — year state, form, save, reset**

**State declaration** (top of file, PHP section):
```php
public array $year = ['en' => '', 'cs' => ''];
```

**In `openEdit()`** — replace:
```php
$this->year = $experience->year ?? '';
```
With:
```php
$this->year = array_merge(['en' => '', 'cs' => ''], $experience->year ?? []);
```

**In `resetForm()`** — replace:
```php
$this->year = '';
```
With:
```php
$this->year = ['en' => '', 'cs' => ''];
```

**In `save()` validation** — replace:
```php
'year' => ['nullable', 'string', 'max:50'],
```
With:
```php
'year' => ['nullable', 'array'],
'year.en' => ['nullable', 'string', 'max:50'],
'year.cs' => ['nullable', 'string', 'max:50'],
```

**In `save()` data array** — replace:
```php
'year' => $validated['year'],
```
With:
```php
'year' => array_filter($validated['year'] ?? [], fn ($v) => filled($v)) ?: null,
```

**In the Blade template**, remove the Year input from the `{{-- Non-translatable fields --}}` grid (delete the entire `<flux:field>` block for Year). Then add Year input inside both language tab sections.

Inside `<div x-show="locale === 'en'" ...>` after the Content field:
```blade
<flux:field>
    <flux:label>Year</flux:label>
    <flux:input wire:model="year.en" placeholder="e.g. 2022 – present" />
    <flux:error name="year.en" />
</flux:field>
```

Inside `<div x-show="locale === 'cs'" ...>` after the Content field:
```blade
<flux:field>
    <flux:label>Rok</flux:label>
    <flux:input wire:model="year.cs" placeholder="např. 2022 – nyní" />
    <flux:error name="year.cs" />
</flux:field>
```

Also update the table cell display — Year column currently shows `$experience->year`. Change to:
```blade
<flux:table.cell>{{ $experience->year['en'] ?? '—' }}</flux:table.cell>
```

- [ ] **Step 8: Run migration**

```bash
docker exec portfolio-2-app-1 php artisan migrate --no-interaction
```

- [ ] **Step 9: Run pint**

```bash
docker exec portfolio-2-app-1 vendor/bin/pint --dirty --format agent
```

- [ ] **Step 10: Run tests**

```bash
docker exec portfolio-2-app-1 php artisan test --compact --filter="ExperienceManagement"
```

Expected: All experience tests PASS.

- [ ] **Step 11: Commit**

```bash
git add database/migrations/*convert_experience_year_to_json* \
        app/Models/Experience.php \
        database/factories/ExperienceFactory.php \
        resources/views/pages/manage/⚡experiences.blade.php \
        tests/Feature/ExperienceManagementTest.php
git commit -m "feat: convert experience year to i18n JSON field

Co-Authored-By: Claude Sonnet 4.6 <noreply@anthropic.com>"
```

---

## Task 2: Badge — `name` → i18n + manage page

**Fields in tabs:** `name`
**Fields outside tabs:** `slug`, `color`

**Files:**
- Create: `database/migrations/2026_04_07_000002_convert_badges_name_to_json.php`
- Modify: `app/Models/Badge.php`
- Create: `database/factories/BadgeFactory.php`
- Create: `resources/views/pages/manage/⚡badges.blade.php`
- Modify: `routes/web.php`
- Create: `tests/Feature/BadgesManagementTest.php`

- [ ] **Step 1: Write failing tests**

```bash
docker exec portfolio-2-app-1 php artisan make:test --pest BadgesManagementTest --no-interaction
```

File content `tests/Feature/BadgesManagementTest.php`:
```php
<?php

use App\Models\Badge;
use App\Models\User;
use Livewire\Livewire;

test('badge name is stored as json with locale keys', function () {
    $badge = Badge::create([
        'slug' => 'laravel',
        'name' => ['en' => 'Laravel', 'cs' => 'Laravel'],
        'color' => 'red',
    ]);

    expect($badge->fresh()->name)->toBe(['en' => 'Laravel', 'cs' => 'Laravel']);
});

test('manage badges page renders for authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('manage.badges'))
        ->assertOk();
});

test('can create badge with english name', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::manage.badges')
        ->set('name', ['en' => 'Laravel', 'cs' => ''])
        ->set('slug', 'laravel')
        ->set('color', 'red')
        ->call('save')
        ->assertHasNoErrors();

    $badge = Badge::first();
    expect($badge->name)->toBe(['en' => 'Laravel'])
        ->and($badge->slug)->toBe('laravel');
});

test('create badge requires english name', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::manage.badges')
        ->set('name', ['en' => '', 'cs' => ''])
        ->set('slug', 'laravel')
        ->call('save')
        ->assertHasErrors(['name.en']);
});

test('can edit badge and update translations', function () {
    $user = User::factory()->create();
    $badge = Badge::create([
        'slug' => 'laravel',
        'name' => ['en' => 'Laravel'],
        'color' => 'red',
    ]);

    Livewire::actingAs($user)
        ->test('pages::manage.badges')
        ->call('openEdit', $badge->id)
        ->assertSet('name', ['en' => 'Laravel', 'cs' => ''])
        ->set('name', ['en' => 'Laravel', 'cs' => 'Laravel'])
        ->call('save')
        ->assertHasNoErrors();

    expect($badge->fresh()->name)->toBe(['en' => 'Laravel', 'cs' => 'Laravel']);
});

test('can delete badge', function () {
    $user = User::factory()->create();
    $badge = Badge::create([
        'slug' => 'laravel',
        'name' => ['en' => 'Laravel'],
        'color' => 'red',
    ]);

    Livewire::actingAs($user)
        ->test('pages::manage.badges')
        ->call('confirmDelete', $badge->id)
        ->call('delete')
        ->assertHasNoErrors();

    expect(Badge::count())->toBe(0);
});
```

- [ ] **Step 2: Run tests to confirm they fail**

```bash
docker exec portfolio-2-app-1 php artisan test --compact --filter="BadgesManagement"
```

Expected: FAIL.

- [ ] **Step 3: Create migration**

```bash
docker exec portfolio-2-app-1 php artisan make:migration convert_badges_name_to_json --no-interaction
```

File content:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            UPDATE badges
            SET name = json_build_object('en', name)::jsonb
            WHERE name IS NOT NULL
        ");

        Schema::table('badges', function (Blueprint $table) {
            $table->json('name')->change();
        });
    }

    public function down(): void
    {
        DB::statement("
            UPDATE badges
            SET name = name->>'en'
            WHERE name IS NOT NULL
        ");

        Schema::table('badges', function (Blueprint $table) {
            $table->string('name')->change();
        });
    }
};
```

- [ ] **Step 4: Update `Badge` model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends Model
{
    use HasUuids;

    protected $fillable = [
        'slug',
        'name',
        'color',
    ];

    protected $casts = [
        'name' => 'array',
    ];

    public function getTranslation(string $field, string $locale, string $fallback = 'en'): string
    {
        $value = $this->{$field};

        if (! is_array($value)) {
            return '';
        }

        return $value[$locale] ?? $value[$fallback] ?? '';
    }

    public function experiences(): BelongsToMany
    {
        return $this->belongsToMany(Experience::class);
    }
}
```

- [ ] **Step 5: Create `BadgeFactory`**

```bash
docker exec portfolio-2-app-1 php artisan make:factory BadgeFactory --model=Badge --no-interaction
```

File content `database/factories/BadgeFactory.php`:
```php
<?php

namespace Database\Factories;

use App\Models\Badge;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Badge>
 */
class BadgeFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->word();

        return [
            'slug' => Str::slug($name) . '-' . $this->faker->unique()->numberBetween(1, 9999),
            'name' => ['en' => ucfirst($name)],
            'color' => $this->faker->randomElement(['red', 'blue', 'green', 'yellow', 'purple', 'zinc']),
        ];
    }

    public function translated(): static
    {
        return $this->state(fn () => [
            'name' => ['en' => ucfirst($this->faker->word()), 'cs' => ucfirst($this->faker->word())],
        ]);
    }
}
```

Also add `HasFactory` to `Badge` model:
```php
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Badge extends Model
{
    use HasFactory, HasUuids;
    // ...
}
```

- [ ] **Step 6: Create `⚡badges.blade.php` manage page**

```bash
# Create the file (use Write tool or editor)
# Path: resources/views/pages/manage/⚡badges.blade.php
```

Full file content:
```php
<?php

use App\Models\Badge;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Manage Badges')] class extends Component {
    public string $search = '';

    public ?string $editingId = null;
    public array $name = ['en' => '', 'cs' => ''];
    public string $slug = '';
    public string $color = '';

    public ?string $deletingId = null;

    #[Computed]
    public function badges(): \Illuminate\Support\Collection
    {
        return Badge::query()
            ->when($this->search, fn ($q) => $q->whereRaw("name->>'en' ILIKE ?", ["%{$this->search}%"]))
            ->orderByRaw("name->>'en'")
            ->get();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->modal('form')->show();
    }

    public function openEdit(string $id): void
    {
        $badge = Badge::findOrFail($id);
        $this->editingId = $id;
        $this->name = array_merge(['en' => '', 'cs' => ''], $badge->name ?? []);
        $this->slug = $badge->slug;
        $this->color = $badge->color ?? '';
        $this->modal('form')->show();
    }

    public function updatedNameEn(string $value): void
    {
        if (! $this->editingId) {
            $this->slug = Str::slug($value);
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'array'],
            'name.en' => ['required', 'string', 'max:100'],
            'name.cs' => ['nullable', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:100', $this->editingId
                ? \Illuminate\Validation\Rule::unique('badges', 'slug')->ignore($this->editingId)
                : 'unique:badges,slug'],
            'color' => ['nullable', 'string', 'max:50'],
        ]);

        $data = [
            'name' => array_filter($validated['name'], fn ($v) => filled($v)),
            'slug' => $validated['slug'],
            'color' => $validated['color'] ?: null,
        ];

        if ($this->editingId) {
            Badge::findOrFail($this->editingId)->update($data);
        } else {
            Badge::create($data);
        }

        $this->modal('form')->close();
        $this->resetForm();
        unset($this->badges);
    }

    public function confirmDelete(string $id): void
    {
        $this->deletingId = $id;
        $this->modal('delete')->show();
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            Badge::findOrFail($this->deletingId)->delete();
            $this->deletingId = null;
            $this->modal('delete')->close();
            unset($this->badges);
        }
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = ['en' => '', 'cs' => ''];
        $this->slug = '';
        $this->color = '';
        $this->resetValidation();
    }
}; ?>

<div style="font-family: var(--font-body); color: var(--c-fg);" class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 style="font-size: 2rem; font-weight: 600; color: var(--c-fg);">Badges</h1>
            <p style="color: var(--c-muted); font-size: 0.875rem; margin-top: 0.2rem;">Skill and technology tags</p>
        </div>
        <flux:button wire:click="openCreate" icon="plus" class="btn-gold">
            Add badge
        </flux:button>
    </div>

    {{-- Search --}}
    <flux:input wire:model.live.debounce="search" placeholder="Search by name…" icon="magnifying-glass" class="max-w-xs" />

    {{-- Table --}}
    <flux:table>
        <flux:table.columns>
            <flux:table.column>Name (EN)</flux:table.column>
            <flux:table.column>Name (CS)</flux:table.column>
            <flux:table.column>Slug</flux:table.column>
            <flux:table.column>Color</flux:table.column>
            <flux:table.column>Updated</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->badges as $badge)
                <flux:table.row wire:key="{{ $badge->id }}">
                    <flux:table.cell variant="strong">{{ $badge->name['en'] ?? '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $badge->name['cs'] ?? '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $badge->slug }}</flux:table.cell>
                    <flux:table.cell>
                        @if ($badge->color)
                            <flux:badge size="sm" color="{{ $badge->color }}" inset="top bottom">{{ $badge->color }}</flux:badge>
                        @else
                            —
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>{{ $badge->updated_at->format('d.m.Y') }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex gap-2 justify-end">
                            <flux:button size="sm" variant="subtle" icon="pencil" wire:click="openEdit('{{ $badge->id }}')" />
                            <flux:button size="sm" variant="subtle" icon="trash" wire:click="confirmDelete('{{ $badge->id }}')" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6">
                        <p style="color: var(--c-muted); text-align: center; padding: 2rem 0;">No badges found.</p>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Create / Edit modal --}}
    <flux:modal name="form" class="w-full md:w-[560px]">
        <flux:heading>{{ $editingId ? 'Edit badge' : 'New badge' }}</flux:heading>
        <flux:text class="mt-1 mb-5">Fill in the details below.</flux:text>

        <form wire:submit="save" class="space-y-4">
            {{-- Language tabs --}}
            <div x-data="{ locale: 'en' }">
                <div class="flex gap-1 mb-4 p-1 rounded-lg" style="background: var(--c-surface-raised, rgba(0,0,0,0.08));">
                    <button type="button" x-on:click="locale = 'en'"
                        :class="locale === 'en' ? 'shadow-sm font-semibold' : 'opacity-60 hover:opacity-80'"
                        class="flex-1 px-3 py-1.5 rounded-md text-sm transition-all"
                        style="background: transparent;" :style="locale === 'en' ? 'background: var(--c-surface)' : ''">
                        🇬🇧 English
                    </button>
                    <button type="button" x-on:click="locale = 'cs'"
                        :class="locale === 'cs' ? 'shadow-sm font-semibold' : 'opacity-60 hover:opacity-80'"
                        class="flex-1 px-3 py-1.5 rounded-md text-sm transition-all"
                        style="background: transparent;" :style="locale === 'cs' ? 'background: var(--c-surface)' : ''">
                        🇨🇿 Czech
                    </button>
                </div>

                <div x-show="locale === 'en'" class="space-y-4">
                    <flux:field>
                        <flux:label>Name <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
                        <flux:input wire:model="name.en" wire:model.live.debounce="name.en" placeholder="e.g. Laravel" />
                        <flux:error name="name.en" />
                    </flux:field>
                </div>

                <div x-show="locale === 'cs'" class="space-y-4">
                    <flux:field>
                        <flux:label>Name</flux:label>
                        <flux:input wire:model="name.cs" placeholder="např. Laravel" />
                        <flux:error name="name.cs" />
                    </flux:field>
                </div>
            </div>

            {{-- Non-translatable fields --}}
            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Slug <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
                    <flux:input wire:model="slug" placeholder="e.g. laravel" />
                    <flux:error name="slug" />
                </flux:field>

                <flux:field>
                    <flux:label>Color</flux:label>
                    <flux:select wire:model="color">
                        <flux:select.option value="">— none —</flux:select.option>
                        <flux:select.option value="red">Red</flux:select.option>
                        <flux:select.option value="orange">Orange</flux:select.option>
                        <flux:select.option value="yellow">Yellow</flux:select.option>
                        <flux:select.option value="green">Green</flux:select.option>
                        <flux:select.option value="blue">Blue</flux:select.option>
                        <flux:select.option value="purple">Purple</flux:select.option>
                        <flux:select.option value="zinc">Zinc</flux:select.option>
                    </flux:select>
                    <flux:error name="color" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <flux:button x-on:click="$flux.modal('form').close()">Cancel</flux:button>
                <flux:button type="submit" class="btn-gold">{{ $editingId ? 'Save changes' : 'Create' }}</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Delete modal --}}
    <flux:modal name="delete" class="md:w-[400px]">
        <flux:heading>Delete badge?</flux:heading>
        <flux:text class="mt-2 mb-6">This action cannot be undone.</flux:text>
        <div class="flex justify-end gap-2">
            <flux:button x-on:click="$flux.modal('delete').close()">Cancel</flux:button>
            <flux:button wire:click="delete" variant="danger">Delete</flux:button>
        </div>
    </flux:modal>

</div>
```

- [ ] **Step 7: Add route**

In `routes/web.php`, add inside the `auth` middleware group:
```php
Route::livewire('dashboard/badges', 'pages::manage.badges')->name('manage.badges');
```

- [ ] **Step 8: Update sidebar**

In `resources/views/layouts/app/sidebar.blade.php`, change the Badges item:
```blade
<flux:sidebar.item icon="tag" :href="route('manage.badges')" :current="request()->routeIs('manage.badges')" wire:navigate>
    {{ __('Badges') }}
</flux:sidebar.item>
```

- [ ] **Step 9: Run migration**

```bash
docker exec portfolio-2-app-1 php artisan migrate --no-interaction
```

- [ ] **Step 10: Run pint**

```bash
docker exec portfolio-2-app-1 vendor/bin/pint --dirty --format agent
```

- [ ] **Step 11: Run tests**

```bash
docker exec portfolio-2-app-1 php artisan test --compact --filter="BadgesManagement"
```

Expected: All PASS.

- [ ] **Step 12: Commit**

```bash
git add database/migrations/*convert_badges_name_to_json* \
        app/Models/Badge.php \
        database/factories/BadgeFactory.php \
        resources/views/pages/manage/⚡badges.blade.php \
        routes/web.php \
        resources/views/layouts/app/sidebar.blade.php \
        tests/Feature/BadgesManagementTest.php
git commit -m "feat: convert badge name to i18n JSON and add badges manage page

Co-Authored-By: Claude Sonnet 4.6 <noreply@anthropic.com>"
```

---

## Task 3: Article — i18n fields + manage page

**Fields in tabs:** `header`, `description`, `content`
**Fields outside tabs:** `slug`, `date`, `thumbnail_url`, badge selection

**Files:**
- Create: `database/migrations/2026_04_07_000003_convert_articles_columns_to_json.php`
- Create: `app/Models/Article.php`
- Create: `database/factories/ArticleFactory.php`
- Create: `resources/views/pages/manage/⚡articles.blade.php`
- Modify: `routes/web.php`
- Modify: `resources/views/layouts/app/sidebar.blade.php`
- Create: `tests/Feature/ArticlesManagementTest.php`

- [ ] **Step 1: Write failing tests**

```bash
docker exec portfolio-2-app-1 php artisan make:test --pest ArticlesManagementTest --no-interaction
```

File content `tests/Feature/ArticlesManagementTest.php`:
```php
<?php

use App\Models\Article;
use App\Models\Badge;
use App\Models\User;
use Livewire\Livewire;

test('manage articles page renders for authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('manage.articles'))
        ->assertOk();
});

test('can create article with english fields', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::manage.articles')
        ->set('header', ['en' => 'My First Article', 'cs' => ''])
        ->set('description', ['en' => 'A short intro.', 'cs' => ''])
        ->set('content', ['en' => '## Hello\n\nWorld.', 'cs' => ''])
        ->set('slug', 'my-first-article')
        ->set('date', '2026-04-07')
        ->call('save')
        ->assertHasNoErrors();

    $article = Article::first();
    expect($article->header)->toBe(['en' => 'My First Article'])
        ->and($article->slug)->toBe('my-first-article');
});

test('create article requires english header', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::manage.articles')
        ->set('header', ['en' => '', 'cs' => ''])
        ->set('slug', 'test')
        ->set('date', '2026-04-07')
        ->call('save')
        ->assertHasErrors(['header.en']);
});

test('can edit article and update translations', function () {
    $user = User::factory()->create();
    $article = Article::factory()->create([
        'header' => ['en' => 'My Article'],
        'description' => ['en' => 'Short.'],
        'content' => ['en' => 'Content here.'],
        'user_id' => $user->id,
    ]);

    Livewire::actingAs($user)
        ->test('pages::manage.articles')
        ->call('openEdit', $article->id)
        ->assertSet('header', ['en' => 'My Article', 'cs' => ''])
        ->set('header', ['en' => 'My Article', 'cs' => 'Můj článek'])
        ->call('save')
        ->assertHasNoErrors();

    expect($article->fresh()->header)->toBe(['en' => 'My Article', 'cs' => 'Můj článek']);
});

test('can delete article', function () {
    $user = User::factory()->create();
    $article = Article::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test('pages::manage.articles')
        ->call('confirmDelete', $article->id)
        ->call('delete')
        ->assertHasNoErrors();

    expect(Article::count())->toBe(0);
});
```

- [ ] **Step 2: Run tests to confirm they fail**

```bash
docker exec portfolio-2-app-1 php artisan test --compact --filter="ArticlesManagement"
```

Expected: FAIL.

- [ ] **Step 3: Create migration**

```bash
docker exec portfolio-2-app-1 php artisan make:migration convert_articles_columns_to_json --no-interaction
```

File content:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn(['header', 'description', 'content']);
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->json('header')->after('slug');
            $table->json('description')->nullable()->after('header');
            $table->json('content')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn(['header', 'description', 'content']);
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->string('header')->after('slug');
            $table->string('description')->nullable()->after('header');
            $table->longText('content')->nullable()->after('description');
        });
    }
};
```

- [ ] **Step 4: Create `Article` model**

```bash
docker exec portfolio-2-app-1 php artisan make:model Article --no-interaction
```

File content `app/Models/Article.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Article extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'slug',
        'header',
        'description',
        'content',
        'date',
        'thumbnail_url',
        'user_id',
    ];

    protected $casts = [
        'header' => 'array',
        'description' => 'array',
        'content' => 'array',
        'date' => 'date',
    ];

    public function getTranslation(string $field, string $locale, string $fallback = 'en'): string
    {
        $value = $this->{$field};

        if (! is_array($value)) {
            return '';
        }

        return $value[$locale] ?? $value[$fallback] ?? '';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'article_badge');
    }
}
```

- [ ] **Step 5: Create `ArticleFactory`**

```bash
docker exec portfolio-2-app-1 php artisan make:factory ArticleFactory --model=Article --no-interaction
```

File content `database/factories/ArticleFactory.php`:
```php
<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    public function definition(): array
    {
        $header = $this->faker->sentence(4);

        return [
            'slug' => Str::slug($header) . '-' . $this->faker->unique()->numberBetween(1, 9999),
            'header' => ['en' => $header],
            'description' => ['en' => $this->faker->sentence()],
            'content' => ['en' => $this->faker->paragraphs(3, true)],
            'date' => $this->faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'thumbnail_url' => null,
            'user_id' => User::factory(),
        ];
    }

    public function translated(): static
    {
        return $this->state(fn () => [
            'header' => ['en' => $this->faker->sentence(4), 'cs' => $this->faker->sentence(4)],
            'description' => ['en' => $this->faker->sentence(), 'cs' => $this->faker->sentence()],
            'content' => ['en' => $this->faker->paragraph(), 'cs' => $this->faker->paragraph()],
        ]);
    }
}
```

- [ ] **Step 6: Create `⚡articles.blade.php` manage page**

File content `resources/views/pages/manage/⚡articles.blade.php`:
```php
<?php

use App\Models\Article;
use App\Models\Badge;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Manage Articles')] class extends Component {
    public string $search = '';

    public ?string $editingId = null;
    public array $header = ['en' => '', 'cs' => ''];
    public array $description = ['en' => '', 'cs' => ''];
    public array $content = ['en' => '', 'cs' => ''];
    public string $slug = '';
    public string $date = '';
    public string $thumbnail_url = '';
    public array $selectedBadgeIds = [];

    public ?string $deletingId = null;

    #[Computed]
    public function articles(): \Illuminate\Support\Collection
    {
        return Article::query()
            ->when($this->search, fn ($q) => $q->whereRaw("header->>'en' ILIKE ?", ["%{$this->search}%"]))
            ->orderBy('date', 'desc')
            ->orderByRaw("header->>'en'")
            ->get();
    }

    #[Computed]
    public function allBadges(): \Illuminate\Support\Collection
    {
        return Badge::orderByRaw("name->>'en'")->get();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->modal('form')->show();
    }

    public function openEdit(string $id): void
    {
        $article = Article::with('badges')->findOrFail($id);
        $this->editingId = $id;
        $this->header = array_merge(['en' => '', 'cs' => ''], $article->header ?? []);
        $this->description = array_merge(['en' => '', 'cs' => ''], $article->description ?? []);
        $this->content = array_merge(['en' => '', 'cs' => ''], $article->content ?? []);
        $this->slug = $article->slug;
        $this->date = $article->date?->format('Y-m-d') ?? '';
        $this->thumbnail_url = $article->thumbnail_url ?? '';
        $this->selectedBadgeIds = $article->badges->pluck('id')->toArray();
        $this->modal('form')->show();
    }

    public function updatedHeaderEn(string $value): void
    {
        if (! $this->editingId) {
            $this->slug = Str::slug($value);
        }
    }

    public function addBadge(): void
    {
        $this->selectedBadgeIds[] = '';
    }

    public function removeBadge(int $index): void
    {
        array_splice($this->selectedBadgeIds, $index, 1);
        $this->selectedBadgeIds = array_values($this->selectedBadgeIds);
    }

    public function save(): void
    {
        $validated = $this->validate([
            'header' => ['required', 'array'],
            'header.en' => ['required', 'string', 'max:255'],
            'header.cs' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string', 'max:500'],
            'description.cs' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'array'],
            'content.en' => ['nullable', 'string'],
            'content.cs' => ['nullable', 'string'],
            'slug' => ['required', 'string', 'max:255', $this->editingId
                ? \Illuminate\Validation\Rule::unique('articles', 'slug')->ignore($this->editingId)
                : 'unique:articles,slug'],
            'date' => ['required', 'date'],
            'thumbnail_url' => ['nullable', 'url', 'max:500'],
            'selectedBadgeIds' => ['nullable', 'array'],
            'selectedBadgeIds.*' => ['nullable', 'uuid', 'exists:badges,id'],
        ]);

        $badgeIds = collect($this->selectedBadgeIds)
            ->filter(fn ($id) => filled($id))
            ->unique()
            ->values()
            ->toArray();

        $data = [
            'header' => array_filter($validated['header'], fn ($v) => filled($v)),
            'description' => array_filter($validated['description'] ?? [], fn ($v) => filled($v)) ?: null,
            'content' => array_filter($validated['content'] ?? [], fn ($v) => filled($v)) ?: null,
            'slug' => $validated['slug'],
            'date' => $validated['date'],
            'thumbnail_url' => $validated['thumbnail_url'] ?: null,
            'user_id' => auth()->id(),
        ];

        if ($this->editingId) {
            $article = Article::findOrFail($this->editingId);
            $article->update($data);
            $article->badges()->sync($badgeIds);
        } else {
            $article = Article::create($data);
            $article->badges()->sync($badgeIds);
        }

        $this->modal('form')->close();
        $this->resetForm();
        unset($this->articles);
    }

    public function confirmDelete(string $id): void
    {
        $this->deletingId = $id;
        $this->modal('delete')->show();
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            Article::findOrFail($this->deletingId)->delete();
            $this->deletingId = null;
            $this->modal('delete')->close();
            unset($this->articles);
        }
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->header = ['en' => '', 'cs' => ''];
        $this->description = ['en' => '', 'cs' => ''];
        $this->content = ['en' => '', 'cs' => ''];
        $this->slug = '';
        $this->date = '';
        $this->thumbnail_url = '';
        $this->selectedBadgeIds = [];
        $this->resetValidation();
    }
}; ?>

<div style="font-family: var(--font-body); color: var(--c-fg);" class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 style="font-size: 2rem; font-weight: 600; color: var(--c-fg);">Articles</h1>
            <p style="color: var(--c-muted); font-size: 0.875rem; margin-top: 0.2rem;">Blog posts and write-ups</p>
        </div>
        <flux:button wire:click="openCreate" icon="plus" class="btn-gold">
            Add article
        </flux:button>
    </div>

    {{-- Search --}}
    <flux:input wire:model.live.debounce="search" placeholder="Search by title…" icon="magnifying-glass" class="max-w-xs" />

    {{-- Table --}}
    <flux:table>
        <flux:table.columns>
            <flux:table.column>Header</flux:table.column>
            <flux:table.column>Slug</flux:table.column>
            <flux:table.column>Date</flux:table.column>
            <flux:table.column>Updated</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->articles as $article)
                <flux:table.row wire:key="{{ $article->id }}">
                    <flux:table.cell variant="strong">{{ $article->header['en'] ?? '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $article->slug }}</flux:table.cell>
                    <flux:table.cell>{{ $article->date?->format('d.m.Y') ?? '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $article->updated_at->format('d.m.Y') }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex gap-2 justify-end">
                            <flux:button size="sm" variant="subtle" icon="pencil" wire:click="openEdit('{{ $article->id }}')" />
                            <flux:button size="sm" variant="subtle" icon="trash" wire:click="confirmDelete('{{ $article->id }}')" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5">
                        <p style="color: var(--c-muted); text-align: center; padding: 2rem 0;">No articles found.</p>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Create / Edit modal --}}
    <flux:modal name="form" class="w-full md:w-[700px]">
        <flux:heading>{{ $editingId ? 'Edit article' : 'New article' }}</flux:heading>
        <flux:text class="mt-1 mb-5">Fill in the details below.</flux:text>

        <form wire:submit="save" class="space-y-4">
            {{-- Language tabs --}}
            <div x-data="{ locale: 'en' }">
                <div class="flex gap-1 mb-4 p-1 rounded-lg" style="background: var(--c-surface-raised, rgba(0,0,0,0.08));">
                    <button type="button" x-on:click="locale = 'en'"
                        :class="locale === 'en' ? 'shadow-sm font-semibold' : 'opacity-60 hover:opacity-80'"
                        class="flex-1 px-3 py-1.5 rounded-md text-sm transition-all"
                        style="background: transparent;" :style="locale === 'en' ? 'background: var(--c-surface)' : ''">
                        🇬🇧 English
                    </button>
                    <button type="button" x-on:click="locale = 'cs'"
                        :class="locale === 'cs' ? 'shadow-sm font-semibold' : 'opacity-60 hover:opacity-80'"
                        class="flex-1 px-3 py-1.5 rounded-md text-sm transition-all"
                        style="background: transparent;" :style="locale === 'cs' ? 'background: var(--c-surface)' : ''">
                        🇨🇿 Czech
                    </button>
                </div>

                <div x-show="locale === 'en'" class="space-y-4">
                    <flux:field>
                        <flux:label>Header <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
                        <flux:input wire:model="header.en" wire:model.live.debounce="header.en" placeholder="e.g. How I Built This Portfolio" />
                        <flux:error name="header.en" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Description</flux:label>
                        <flux:input wire:model="description.en" placeholder="Short summary shown in listings" />
                        <flux:error name="description.en" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Content</flux:label>
                        <flux:textarea wire:model="content.en" placeholder="Markdown supported…" rows="8" />
                        <flux:error name="content.en" />
                    </flux:field>
                </div>

                <div x-show="locale === 'cs'" class="space-y-4">
                    <flux:field>
                        <flux:label>Header</flux:label>
                        <flux:input wire:model="header.cs" placeholder="např. Jak jsem postavil toto portfolio" />
                        <flux:error name="header.cs" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Description</flux:label>
                        <flux:input wire:model="description.cs" placeholder="Krátký popis zobrazovaný v přehledu" />
                        <flux:error name="description.cs" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Content</flux:label>
                        <flux:textarea wire:model="content.cs" placeholder="Podporuje Markdown…" rows="8" />
                        <flux:error name="content.cs" />
                    </flux:field>
                </div>
            </div>

            {{-- Non-translatable fields --}}
            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Slug <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
                    <flux:input wire:model="slug" placeholder="e.g. how-i-built-this-portfolio" />
                    <flux:error name="slug" />
                </flux:field>

                <flux:field>
                    <flux:label>Date <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
                    <flux:input wire:model="date" type="date" />
                    <flux:error name="date" />
                </flux:field>

                <flux:field class="col-span-2">
                    <flux:label>Thumbnail URL</flux:label>
                    <flux:input wire:model="thumbnail_url" type="url" placeholder="https://…" />
                    <flux:error name="thumbnail_url" />
                </flux:field>

                {{-- Badges --}}
                <div class="col-span-2 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium" style="color: var(--c-fg);">Badges</span>
                        <flux:button size="sm" icon="plus" wire:click.prevent="addBadge" class="btn-gold-subtle">Add badge</flux:button>
                    </div>
                    @foreach ($selectedBadgeIds as $i => $badgeId)
                        <div class="flex gap-2 items-center" wire:key="badge-{{ $i }}">
                            <flux:select wire:model="selectedBadgeIds.{{ $i }}" class="flex-1">
                                <flux:select.option value="">— select badge —</flux:select.option>
                                @foreach ($this->allBadges as $badge)
                                    <flux:select.option value="{{ $badge->id }}">{{ $badge->name['en'] ?? $badge->slug }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:button size="sm" variant="subtle" icon="x-mark" wire:click.prevent="removeBadge({{ $i }})" class="btn-muted-icon" />
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <flux:button x-on:click="$flux.modal('form').close()">Cancel</flux:button>
                <flux:button type="submit" class="btn-gold">{{ $editingId ? 'Save changes' : 'Create' }}</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Delete modal --}}
    <flux:modal name="delete" class="md:w-[400px]">
        <flux:heading>Delete article?</flux:heading>
        <flux:text class="mt-2 mb-6">This action cannot be undone.</flux:text>
        <div class="flex justify-end gap-2">
            <flux:button x-on:click="$flux.modal('delete').close()">Cancel</flux:button>
            <flux:button wire:click="delete" variant="danger">Delete</flux:button>
        </div>
    </flux:modal>

</div>
```

- [ ] **Step 7: Add route and update sidebar**

In `routes/web.php`:
```php
Route::livewire('dashboard/articles', 'pages::manage.articles')->name('manage.articles');
```

In `resources/views/layouts/app/sidebar.blade.php`, update Articles item:
```blade
<flux:sidebar.item icon="newspaper" :href="route('manage.articles')" :current="request()->routeIs('manage.articles')" wire:navigate>
    {{ __('Articles') }}
</flux:sidebar.item>
```

- [ ] **Step 8: Run migration**

```bash
docker exec portfolio-2-app-1 php artisan migrate --no-interaction
```

- [ ] **Step 9: Run pint**

```bash
docker exec portfolio-2-app-1 vendor/bin/pint --dirty --format agent
```

- [ ] **Step 10: Run tests**

```bash
docker exec portfolio-2-app-1 php artisan test --compact --filter="ArticlesManagement"
```

Expected: All PASS.

- [ ] **Step 11: Commit**

```bash
git add database/migrations/*convert_articles_columns_to_json* \
        app/Models/Article.php \
        database/factories/ArticleFactory.php \
        resources/views/pages/manage/⚡articles.blade.php \
        routes/web.php \
        resources/views/layouts/app/sidebar.blade.php \
        tests/Feature/ArticlesManagementTest.php
git commit -m "feat: add articles manage page with i18n header/description/content

Co-Authored-By: Claude Sonnet 4.6 <noreply@anthropic.com>"
```

---

## Task 4: Project — i18n fields + manage page

**Fields in tabs:** `header`, `description`
**Fields outside tabs:** `year` (integer), `slug`, `img_url`, badge selection, inline links

Note: The `links` table stores project-linked links with `alt` (string currently). In this task we manage links inline within the project form (add/remove rows from the `links` table on save). The `alt` field in `links` will be made i18n in Task 5, but the project form will already use the array structure so it's forward-compatible.

**Files:**
- Create: `database/migrations/2026_04_07_000004_convert_projects_columns_to_json.php`
- Create: `app/Models/Project.php`
- Create: `app/Models/Link.php` (basic, alt still string until Task 5)
- Create: `database/factories/ProjectFactory.php`
- Create: `resources/views/pages/manage/⚡projects.blade.php`
- Modify: `routes/web.php`
- Modify: `resources/views/layouts/app/sidebar.blade.php`
- Create: `tests/Feature/ProjectsManagementTest.php`

- [ ] **Step 1: Write failing tests**

```bash
docker exec portfolio-2-app-1 php artisan make:test --pest ProjectsManagementTest --no-interaction
```

File content `tests/Feature/ProjectsManagementTest.php`:
```php
<?php

use App\Models\Badge;
use App\Models\Project;
use App\Models\User;
use Livewire\Livewire;

test('manage projects page renders for authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('manage.projects'))
        ->assertOk();
});

test('can create project with english fields', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::manage.projects')
        ->set('header', ['en' => 'My Portfolio', 'cs' => ''])
        ->set('description', ['en' => 'A personal website.', 'cs' => ''])
        ->set('slug', 'my-portfolio')
        ->set('year', '2026')
        ->call('save')
        ->assertHasNoErrors();

    $project = Project::first();
    expect($project->header)->toBe(['en' => 'My Portfolio'])
        ->and($project->slug)->toBe('my-portfolio')
        ->and($project->year)->toBe(2026);
});

test('create project requires english header', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::manage.projects')
        ->set('header', ['en' => '', 'cs' => ''])
        ->set('slug', 'test')
        ->set('year', '2026')
        ->call('save')
        ->assertHasErrors(['header.en']);
});

test('can edit project and update translations', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create([
        'header' => ['en' => 'My Project'],
        'description' => ['en' => 'Details here.'],
    ]);

    Livewire::actingAs($user)
        ->test('pages::manage.projects')
        ->call('openEdit', $project->id)
        ->assertSet('header', ['en' => 'My Project', 'cs' => ''])
        ->set('header', ['en' => 'My Project', 'cs' => 'Můj projekt'])
        ->call('save')
        ->assertHasNoErrors();

    expect($project->fresh()->header)->toBe(['en' => 'My Project', 'cs' => 'Můj projekt']);
});

test('can delete project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::manage.projects')
        ->call('confirmDelete', $project->id)
        ->call('delete')
        ->assertHasNoErrors();

    expect(Project::count())->toBe(0);
});
```

- [ ] **Step 2: Run tests to confirm they fail**

```bash
docker exec portfolio-2-app-1 php artisan test --compact --filter="ProjectsManagement"
```

Expected: FAIL.

- [ ] **Step 3: Create migration**

```bash
docker exec portfolio-2-app-1 php artisan make:migration convert_projects_columns_to_json --no-interaction
```

File content:
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
            $table->dropColumn(['header', 'description']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->json('header')->after('slug');
            $table->json('description')->nullable()->after('header');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['header', 'description']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->string('header')->after('slug');
            $table->text('description')->nullable()->after('header');
        });
    }
};
```

- [ ] **Step 4: Create `Project` model**

```bash
docker exec portfolio-2-app-1 php artisan make:model Project --no-interaction
```

File content `app/Models/Project.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'year',
        'slug',
        'header',
        'description',
        'img_url',
    ];

    protected $casts = [
        'year' => 'integer',
        'header' => 'array',
        'description' => 'array',
    ];

    public function getTranslation(string $field, string $locale, string $fallback = 'en'): string
    {
        $value = $this->{$field};

        if (! is_array($value)) {
            return '';
        }

        return $value[$locale] ?? $value[$fallback] ?? '';
    }

    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'project_badge');
    }

    public function links(): HasMany
    {
        return $this->hasMany(Link::class);
    }
}
```

- [ ] **Step 5: Create basic `Link` model** (alt will be converted in Task 5)

```bash
docker exec portfolio-2-app-1 php artisan make:model Link --no-interaction
```

File content `app/Models/Link.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Link extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'project_id',
        'alt',
        'img_url',
        'url',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
```

- [ ] **Step 6: Create `ProjectFactory`**

```bash
docker exec portfolio-2-app-1 php artisan make:factory ProjectFactory --model=Project --no-interaction
```

File content `database/factories/ProjectFactory.php`:
```php
<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    public function definition(): array
    {
        $header = $this->faker->sentence(3);

        return [
            'year' => $this->faker->numberBetween(2018, 2026),
            'slug' => Str::slug($header) . '-' . $this->faker->unique()->numberBetween(1, 9999),
            'header' => ['en' => $header],
            'description' => ['en' => $this->faker->sentence()],
            'img_url' => null,
        ];
    }

    public function translated(): static
    {
        return $this->state(fn () => [
            'header' => ['en' => $this->faker->sentence(3), 'cs' => $this->faker->sentence(3)],
            'description' => ['en' => $this->faker->sentence(), 'cs' => $this->faker->sentence()],
        ]);
    }
}
```

- [ ] **Step 7: Create `⚡projects.blade.php` manage page**

File content `resources/views/pages/manage/⚡projects.blade.php`:
```php
<?php

use App\Models\Badge;
use App\Models\Link;
use App\Models\Project;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Manage Projects')] class extends Component {
    public string $search = '';

    public ?string $editingId = null;
    public array $header = ['en' => '', 'cs' => ''];
    public array $description = ['en' => '', 'cs' => ''];
    public string $slug = '';
    public string $year = '';
    public string $img_url = '';
    public array $links = [];
    public array $selectedBadgeIds = [];

    public ?string $deletingId = null;

    #[Computed]
    public function projects(): \Illuminate\Support\Collection
    {
        return Project::query()
            ->when($this->search, fn ($q) => $q->whereRaw("header->>'en' ILIKE ?", ["%{$this->search}%"]))
            ->orderBy('year', 'desc')
            ->orderByRaw("header->>'en'")
            ->get();
    }

    #[Computed]
    public function allBadges(): \Illuminate\Support\Collection
    {
        return Badge::orderByRaw("name->>'en'")->get();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->modal('form')->show();
    }

    public function openEdit(string $id): void
    {
        $project = Project::with(['badges', 'links'])->findOrFail($id);
        $this->editingId = $id;
        $this->header = array_merge(['en' => '', 'cs' => ''], $project->header ?? []);
        $this->description = array_merge(['en' => '', 'cs' => ''], $project->description ?? []);
        $this->slug = $project->slug;
        $this->year = (string) $project->year;
        $this->img_url = $project->img_url ?? '';
        $this->links = $project->links->map(fn ($l) => [
            'url' => $l->url,
            'alt' => $l->alt,
            'img_url' => $l->img_url ?? '',
        ])->toArray();
        $this->selectedBadgeIds = $project->badges->pluck('id')->toArray();
        $this->modal('form')->show();
    }

    public function updatedHeaderEn(string $value): void
    {
        if (! $this->editingId) {
            $this->slug = Str::slug($value);
        }
    }

    public function addLink(): void
    {
        $this->links[] = ['url' => '', 'alt' => '', 'img_url' => ''];
    }

    public function removeLink(int $index): void
    {
        array_splice($this->links, $index, 1);
        $this->links = array_values($this->links);
    }

    public function addBadge(): void
    {
        $this->selectedBadgeIds[] = '';
    }

    public function removeBadge(int $index): void
    {
        array_splice($this->selectedBadgeIds, $index, 1);
        $this->selectedBadgeIds = array_values($this->selectedBadgeIds);
    }

    public function save(): void
    {
        $validated = $this->validate([
            'header' => ['required', 'array'],
            'header.en' => ['required', 'string', 'max:255'],
            'header.cs' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string', 'max:1000'],
            'description.cs' => ['nullable', 'string', 'max:1000'],
            'slug' => ['required', 'string', 'max:255', $this->editingId
                ? \Illuminate\Validation\Rule::unique('projects', 'slug')->ignore($this->editingId)
                : 'unique:projects,slug'],
            'year' => ['required', 'integer', 'min:1900', 'max:2100'],
            'img_url' => ['nullable', 'url', 'max:500'],
            'links' => ['nullable', 'array'],
            'links.*.url' => ['nullable', 'url', 'max:500'],
            'links.*.alt' => ['nullable', 'string', 'max:100'],
            'links.*.img_url' => ['nullable', 'url', 'max:500'],
            'selectedBadgeIds' => ['nullable', 'array'],
            'selectedBadgeIds.*' => ['nullable', 'uuid', 'exists:badges,id'],
        ]);

        $badgeIds = collect($this->selectedBadgeIds)
            ->filter(fn ($id) => filled($id))
            ->unique()
            ->values()
            ->toArray();

        $filteredLinks = collect($this->links)
            ->filter(fn ($l) => filled($l['url'] ?? ''))
            ->values()
            ->toArray();

        $data = [
            'header' => array_filter($validated['header'], fn ($v) => filled($v)),
            'description' => array_filter($validated['description'] ?? [], fn ($v) => filled($v)) ?: null,
            'slug' => $validated['slug'],
            'year' => (int) $validated['year'],
            'img_url' => $validated['img_url'] ?: null,
        ];

        if ($this->editingId) {
            $project = Project::findOrFail($this->editingId);
            $project->update($data);
            $project->badges()->sync($badgeIds);
            $project->links()->delete();
        } else {
            $project = Project::create($data);
            $project->badges()->sync($badgeIds);
        }

        foreach ($filteredLinks as $linkData) {
            $project->links()->create([
                'url' => $linkData['url'],
                'alt' => $linkData['alt'] ?? '',
                'img_url' => $linkData['img_url'] ?: null,
            ]);
        }

        $this->modal('form')->close();
        $this->resetForm();
        unset($this->projects);
    }

    public function confirmDelete(string $id): void
    {
        $this->deletingId = $id;
        $this->modal('delete')->show();
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            Project::findOrFail($this->deletingId)->delete();
            $this->deletingId = null;
            $this->modal('delete')->close();
            unset($this->projects);
        }
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->header = ['en' => '', 'cs' => ''];
        $this->description = ['en' => '', 'cs' => ''];
        $this->slug = '';
        $this->year = '';
        $this->img_url = '';
        $this->links = [];
        $this->selectedBadgeIds = [];
        $this->resetValidation();
    }
}; ?>

<div style="font-family: var(--font-body); color: var(--c-fg);" class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 style="font-size: 2rem; font-weight: 600; color: var(--c-fg);">Projects</h1>
            <p style="color: var(--c-muted); font-size: 0.875rem; margin-top: 0.2rem;">Portfolio projects</p>
        </div>
        <flux:button wire:click="openCreate" icon="plus" class="btn-gold">
            Add project
        </flux:button>
    </div>

    {{-- Search --}}
    <flux:input wire:model.live.debounce="search" placeholder="Search by title…" icon="magnifying-glass" class="max-w-xs" />

    {{-- Table --}}
    <flux:table>
        <flux:table.columns>
            <flux:table.column>Header</flux:table.column>
            <flux:table.column>Year</flux:table.column>
            <flux:table.column>Slug</flux:table.column>
            <flux:table.column>Updated</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->projects as $project)
                <flux:table.row wire:key="{{ $project->id }}">
                    <flux:table.cell variant="strong">{{ $project->header['en'] ?? '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $project->year }}</flux:table.cell>
                    <flux:table.cell>{{ $project->slug }}</flux:table.cell>
                    <flux:table.cell>{{ $project->updated_at->format('d.m.Y') }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex gap-2 justify-end">
                            <flux:button size="sm" variant="subtle" icon="pencil" wire:click="openEdit('{{ $project->id }}')" />
                            <flux:button size="sm" variant="subtle" icon="trash" wire:click="confirmDelete('{{ $project->id }}')" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5">
                        <p style="color: var(--c-muted); text-align: center; padding: 2rem 0;">No projects found.</p>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Create / Edit modal --}}
    <flux:modal name="form" class="w-full md:w-[700px]">
        <flux:heading>{{ $editingId ? 'Edit project' : 'New project' }}</flux:heading>
        <flux:text class="mt-1 mb-5">Fill in the details below.</flux:text>

        <form wire:submit="save" class="space-y-4">
            {{-- Language tabs --}}
            <div x-data="{ locale: 'en' }">
                <div class="flex gap-1 mb-4 p-1 rounded-lg" style="background: var(--c-surface-raised, rgba(0,0,0,0.08));">
                    <button type="button" x-on:click="locale = 'en'"
                        :class="locale === 'en' ? 'shadow-sm font-semibold' : 'opacity-60 hover:opacity-80'"
                        class="flex-1 px-3 py-1.5 rounded-md text-sm transition-all"
                        style="background: transparent;" :style="locale === 'en' ? 'background: var(--c-surface)' : ''">
                        🇬🇧 English
                    </button>
                    <button type="button" x-on:click="locale = 'cs'"
                        :class="locale === 'cs' ? 'shadow-sm font-semibold' : 'opacity-60 hover:opacity-80'"
                        class="flex-1 px-3 py-1.5 rounded-md text-sm transition-all"
                        style="background: transparent;" :style="locale === 'cs' ? 'background: var(--c-surface)' : ''">
                        🇨🇿 Czech
                    </button>
                </div>

                <div x-show="locale === 'en'" class="space-y-4">
                    <flux:field>
                        <flux:label>Header <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
                        <flux:input wire:model="header.en" wire:model.live.debounce="header.en" placeholder="e.g. Portfolio Website" />
                        <flux:error name="header.en" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Description</flux:label>
                        <flux:textarea wire:model="description.en" placeholder="Short project description…" rows="4" />
                        <flux:error name="description.en" />
                    </flux:field>
                </div>

                <div x-show="locale === 'cs'" class="space-y-4">
                    <flux:field>
                        <flux:label>Header</flux:label>
                        <flux:input wire:model="header.cs" placeholder="např. Portfoliový web" />
                        <flux:error name="header.cs" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Description</flux:label>
                        <flux:textarea wire:model="description.cs" placeholder="Krátký popis projektu…" rows="4" />
                        <flux:error name="description.cs" />
                    </flux:field>
                </div>
            </div>

            {{-- Non-translatable fields --}}
            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Slug <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
                    <flux:input wire:model="slug" placeholder="e.g. portfolio-website" />
                    <flux:error name="slug" />
                </flux:field>

                <flux:field>
                    <flux:label>Year <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
                    <flux:input wire:model="year" type="number" placeholder="e.g. 2026" min="1900" max="2100" />
                    <flux:error name="year" />
                </flux:field>

                <flux:field class="col-span-2">
                    <flux:label>Image URL</flux:label>
                    <flux:input wire:model="img_url" type="url" placeholder="https://…" />
                    <flux:error name="img_url" />
                </flux:field>

                {{-- Links --}}
                <div class="col-span-2 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium" style="color: var(--c-fg);">Links</span>
                        <flux:button size="sm" icon="plus" wire:click.prevent="addLink" class="btn-gold-subtle">Add link</flux:button>
                    </div>
                    @foreach ($links as $i => $link)
                        <div class="space-y-3 rounded-lg p-4" style="border: 1px solid var(--c-primary-fade); background-color: rgba(96,84,67,0.15);" wire:key="link-{{ $i }}">
                            <div class="flex gap-2 items-center">
                                <flux:input wire:model="links.{{ $i }}.url" type="url" placeholder="URL — https://…" class="flex-1" />
                                <flux:button size="sm" variant="subtle" icon="x-mark" wire:click.prevent="removeLink({{ $i }})" class="btn-muted-icon" />
                            </div>
                            <flux:error name="links.{{ $i }}.url" />
                            <flux:input wire:model="links.{{ $i }}.alt" placeholder="Alt text (optional)" />
                            <div>
                                <flux:input wire:model="links.{{ $i }}.img_url" type="url" placeholder="Icon URL (optional)" />
                                <flux:error name="links.{{ $i }}.img_url" />
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Badges --}}
                <div class="col-span-2 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium" style="color: var(--c-fg);">Badges</span>
                        <flux:button size="sm" icon="plus" wire:click.prevent="addBadge" class="btn-gold-subtle">Add badge</flux:button>
                    </div>
                    @foreach ($selectedBadgeIds as $i => $badgeId)
                        <div class="flex gap-2 items-center" wire:key="badge-{{ $i }}">
                            <flux:select wire:model="selectedBadgeIds.{{ $i }}" class="flex-1">
                                <flux:select.option value="">— select badge —</flux:select.option>
                                @foreach ($this->allBadges as $badge)
                                    <flux:select.option value="{{ $badge->id }}">{{ $badge->name['en'] ?? $badge->slug }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:button size="sm" variant="subtle" icon="x-mark" wire:click.prevent="removeBadge({{ $i }})" class="btn-muted-icon" />
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <flux:button x-on:click="$flux.modal('form').close()">Cancel</flux:button>
                <flux:button type="submit" class="btn-gold">{{ $editingId ? 'Save changes' : 'Create' }}</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Delete modal --}}
    <flux:modal name="delete" class="md:w-[400px]">
        <flux:heading>Delete project?</flux:heading>
        <flux:text class="mt-2 mb-6">This action cannot be undone.</flux:text>
        <div class="flex justify-end gap-2">
            <flux:button x-on:click="$flux.modal('delete').close()">Cancel</flux:button>
            <flux:button wire:click="delete" variant="danger">Delete</flux:button>
        </div>
    </flux:modal>

</div>
```

- [ ] **Step 8: Add route and update sidebar**

In `routes/web.php`:
```php
Route::livewire('dashboard/projects', 'pages::manage.projects')->name('manage.projects');
```

In `resources/views/layouts/app/sidebar.blade.php`, update Projects item:
```blade
<flux:sidebar.item icon="layout-grid" :href="route('manage.projects')" :current="request()->routeIs('manage.projects')" wire:navigate>
    {{ __('Projects') }}
</flux:sidebar.item>
```

- [ ] **Step 9: Run migration**

```bash
docker exec portfolio-2-app-1 php artisan migrate --no-interaction
```

- [ ] **Step 10: Run pint**

```bash
docker exec portfolio-2-app-1 vendor/bin/pint --dirty --format agent
```

- [ ] **Step 11: Run tests**

```bash
docker exec portfolio-2-app-1 php artisan test --compact --filter="ProjectsManagement"
```

Expected: All PASS.

- [ ] **Step 12: Commit**

```bash
git add database/migrations/*convert_projects_columns_to_json* \
        app/Models/Project.php \
        app/Models/Link.php \
        database/factories/ProjectFactory.php \
        resources/views/pages/manage/⚡projects.blade.php \
        routes/web.php \
        resources/views/layouts/app/sidebar.blade.php \
        tests/Feature/ProjectsManagementTest.php
git commit -m "feat: add projects manage page with i18n header/description and inline links

Co-Authored-By: Claude Sonnet 4.6 <noreply@anthropic.com>"
```

---

## Task 5: Link — `alt` → i18n + Links manage page

**Fields in tabs:** `alt`
**Fields outside tabs:** `url`, `img_url`, `project_id` (select)

Note: The `links` table `alt` column is currently a string. The projects manage page (Task 4) stores `alt` as a plain string from the form. In this task we convert `alt` to JSON and update the project form to send an array, and create a dedicated Links manage page.

**Files:**
- Create: `database/migrations/2026_04_07_000005_convert_links_alt_to_json.php`
- Modify: `app/Models/Link.php` — add `alt` cast + `getTranslation`
- Create: `database/factories/LinkFactory.php`
- Modify: `resources/views/pages/manage/⚡projects.blade.php` — alt field becomes `alt.en`/`alt.cs`
- Create: `resources/views/pages/manage/⚡links.blade.php`
- Modify: `routes/web.php`
- Modify: `resources/views/layouts/app/sidebar.blade.php`
- Create: `tests/Feature/LinksManagementTest.php`

- [ ] **Step 1: Write failing tests**

```bash
docker exec portfolio-2-app-1 php artisan make:test --pest LinksManagementTest --no-interaction
```

File content `tests/Feature/LinksManagementTest.php`:
```php
<?php

use App\Models\Link;
use App\Models\Project;
use App\Models\User;
use Livewire\Livewire;

test('manage links page renders for authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('manage.links'))
        ->assertOk();
});

test('can create link with i18n alt', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::manage.links')
        ->set('project_id', $project->id)
        ->set('url', 'https://example.com')
        ->set('alt', ['en' => 'Visit site', 'cs' => ''])
        ->set('img_url', '')
        ->call('save')
        ->assertHasNoErrors();

    $link = Link::first();
    expect($link->alt)->toBe(['en' => 'Visit site'])
        ->and($link->url)->toBe('https://example.com');
});

test('create link requires url', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::manage.links')
        ->set('project_id', $project->id)
        ->set('url', '')
        ->set('alt', ['en' => 'Visit', 'cs' => ''])
        ->call('save')
        ->assertHasErrors(['url']);
});

test('can delete link', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create();
    $link = Link::factory()->create(['project_id' => $project->id]);

    Livewire::actingAs($user)
        ->test('pages::manage.links')
        ->call('confirmDelete', $link->id)
        ->call('delete')
        ->assertHasNoErrors();

    expect(Link::count())->toBe(0);
});
```

- [ ] **Step 2: Run tests to confirm they fail**

```bash
docker exec portfolio-2-app-1 php artisan test --compact --filter="LinksManagement"
```

Expected: FAIL.

- [ ] **Step 3: Create migration**

```bash
docker exec portfolio-2-app-1 php artisan make:migration convert_links_alt_to_json --no-interaction
```

File content:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            UPDATE links
            SET alt = json_build_object('en', alt)::jsonb
            WHERE alt IS NOT NULL AND alt != ''
        ");

        Schema::table('links', function (Blueprint $table) {
            $table->json('alt')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::statement("
            UPDATE links
            SET alt = alt->>'en'
            WHERE alt IS NOT NULL
        ");

        Schema::table('links', function (Blueprint $table) {
            $table->string('alt')->change();
        });
    }
};
```

- [ ] **Step 4: Update `Link` model** — add json cast for alt

Full updated `app/Models/Link.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Link extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'project_id',
        'alt',
        'img_url',
        'url',
    ];

    protected $casts = [
        'alt' => 'array',
    ];

    public function getTranslation(string $field, string $locale, string $fallback = 'en'): string
    {
        $value = $this->{$field};

        if (! is_array($value)) {
            return '';
        }

        return $value[$locale] ?? $value[$fallback] ?? '';
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
```

- [ ] **Step 5: Create `LinkFactory`**

```bash
docker exec portfolio-2-app-1 php artisan make:factory LinkFactory --model=Link --no-interaction
```

File content `database/factories/LinkFactory.php`:
```php
<?php

namespace Database\Factories;

use App\Models\Link;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Link>
 */
class LinkFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'url' => $this->faker->url(),
            'alt' => ['en' => $this->faker->words(3, true)],
            'img_url' => null,
        ];
    }

    public function translated(): static
    {
        return $this->state(fn () => [
            'alt' => ['en' => $this->faker->words(3, true), 'cs' => $this->faker->words(3, true)],
        ]);
    }
}
```

- [ ] **Step 6: Update `⚡projects.blade.php` — alt becomes i18n**

The project form currently sends `alt` as a plain string in the links array. Update the link rows section to use language-aware alt.

In the `⚡projects.blade.php` links loop, replace the `alt` input:
```blade
<flux:input wire:model="links.{{ $i }}.alt" placeholder="Alt text (optional)" />
```
with two inputs inside a mini language-aware structure. Since adding full tabs inside an existing tab context would be complex, use a simpler two-column EN/CS layout:
```blade
<div class="grid grid-cols-2 gap-2">
    <flux:input wire:model="links.{{ $i }}.alt.en" placeholder="Alt (EN)" />
    <flux:input wire:model="links.{{ $i }}.alt.cs" placeholder="Alt (CS)" />
</div>
```

Update the `addLink()` method to use array alt:
```php
public function addLink(): void
{
    $this->links[] = ['url' => '', 'alt' => ['en' => '', 'cs' => ''], 'img_url' => ''];
}
```

Update `openEdit()` links mapping to use array alt:
```php
$this->links = $project->links->map(fn ($l) => [
    'url' => $l->url,
    'alt' => array_merge(['en' => '', 'cs' => ''], $l->alt ?? []),
    'img_url' => $l->img_url ?? '',
])->toArray();
```

Update validation in `save()`:
```php
'links.*.alt' => ['nullable', 'array'],
'links.*.alt.en' => ['nullable', 'string', 'max:100'],
'links.*.alt.cs' => ['nullable', 'string', 'max:100'],
```

Update link creation in `save()`:
```php
$project->links()->create([
    'url' => $linkData['url'],
    'alt' => array_filter($linkData['alt'] ?? [], fn ($v) => filled($v)) ?: null,
    'img_url' => $linkData['img_url'] ?: null,
]);
```

- [ ] **Step 7: Create `⚡links.blade.php` manage page**

File content `resources/views/pages/manage/⚡links.blade.php`:
```php
<?php

use App\Models\Link;
use App\Models\Project;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Manage Links')] class extends Component {
    public string $search = '';

    public ?string $editingId = null;
    public string $project_id = '';
    public string $url = '';
    public array $alt = ['en' => '', 'cs' => ''];
    public string $img_url = '';

    public ?string $deletingId = null;

    #[Computed]
    public function links(): \Illuminate\Support\Collection
    {
        return Link::with('project')
            ->when($this->search, fn ($q) => $q->where('url', 'ILIKE', "%{$this->search}%"))
            ->orderByRaw("alt->>'en'")
            ->get();
    }

    #[Computed]
    public function allProjects(): \Illuminate\Support\Collection
    {
        return Project::orderByRaw("header->>'en'")->get();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->modal('form')->show();
    }

    public function openEdit(string $id): void
    {
        $link = Link::findOrFail($id);
        $this->editingId = $id;
        $this->project_id = $link->project_id;
        $this->url = $link->url;
        $this->alt = array_merge(['en' => '', 'cs' => ''], $link->alt ?? []);
        $this->img_url = $link->img_url ?? '';
        $this->modal('form')->show();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'project_id' => ['required', 'uuid', 'exists:projects,id'],
            'url' => ['required', 'url', 'max:500'],
            'alt' => ['nullable', 'array'],
            'alt.en' => ['nullable', 'string', 'max:100'],
            'alt.cs' => ['nullable', 'string', 'max:100'],
            'img_url' => ['nullable', 'url', 'max:500'],
        ]);

        $data = [
            'project_id' => $validated['project_id'],
            'url' => $validated['url'],
            'alt' => array_filter($validated['alt'] ?? [], fn ($v) => filled($v)) ?: null,
            'img_url' => $validated['img_url'] ?: null,
        ];

        if ($this->editingId) {
            Link::findOrFail($this->editingId)->update($data);
        } else {
            Link::create($data);
        }

        $this->modal('form')->close();
        $this->resetForm();
        unset($this->links);
    }

    public function confirmDelete(string $id): void
    {
        $this->deletingId = $id;
        $this->modal('delete')->show();
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            Link::findOrFail($this->deletingId)->delete();
            $this->deletingId = null;
            $this->modal('delete')->close();
            unset($this->links);
        }
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->project_id = '';
        $this->url = '';
        $this->alt = ['en' => '', 'cs' => ''];
        $this->img_url = '';
        $this->resetValidation();
    }
}; ?>

<div style="font-family: var(--font-body); color: var(--c-fg);" class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 style="font-size: 2rem; font-weight: 600; color: var(--c-fg);">Links</h1>
            <p style="color: var(--c-muted); font-size: 0.875rem; margin-top: 0.2rem;">Project external links</p>
        </div>
        <flux:button wire:click="openCreate" icon="plus" class="btn-gold">
            Add link
        </flux:button>
    </div>

    {{-- Search --}}
    <flux:input wire:model.live.debounce="search" placeholder="Search by URL…" icon="magnifying-glass" class="max-w-xs" />

    {{-- Table --}}
    <flux:table>
        <flux:table.columns>
            <flux:table.column>URL</flux:table.column>
            <flux:table.column>Alt (EN)</flux:table.column>
            <flux:table.column>Alt (CS)</flux:table.column>
            <flux:table.column>Project</flux:table.column>
            <flux:table.column>Updated</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->links as $link)
                <flux:table.row wire:key="{{ $link->id }}">
                    <flux:table.cell variant="strong" class="max-w-[200px] truncate">{{ $link->url }}</flux:table.cell>
                    <flux:table.cell>{{ $link->alt['en'] ?? '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $link->alt['cs'] ?? '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $link->project?->header['en'] ?? '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $link->updated_at->format('d.m.Y') }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex gap-2 justify-end">
                            <flux:button size="sm" variant="subtle" icon="pencil" wire:click="openEdit('{{ $link->id }}')" />
                            <flux:button size="sm" variant="subtle" icon="trash" wire:click="confirmDelete('{{ $link->id }}')" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6">
                        <p style="color: var(--c-muted); text-align: center; padding: 2rem 0;">No links found.</p>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Create / Edit modal --}}
    <flux:modal name="form" class="w-full md:w-[560px]">
        <flux:heading>{{ $editingId ? 'Edit link' : 'New link' }}</flux:heading>
        <flux:text class="mt-1 mb-5">Fill in the details below.</flux:text>

        <form wire:submit="save" class="space-y-4">
            <flux:field>
                <flux:label>Project <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
                <flux:select wire:model="project_id">
                    <flux:select.option value="">— select project —</flux:select.option>
                    @foreach ($this->allProjects as $project)
                        <flux:select.option value="{{ $project->id }}">{{ $project->header['en'] ?? $project->slug }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="project_id" />
            </flux:field>

            <flux:field>
                <flux:label>URL <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
                <flux:input wire:model="url" type="url" placeholder="https://…" />
                <flux:error name="url" />
            </flux:field>

            {{-- Language tabs for alt --}}
            <div x-data="{ locale: 'en' }">
                <div class="flex gap-1 mb-4 p-1 rounded-lg" style="background: var(--c-surface-raised, rgba(0,0,0,0.08));">
                    <button type="button" x-on:click="locale = 'en'"
                        :class="locale === 'en' ? 'shadow-sm font-semibold' : 'opacity-60 hover:opacity-80'"
                        class="flex-1 px-3 py-1.5 rounded-md text-sm transition-all"
                        style="background: transparent;" :style="locale === 'en' ? 'background: var(--c-surface)' : ''">
                        🇬🇧 English
                    </button>
                    <button type="button" x-on:click="locale = 'cs'"
                        :class="locale === 'cs' ? 'shadow-sm font-semibold' : 'opacity-60 hover:opacity-80'"
                        class="flex-1 px-3 py-1.5 rounded-md text-sm transition-all"
                        style="background: transparent;" :style="locale === 'cs' ? 'background: var(--c-surface)' : ''">
                        🇨🇿 Czech
                    </button>
                </div>

                <div x-show="locale === 'en'" class="space-y-4">
                    <flux:field>
                        <flux:label>Alt text</flux:label>
                        <flux:input wire:model="alt.en" placeholder="e.g. Visit on GitHub" />
                        <flux:error name="alt.en" />
                    </flux:field>
                </div>

                <div x-show="locale === 'cs'" class="space-y-4">
                    <flux:field>
                        <flux:label>Alt text</flux:label>
                        <flux:input wire:model="alt.cs" placeholder="např. Zobrazit na GitHubu" />
                        <flux:error name="alt.cs" />
                    </flux:field>
                </div>
            </div>

            <flux:field>
                <flux:label>Icon URL</flux:label>
                <flux:input wire:model="img_url" type="url" placeholder="https://… (icon image)" />
                <flux:error name="img_url" />
            </flux:field>

            <div class="flex justify-end gap-2 pt-2">
                <flux:button x-on:click="$flux.modal('form').close()">Cancel</flux:button>
                <flux:button type="submit" class="btn-gold">{{ $editingId ? 'Save changes' : 'Create' }}</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Delete modal --}}
    <flux:modal name="delete" class="md:w-[400px]">
        <flux:heading>Delete link?</flux:heading>
        <flux:text class="mt-2 mb-6">This action cannot be undone.</flux:text>
        <div class="flex justify-end gap-2">
            <flux:button x-on:click="$flux.modal('delete').close()">Cancel</flux:button>
            <flux:button wire:click="delete" variant="danger">Delete</flux:button>
        </div>
    </flux:modal>

</div>
```

- [ ] **Step 8: Add route and update sidebar**

In `routes/web.php`:
```php
Route::livewire('dashboard/links', 'pages::manage.links')->name('manage.links');
```

In `resources/views/layouts/app/sidebar.blade.php`, update Links item:
```blade
<flux:sidebar.item icon="link" :href="route('manage.links')" :current="request()->routeIs('manage.links')" wire:navigate>
    {{ __('Links') }}
</flux:sidebar.item>
```

- [ ] **Step 9: Run migration**

```bash
docker exec portfolio-2-app-1 php artisan migrate --no-interaction
```

- [ ] **Step 10: Run pint**

```bash
docker exec portfolio-2-app-1 vendor/bin/pint --dirty --format agent
```

- [ ] **Step 11: Run all tests**

```bash
docker exec portfolio-2-app-1 php artisan test --compact --filter="LinksManagement"
```

Expected: All PASS.

- [ ] **Step 12: Run full test suite**

```bash
docker exec portfolio-2-app-1 php artisan test --compact
```

Expected: All PASS.

- [ ] **Step 13: Commit**

```bash
git add database/migrations/*convert_links_alt_to_json* \
        app/Models/Link.php \
        database/factories/LinkFactory.php \
        resources/views/pages/manage/⚡projects.blade.php \
        resources/views/pages/manage/⚡links.blade.php \
        routes/web.php \
        resources/views/layouts/app/sidebar.blade.php \
        tests/Feature/LinksManagementTest.php
git commit -m "feat: convert link alt to i18n JSON and add links manage page

Co-Authored-By: Claude Sonnet 4.6 <noreply@anthropic.com>"
```
