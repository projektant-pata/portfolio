# Experience Content & i18n Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a markdown `content` field to experiences and make `title`, `subtitle`, and `content` multilingual (EN/CS) using JSON columns, with language tabs in the dashboard edit modal.

**Architecture:** Convert `title` and `subtitle` columns to JSON, add `content` as JSON — each stores locale-keyed values like `{"en": "...", "cs": "..."}`. The `Experience` model gets JSON casts and a `getTranslation()` helper. The Livewire manage page gains Alpine-driven EN/CS tabs in the edit form.

**Tech Stack:** Laravel 13, Livewire 4, Flux UI v2, Pest 4, PostgreSQL 17, Alpine.js (bundled with Flux)

---

## Files

| File | Action | Responsibility |
|---|---|---|
| `database/migrations/2026_04_02_XXXXXX_convert_experience_columns_to_json.php` | Create | Drop string columns, re-add as JSON, add `content` |
| `database/factories/ExperienceFactory.php` | Create | Factory for test setup |
| `app/Models/Experience.php` | Modify | JSON casts, updated fillable, `getTranslation()` helper |
| `tests/Feature/ExperienceManagementTest.php` | Create | Feature tests for model + Livewire component |
| `resources/views/pages/manage/⚡experiences.blade.php` | Modify | Updated state, validation, save logic, EN/CS tabs in modal |
| `docs/database.md` | Modify | Update column types for title, subtitle; add content row |

---

## Task 1: Migration — convert columns to JSON

**Files:**
- Create: `database/migrations/2026_04_02_XXXXXX_convert_experience_columns_to_json.php` (use `php artisan make:migration`)

- [ ] **Step 1: Create the migration**

```bash
docker exec portfolio-2-app-1 php artisan make:migration convert_experience_columns_to_json --no-interaction
```

- [ ] **Step 2: Write the migration**

Fill the generated file with:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('experiences', function (Blueprint $table) {
            $table->dropColumn(['title', 'subtitle']);
        });

        Schema::table('experiences', function (Blueprint $table) {
            $table->json('title')->after('type');
            $table->json('subtitle')->nullable()->after('title');
            $table->json('content')->nullable()->after('subtitle');
        });
    }

    public function down(): void
    {
        Schema::table('experiences', function (Blueprint $table) {
            $table->dropColumn(['title', 'subtitle', 'content']);
        });

        Schema::table('experiences', function (Blueprint $table) {
            $table->string('title')->after('type');
            $table->string('subtitle')->nullable()->after('title');
        });
    }
};
```

- [ ] **Step 3: Run the migration**

```bash
docker exec portfolio-2-app-1 php artisan migrate --no-interaction
```

Expected output: `Migrating: ..._convert_experience_columns_to_json` then `Migrated`.

- [ ] **Step 4: Commit**

```bash
git add database/migrations/
git commit -m "feat: migrate experience title/subtitle to json, add content column"
```

---

## Task 2: Factory

**Files:**
- Create: `database/factories/ExperienceFactory.php`

- [ ] **Step 1: Create the factory**

```bash
docker exec portfolio-2-app-1 php artisan make:factory ExperienceFactory --model=Experience --no-interaction
```

- [ ] **Step 2: Write the factory**

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Experience>
 */
class ExperienceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(['work', 'life']),
            'is_special' => false,
            'title' => ['en' => $this->faker->jobTitle()],
            'subtitle' => ['en' => $this->faker->company()],
            'content' => null,
            'year' => (string) $this->faker->year(),
            'image_path' => null,
            'links' => null,
            'sort_order' => 0,
        ];
    }

    public function withContent(): static
    {
        return $this->state(fn () => [
            'content' => ['en' => $this->faker->paragraphs(2, true)],
        ]);
    }

    public function translated(): static
    {
        return $this->state(fn () => [
            'title' => ['en' => $this->faker->jobTitle(), 'cs' => $this->faker->jobTitle()],
            'subtitle' => ['en' => $this->faker->company(), 'cs' => $this->faker->company()],
            'content' => ['en' => $this->faker->paragraph(), 'cs' => $this->faker->paragraph()],
        ]);
    }
}
```

- [ ] **Step 3: Add `HasFactory` to the Experience model**

In `app/Models/Experience.php`, add the trait and import:

```php
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Experience extends Model
{
    use HasFactory;
    // ...
}
```

- [ ] **Step 4: Commit**

```bash
git add database/factories/ExperienceFactory.php app/Models/Experience.php
git commit -m "feat: add ExperienceFactory"
```

---

## Task 3: Update the Experience model

**Files:**
- Modify: `app/Models/Experience.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/ExperienceManagementTest.php`:

```php
<?php

use App\Models\Experience;

test('experience stores title as json with locale keys', function () {
    $experience = Experience::factory()->create([
        'title' => ['en' => 'Software Developer', 'cs' => 'Softwarový vývojář'],
    ]);

    expect($experience->fresh()->title)->toBe(['en' => 'Software Developer', 'cs' => 'Softwarový vývojář']);
});

test('getTranslation returns value for requested locale', function () {
    $experience = Experience::factory()->create([
        'title' => ['en' => 'Software Developer', 'cs' => 'Softwarový vývojář'],
    ]);

    expect($experience->getTranslation('title', 'cs'))->toBe('Softwarový vývojář');
});

test('getTranslation falls back to english when locale is missing', function () {
    $experience = Experience::factory()->create([
        'title' => ['en' => 'Software Developer'],
    ]);

    expect($experience->getTranslation('title', 'cs'))->toBe('Software Developer');
});

test('getTranslation returns empty string when field is null', function () {
    $experience = Experience::factory()->create([
        'content' => null,
    ]);

    expect($experience->getTranslation('content', 'en'))->toBe('');
});
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
docker exec portfolio-2-app-1 php artisan test --compact --filter=ExperienceManagementTest
```

Expected: failures — `getTranslation` method does not exist.

- [ ] **Step 3: Update the model**

Replace the full contents of `app/Models/Experience.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Experience extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'is_special',
        'title',
        'subtitle',
        'content',
        'year',
        'image_path',
        'links',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_special' => 'boolean',
        'links' => 'array',
        'title' => 'array',
        'subtitle' => 'array',
        'content' => 'array',
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
        return $this->belongsToMany(Badge::class);
    }
}
```

- [ ] **Step 4: Run tests to verify they pass**

```bash
docker exec portfolio-2-app-1 php artisan test --compact --filter=ExperienceManagementTest
```

Expected: all 4 pass.

- [ ] **Step 5: Run pint**

```bash
docker exec portfolio-2-app-1 vendor/bin/pint --dirty --format agent
```

- [ ] **Step 6: Commit**

```bash
git add app/Models/Experience.php tests/Feature/ExperienceManagementTest.php
git commit -m "feat: update Experience model with json casts and getTranslation helper"
```

---

## Task 4: Update the Livewire component — PHP class

**Files:**
- Modify: `resources/views/pages/manage/⚡experiences.blade.php` (PHP class section, lines 1–171)

- [ ] **Step 1: Write failing Livewire tests**

Add to `tests/Feature/ExperienceManagementTest.php`:

```php
use App\Models\User;
use Livewire\Livewire;

test('manage experiences page renders for authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('manage.experiences'))
        ->assertOk();
});

test('can create experience with english title', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::manage.experiences')
        ->set('type', 'work')
        ->set('title', ['en' => 'Software Developer', 'cs' => ''])
        ->set('subtitle', ['en' => 'Acme Corp', 'cs' => ''])
        ->set('content', ['en' => '## Overview\n\nGreat job.', 'cs' => ''])
        ->set('year', '2024')
        ->call('save')
        ->assertHasNoErrors();

    $experience = Experience::first();
    expect($experience->title)->toBe(['en' => 'Software Developer'])
        ->and($experience->subtitle)->toBe(['en' => 'Acme Corp'])
        ->and($experience->content)->toBe(['en' => '## Overview\n\nGreat job.']);
});

test('create experience requires english title', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::manage.experiences')
        ->set('type', 'work')
        ->set('title', ['en' => '', 'cs' => ''])
        ->call('save')
        ->assertHasErrors(['title.en']);
});

test('can edit experience and update translations', function () {
    $user = User::factory()->create();
    $experience = Experience::factory()->create([
        'title' => ['en' => 'Developer'],
        'content' => ['en' => 'Some content'],
    ]);

    Livewire::actingAs($user)
        ->test('pages::manage.experiences')
        ->call('openEdit', $experience->id)
        ->assertSet('title', ['en' => 'Developer', 'cs' => ''])
        ->assertSet('content', ['en' => 'Some content', 'cs' => ''])
        ->set('title', ['en' => 'Developer', 'cs' => 'Vývojář'])
        ->call('save')
        ->assertHasNoErrors();

    expect($experience->fresh()->title)->toBe(['en' => 'Developer', 'cs' => 'Vývojář']);
});
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
docker exec portfolio-2-app-1 php artisan test --compact --filter=ExperienceManagementTest
```

Expected: failures — state properties don't exist yet, `openEdit` loads string not array, etc.

- [ ] **Step 3: Update the PHP class section of the component**

Replace the entire PHP class section (the `<?php ... ?>` block at the top of `resources/views/pages/manage/⚡experiences.blade.php`) with:

```php
<?php

use App\Models\Badge;
use App\Models\Experience;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Manage Experiences')] class extends Component {
    use WithFileUploads;

    public string $search = '';
    public string $typeFilter = '';

    public ?int $editingId = null;
    public string $type = 'work';
    public array $title = ['en' => '', 'cs' => ''];
    public array $subtitle = ['en' => '', 'cs' => ''];
    public array $content = ['en' => '', 'cs' => ''];
    public string $year = '';
    public $imageFile = null;
    public string $image_path = '';
    public array $links = [];
    public array $selectedBadgeIds = [];

    public ?int $deletingId = null;

    #[Computed]
    public function experiences(): \Illuminate\Support\Collection
    {
        return Experience::query()
            ->when($this->search, fn ($q) => $q->whereRaw("title->>'en' ILIKE ?", ["%{$this->search}%"]))
            ->when($this->typeFilter, fn ($q) => $q->where('type', $this->typeFilter))
            ->orderBy('sort_order')
            ->orderByRaw("title->>'en'")
            ->get();
    }

    #[Computed]
    public function allBadges(): \Illuminate\Support\Collection
    {
        return Badge::orderBy('name')->get();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->modal('form')->show();
    }

    public function openEdit(int $id): void
    {
        $experience = Experience::with('badges')->findOrFail($id);
        $this->editingId = $id;
        $this->type = $experience->type;
        $this->title = array_merge(['en' => '', 'cs' => ''], $experience->title ?? []);
        $this->subtitle = array_merge(['en' => '', 'cs' => ''], $experience->subtitle ?? []);
        $this->content = array_merge(['en' => '', 'cs' => ''], $experience->content ?? []);
        $this->year = $experience->year ?? '';
        $this->image_path = $experience->image_path ?? '';
        $this->links = $experience->links ?? [];
        $this->selectedBadgeIds = $experience->badges->pluck('id')->toArray();
        $this->modal('form')->show();
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
            'type' => ['required', 'in:work,life'],
            'title' => ['required', 'array'],
            'title.en' => ['required', 'string', 'max:255'],
            'title.cs' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'array'],
            'subtitle.en' => ['nullable', 'string', 'max:255'],
            'subtitle.cs' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'array'],
            'content.en' => ['nullable', 'string'],
            'content.cs' => ['nullable', 'string'],
            'year' => ['nullable', 'string', 'max:50'],
            'imageFile' => ['nullable', 'image', 'max:2048'],
            'links' => ['nullable', 'array'],
            'links.*.url' => ['nullable', 'url', 'max:500'],
            'links.*.alt' => ['nullable', 'string', 'max:100'],
            'links.*.img_url' => ['nullable', 'url', 'max:500'],
            'selectedBadgeIds' => ['nullable', 'array'],
            'selectedBadgeIds.*' => ['nullable', 'uuid', 'exists:badges,id'],
        ]);

        if ($this->imageFile) {
            $path = $this->imageFile->store('experiences', 'public');
            $this->image_path = 'storage/' . $path;
        }

        $links = collect($this->links)
            ->filter(fn ($l) => filled($l['url'] ?? ''))
            ->values()
            ->toArray();

        $badgeIds = collect($this->selectedBadgeIds)
            ->filter(fn ($id) => filled($id))
            ->unique()
            ->values()
            ->toArray();

        $data = [
            'type' => $validated['type'],
            'title' => array_filter($validated['title'], fn ($v) => filled($v)),
            'subtitle' => array_filter($validated['subtitle'] ?? [], fn ($v) => filled($v)) ?: null,
            'content' => array_filter($validated['content'] ?? [], fn ($v) => filled($v)) ?: null,
            'year' => $validated['year'],
            'image_path' => $this->image_path ?: null,
            'links' => $links ?: null,
        ];

        if ($this->editingId) {
            $experience = Experience::findOrFail($this->editingId);
            $experience->update($data);
            $experience->badges()->sync($badgeIds);
        } else {
            $experience = Experience::create($data);
            $experience->badges()->sync($badgeIds);
        }

        $this->modal('form')->close();
        $this->resetForm();
        unset($this->experiences);
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->modal('delete')->show();
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            Experience::findOrFail($this->deletingId)->delete();
            $this->deletingId = null;
            $this->modal('delete')->close();
            unset($this->experiences);
        }
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->type = 'work';
        $this->title = ['en' => '', 'cs' => ''];
        $this->subtitle = ['en' => '', 'cs' => ''];
        $this->content = ['en' => '', 'cs' => ''];
        $this->year = '';
        $this->imageFile = null;
        $this->image_path = '';
        $this->links = [];
        $this->selectedBadgeIds = [];
        $this->resetValidation();
    }
}; ?>
```

- [ ] **Step 4: Run tests to verify they pass**

```bash
docker exec portfolio-2-app-1 php artisan test --compact --filter=ExperienceManagementTest
```

Expected: all tests pass.

- [ ] **Step 5: Run pint**

```bash
docker exec portfolio-2-app-1 vendor/bin/pint --dirty --format agent
```

- [ ] **Step 6: Commit**

```bash
git add resources/views/pages/manage/ tests/Feature/ExperienceManagementTest.php
git commit -m "feat: update experience Livewire component for json i18n fields"
```

---

## Task 5: Update the Livewire component — Blade template

**Files:**
- Modify: `resources/views/pages/manage/⚡experiences.blade.php` (template section after `?>`)

- [ ] **Step 1: Update the table to show English title/subtitle**

In the table rows section, change:
```blade
{{-- BEFORE --}}
<flux:table.cell variant="strong">{{ $experience->title }}</flux:table.cell>
{{-- ... --}}
<flux:table.cell>{{ $experience->subtitle ?? '—' }}</flux:table.cell>
```
to:
```blade
{{-- AFTER --}}
<flux:table.cell variant="strong">{{ $experience->title['en'] ?? '—' }}</flux:table.cell>
{{-- ... --}}
<flux:table.cell>{{ $experience->subtitle['en'] ?? '—' }}</flux:table.cell>
```

- [ ] **Step 2: Replace the form fields section with EN/CS tabs**

Replace the entire form inside `<flux:modal name="form" ...>` — specifically the `<form wire:submit="save" class="space-y-4">` contents — with:

```blade
<form wire:submit="save" class="space-y-4">
    {{-- Language tabs --}}
    <div x-data="{ locale: 'en' }">
        <div class="flex gap-1 mb-4 p-1 rounded-lg" style="background: var(--c-surface-raised, rgba(0,0,0,0.08));">
            <button
                type="button"
                x-on:click="locale = 'en'"
                :class="locale === 'en' ? 'shadow-sm font-semibold' : 'opacity-60 hover:opacity-80'"
                class="flex-1 px-3 py-1.5 rounded-md text-sm transition-all"
                style="background: transparent;"
                :style="locale === 'en' ? 'background: var(--c-surface)' : ''"
            >🇬🇧 English</button>
            <button
                type="button"
                x-on:click="locale = 'cs'"
                :class="locale === 'cs' ? 'shadow-sm font-semibold' : 'opacity-60 hover:opacity-80'"
                class="flex-1 px-3 py-1.5 rounded-md text-sm transition-all"
                style="background: transparent;"
                :style="locale === 'cs' ? 'background: var(--c-surface)' : ''"
            >🇨🇿 Czech</button>
        </div>

        {{-- English fields --}}
        <div x-show="locale === 'en'" class="space-y-4">
            <flux:field>
                <flux:label>Title <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
                <flux:input wire:model="title.en" placeholder="e.g. Software Developer" />
                <flux:error name="title.en" />
            </flux:field>

            <flux:field>
                <flux:label>Subtitle</flux:label>
                <flux:input wire:model="subtitle.en" placeholder="e.g. Company name or school" />
                <flux:error name="subtitle.en" />
            </flux:field>

            <flux:field>
                <flux:label>Content</flux:label>
                <flux:textarea wire:model="content.en" placeholder="Markdown supported…" rows="6" />
                <flux:error name="content.en" />
            </flux:field>
        </div>

        {{-- Czech fields --}}
        <div x-show="locale === 'cs'" class="space-y-4">
            <flux:field>
                <flux:label>Title</flux:label>
                <flux:input wire:model="title.cs" placeholder="např. Softwarový vývojář" />
                <flux:error name="title.cs" />
            </flux:field>

            <flux:field>
                <flux:label>Subtitle</flux:label>
                <flux:input wire:model="subtitle.cs" placeholder="např. název firmy nebo školy" />
                <flux:error name="subtitle.cs" />
            </flux:field>

            <flux:field>
                <flux:label>Content</flux:label>
                <flux:textarea wire:model="content.cs" placeholder="Podporuje Markdown…" rows="6" />
                <flux:error name="content.cs" />
            </flux:field>
        </div>
    </div>

    {{-- Non-translatable fields --}}
    <div class="grid grid-cols-2 gap-4">
        <flux:field>
            <flux:label>Type</flux:label>
            <flux:select wire:model="type">
                <flux:select.option value="work">Work</flux:select.option>
                <flux:select.option value="life">Life</flux:select.option>
            </flux:select>
            <flux:error name="type" />
        </flux:field>

        <flux:field>
            <flux:label>Year</flux:label>
            <flux:input wire:model="year" placeholder="e.g. 2022 – present" />
            <flux:error name="year" />
        </flux:field>

        {{-- Image upload --}}
        <flux:field class="col-span-2">
            <flux:label>Image</flux:label>
            <flux:input wire:model="imageFile" type="file" accept="image/*" />
            <flux:error name="imageFile" />
            @if ($image_path && ! $imageFile)
                <p class="text-xs mt-1" style="color: var(--c-muted);">Current: {{ $image_path }}</p>
            @endif
            @if ($imageFile)
                <img src="{{ $imageFile->temporaryUrl() }}" class="mt-2 h-16 rounded object-cover" />
            @endif
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
                            <flux:select.option value="{{ $badge->id }}">{{ $badge->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:button size="sm" variant="subtle" icon="x-mark" wire:click.prevent="removeBadge({{ $i }})" class="btn-muted-icon" />
                </div>
            @endforeach
        </div>
    </div>

    <div class="flex justify-end gap-2 pt-2">
        <flux:button x-on:click="$flux.modal('form').close()">Cancel</flux:button>
        <flux:button type="submit" class="btn-gold">
            {{ $editingId ? 'Save changes' : 'Create' }}
        </flux:button>
    </div>
</form>
```

- [ ] **Step 3: Verify the page loads without errors**

```bash
docker exec portfolio-2-app-1 php artisan test --compact --filter=ExperienceManagementTest
```

Expected: all tests still pass.

- [ ] **Step 4: Run pint**

```bash
docker exec portfolio-2-app-1 vendor/bin/pint --dirty --format agent
```

- [ ] **Step 5: Commit**

```bash
git add resources/views/pages/manage/
git commit -m "feat: add EN/CS language tabs to experience edit modal"
```

---

## Task 6: Update docs/database.md

**Files:**
- Modify: `docs/database.md`

- [ ] **Step 1: Update the experiences table**

In `docs/database.md`, find the `experiences` table section and update the `title`, `subtitle` rows and add `content`:

```markdown
| title | json | locale-keyed object — `{"en": "...", "cs": "..."}` |
| subtitle | json | nullable — locale-keyed |
| content | json | nullable — locale-keyed markdown body |
```

- [ ] **Step 2: Commit**

```bash
git add docs/database.md
git commit -m "docs: update experiences schema for json i18n columns"
```

---

## Task 7: Full test run

- [ ] **Step 1: Run the full test suite**

```bash
docker exec portfolio-2-app-1 php artisan test --compact
```

Expected: all tests pass, no failures.
