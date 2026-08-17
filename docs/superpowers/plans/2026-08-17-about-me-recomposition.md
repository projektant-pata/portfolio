# About-me Page Recomposition Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the body of `/about-me` with seven sections whose silhouettes differ on purpose — intro frame, timeline, stats bento, interest cells, show-up strip, reviews carousel and contact band — backed by two new models, two extended ones, and six new admin-editable settings.

**Architecture:** Data comes from Eloquent models (`PathNode`, `Interest`, extended `Stat` and `Experience`, existing `Review`) plus `App\Models\Setting` keys; `AboutMeController` loads them and `resources/views/about-me.blade.php` renders each section inside the existing `<x-portfolio-layout>` / `.portfolio-section` / `<x-portfolio.section-head>` system. Three new public Blade components (`image-slot`, `availability-dot`, `pill-button`) carry the reusable pieces, each with its own CSS file imported by the rewritten `resources/css/pages/about-me.css`. Admin CRUD follows the existing Livewire single-file-component pattern under `resources/views/pages/manage/`. The retired `about_cards` table is dropped only after the new page is verified.

**Tech Stack:** Laravel 13 (PHP 8.5), Livewire 4 single-file components, Flux UI free tier, Tailwind v4 + hand-written CSS with `--c-*` tokens, PostgreSQL 17, Pest 4 (feature, unit and browser tests), Vite.

**Spec:** `docs/superpowers/specs/2026-08-17-about-me-recomposition-design.md` (design handoff it argues from: `docs/handoff/about-me/README.md`, `about.html`, `about.css`)

## Global Constraints

- **Docker.** Every PHP/Artisan command runs inside the app container: `docker exec portfolio-2-app-1 <cmd>`. The container name is `portfolio-2-app-1` (CLAUDE.md's `portfolio-app-1` is stale — do not "fix" the command to match it). Vite builds run on the **host**: `npm run build`.
- **Tests.** `docker exec portfolio-2-app-1 php artisan test --compact --filter=<name>` for a single file/filter; full suite `docker exec portfolio-2-app-1 php artisan test --compact`. Every task ends with tests passing.
- **Pint.** After touching any PHP file: `docker exec portfolio-2-app-1 vendor/bin/pint --dirty --format agent`.
- **Translatable columns are `jsonb`.** Postgres reorders keys, so assert them with `toEqual`, never `toBe`.
- **Locale shape.** Every translatable field is `{"en": …, "cs": …}` read through the per-model `getTranslation(string $field, string $locale, string $fallback = 'en'): string` helper, which is duplicated in each model by convention — duplicate it again in new models; do not extract a trait.
- **Class prefix.** Page CSS classes use the existing `about-me-*` convention, never the handoff's `.ab-*`.
- **Tokens only.** All colours/spacing come from `--c-*`, `--r-*`, `--sp-*`, `--fw-*`, `--t-fast`, `--font-*`. The single new value is the availability green: `#4ADE80` dark / `#16A34A` light.
- **No new dependencies.** No composer or npm package additions.
- **Escaping.** Model text is never rendered unescaped. `{!! !!}` is allowed only for (a) `resources/lang` copy, (b) `Setting` values (owner-authored, same trust level as lang, already the precedent for hero titles), and (c) `Interest::highlightedTitle()` / `Review::highlightedText()`, which escape before wrapping.
- **Empty renders nothing.** Every section renders no markup at all — not an empty frame — when its collection or its `Setting` copy is empty. `/about-me` must return 200 on a freshly migrated, unseeded database.
- **PHP style.** Explicit return types and param type hints, curly braces always, constructor property promotion, PHPDoc array shapes over inline comments.
- **Czech copy** is drafted for every new lang string and every seeded row (owner reviews it later); never leave `cs` empty where `en` exists.

---

## File Structure

**Created**

| File | Responsibility |
|---|---|
| `database/migrations/2026_08_17_000001_create_path_nodes_table.php` | timeline table |
| `database/migrations/2026_08_17_000002_create_interests_table.php` | off-screen cells table |
| `database/migrations/2026_08_17_000003_add_tier_and_note_to_stats_table.php` | bento tiering |
| `database/migrations/2026_08_17_000004_add_show_on_about_to_experiences_table.php` | show-up selection |
| `database/migrations/2026_08_17_000005_drop_about_cards_table.php` | retires `about_cards` (Task 8 only) |
| `app/Models/PathNode.php`, `app/Models/Interest.php` | new models |
| `database/factories/PathNodeFactory.php`, `database/factories/InterestFactory.php` | test data |
| `database/seeders/PathNodeSeeder.php`, `database/seeders/InterestSeeder.php` | transplanted copy |
| `resources/views/components/portfolio/image-slot.blade.php` | `<img>` or striped placeholder plate |
| `resources/views/components/portfolio/availability-dot.blade.php` | pulsing green dot |
| `resources/views/components/portfolio/pill-button.blade.php` | public gold/ghost pill link |
| `resources/css/components/image-slot.css`, `availability-dot.css`, `pill-button.css` | one file per component |
| `resources/views/pages/manage/⚡path-nodes.blade.php`, `⚡interests.blade.php` | admin CRUD |
| `tests/Unit/InterestHighlightTest.php` | escaping, no DB |
| `tests/Feature/PathNodesManagementTest.php`, `tests/Feature/InterestsManagementTest.php` | admin CRUD tests |
| `tests/Feature/AboutContentTest.php` | model/casts/tier/setting-shape coverage |

**Modified**

| File | Change |
|---|---|
| `app/Models/Stat.php` | `tier`, `note` fillable/casts, `TIERS` const |
| `app/Models/Experience.php` | `show_on_about` fillable/cast |
| `app/Models/Setting.php` | `list()` docblock relaxed to `array<int, mixed>` |
| `app/Http/Controllers/AboutMeController.php` | loads five collections |
| `resources/views/about-me.blade.php` | rewritten body |
| `resources/css/pages/about-me.css` | rewritten |
| `resources/css/app.css` | `--color-available` + alias + light override |
| `config/portfolio.php` | `hero_images.portrait`, `cv_path` |
| `resources/lang/{en,cs}/pages/about-me.php` | new section copy, `card*` keys removed |
| `database/seeders/{Setting,Stat,Experience,Database}Seeder.php` | new keys, tiers, flags, registration |
| `resources/views/pages/manage/⚡{stats,experiences,site-content}.blade.php` | new fields |
| `routes/web.php`, `resources/views/pages/⚡dashboard.blade.php`, `resources/views/layouts/app/sidebar.blade.php` | +2 manage entries, −1 |
| `tests/Feature/AboutMePageTest.php` | rewritten |
| `tests/Feature/{SiteContentManagementTest,ExperienceManagementTest,SettingTest,DatabaseSeederTest}.php` | extended / de-`AboutCard`ed |
| `tests/Browser/{PublicPagesTest,LightThemeTest}.php` | new page assertions |
| `CLAUDE.md` | manage-page list corrected |

**Deleted (Task 8 only)**

`app/Models/AboutCard.php`, `database/factories/AboutCardFactory.php`, `database/seeders/AboutCardSeeder.php`, `resources/views/pages/manage/⚡about-cards.blade.php`.

## Plan-level decisions (deviations from the spec, already ruled)

1. **`--c-available` is an alias, not a raw token.** The spec says the literal joins the `@theme` block; the project's rule is that `@theme` holds `--color-*` values and `--c-*` are aliases with no values of their own. So: `--color-available: #4ADE80` in `@theme`, `--c-available: var(--color-available)` in `:root`, and `--color-available: #16A34A` in the `html:not(.dark)` block (the project's light selector — there is no `.light` class).
2. **The contact band carries its own `h2`, not a `<x-portfolio.section-head>`.** The handoff's band is a self-contained composition and the spec's `Setting` key list gives it a title and a sub-line but no eyebrow or note. Adding a section head would require copy nobody specified.
3. **No browser assertion for `prefers-reduced-motion`.** `pest-plugin-browser` exposes `inDarkMode()` / `inLightMode()` / locale / timezone / geolocation context options but no `reducedMotion` emulation, so the spec's third browser assertion cannot be written honestly. The suppression is enforced structurally: the pulse animation exists **only** inside `@media (prefers-reduced-motion: no-preference)`. The browser test asserts the animation is present under default motion.
4. **`about_facts` is edited as one textarea of `label | value` lines** in `⚡site-content`, one line per fact — the spec asks for "a repeatable editor" and this is the smallest thing that is one, matching the existing newline-split role-list editor rather than inventing a nested-array Livewire form.

---

### Task 1: Schema, models and factories

**Files:**
- Create: `database/migrations/2026_08_17_000001_create_path_nodes_table.php`
- Create: `database/migrations/2026_08_17_000002_create_interests_table.php`
- Create: `database/migrations/2026_08_17_000003_add_tier_and_note_to_stats_table.php`
- Create: `database/migrations/2026_08_17_000004_add_show_on_about_to_experiences_table.php`
- Create: `app/Models/PathNode.php`, `app/Models/Interest.php`
- Create: `database/factories/PathNodeFactory.php`, `database/factories/InterestFactory.php`
- Modify: `app/Models/Stat.php`, `app/Models/Experience.php`, `app/Models/Setting.php`
- Modify: `database/factories/StatFactory.php`, `database/factories/ExperienceFactory.php`
- Test: `tests/Unit/InterestHighlightTest.php`, `tests/Feature/AboutContentTest.php`

**Interfaces:**
- Produces: `App\Models\PathNode` (`year`, `title`, `body` translatable; `is_now` bool; `sort_order` int; `getTranslation()`), `App\Models\Interest` (`title`, `highlight`, `body`, `meta` translatable; `image_path` nullable string; `sort_order` int; `getTranslation()`; `highlightedTitle(string $locale): string`), `Stat::TIERS`, `Stat->tier`, `Stat->note`, `Experience->show_on_about`.
- Consumes: nothing.

- [ ] **Step 1: Write the failing tests**

Create `tests/Unit/InterestHighlightTest.php` (no database — `tests/Unit` does not get `RefreshDatabase`):

```php
<?php

use App\Models\Interest;

test('the highlighted phrase is wrapped in a span', function () {
    $interest = new Interest([
        'title' => ['en' => 'Chess since 2nd grade'],
        'highlight' => ['en' => '2nd grade'],
    ]);

    expect($interest->highlightedTitle('en'))->toBe('Chess since <span>2nd grade</span>');
});

test('a title is escaped before the highlight is wrapped', function () {
    $interest = new Interest([
        'title' => ['en' => '<script>alert(1)</script> chess'],
        'highlight' => ['en' => 'chess'],
    ]);

    expect($interest->highlightedTitle('en'))
        ->toBe('&lt;script&gt;alert(1)&lt;/script&gt; <span>chess</span>')
        ->not->toContain('<script>');
});

test('a highlight that is not in the title changes nothing', function () {
    $interest = new Interest([
        'title' => ['en' => 'Rock, loud'],
        'highlight' => ['en' => 'jazz'],
    ]);

    expect($interest->highlightedTitle('en'))->toBe('Rock, loud');
});

test('an empty highlight returns the escaped title', function () {
    $interest = new Interest([
        'title' => ['en' => 'Rock & roll'],
        'highlight' => [],
    ]);

    expect($interest->highlightedTitle('en'))->toBe('Rock &amp; roll');
});
```

Create `tests/Feature/AboutContentTest.php`:

```php
<?php

use App\Models\Experience;
use App\Models\Interest;
use App\Models\PathNode;
use App\Models\Stat;

test('a path node stores both locales and the now flag', function () {
    $node = PathNode::factory()->create([
        'year' => ['en' => 'Now', 'cs' => 'Teď'],
        'title' => ['en' => 'Freelance', 'cs' => 'Freelance'],
        'body' => ['en' => 'Paid client work.', 'cs' => 'Placená práce pro klienty.'],
        'is_now' => true,
        'sort_order' => 4,
    ]);

    expect($node->fresh()->year)->toEqual(['en' => 'Now', 'cs' => 'Teď'])
        ->and($node->fresh()->is_now)->toBeTrue()
        ->and($node->getTranslation('title', 'cs'))->toBe('Freelance')
        ->and($node->getTranslation('body', 'fr'))->toBe('Paid client work.');
});

test('an interest stores its image path and translatable meta', function () {
    $interest = Interest::factory()->create([
        'meta' => ['en' => '1684 elo', 'cs' => '1684 elo'],
        'image_path' => 'images/about/chess.webp',
    ]);

    expect($interest->fresh()->meta)->toEqual(['en' => '1684 elo', 'cs' => '1684 elo'])
        ->and($interest->fresh()->image_path)->toBe('images/about/chess.webp');
});

test('a stat defaults to the normal tier and accepts hero with a note', function () {
    $plain = Stat::factory()->create();
    $hero = Stat::factory()->hero()->create();

    expect($plain->fresh()->tier)->toBe('normal')
        ->and($plain->fresh()->note)->toBeNull()
        ->and($hero->fresh()->tier)->toBe('hero')
        ->and($hero->getTranslation('note', 'en'))->not->toBe('');
});

test('stats group by tier without losing sort order', function () {
    Stat::factory()->create(['tier' => 'soft', 'sort_order' => 1]);
    Stat::factory()->hero()->create(['sort_order' => 0]);

    $tiers = Stat::orderBy('sort_order')->get()->pluck('tier')->all();

    expect($tiers)->toBe(['hero', 'soft'])
        ->and(Stat::TIERS)->toBe(['hero', 'normal', 'soft']);
});

test('an experience defaults to hidden on the about page', function () {
    expect(Experience::factory()->create()->fresh()->show_on_about)->toBeFalse()
        ->and(Experience::factory()->create(['show_on_about' => true])->fresh()->show_on_about)->toBeTrue();
});
```

- [ ] **Step 2: Run the tests to verify they fail**

```bash
docker exec portfolio-2-app-1 php artisan test --compact --filter="InterestHighlight|AboutContent"
```
Expected: FAIL — `Class "App\Models\Interest" not found`.

- [ ] **Step 3: Write the migrations**

Generate the files with Artisan so the timestamps are real, then rename them to the exact names in **Files** above (the numbered suffixes keep the drop migration in Task 8 ordered after these):

```bash
docker exec portfolio-2-app-1 php artisan make:migration create_path_nodes_table --no-interaction
docker exec portfolio-2-app-1 php artisan make:migration create_interests_table --no-interaction
docker exec portfolio-2-app-1 php artisan make:migration add_tier_and_note_to_stats_table --no-interaction
docker exec portfolio-2-app-1 php artisan make:migration add_show_on_about_to_experiences_table --no-interaction
```

`2026_08_17_000001_create_path_nodes_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('path_nodes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->jsonb('year')->nullable();   // {en,cs} — "2019" but also "Now"/"Teď"
            $table->jsonb('title')->nullable();  // {en,cs}
            $table->jsonb('body')->nullable();   // {en,cs}
            $table->boolean('is_now')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('path_nodes');
    }
};
```

`2026_08_17_000002_create_interests_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->jsonb('title')->nullable();      // {en,cs}
            $table->jsonb('highlight')->nullable();  // {en,cs} — phrase rendered gold inside the title
            $table->jsonb('body')->nullable();       // {en,cs}
            $table->jsonb('meta')->nullable();       // {en,cs} — monospace footer line
            $table->string('image_path')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interests');
    }
};
```

`2026_08_17_000003_add_tier_and_note_to_stats_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stats', function (Blueprint $table) {
            $table->string('tier')->default('normal'); // hero|normal|soft — bento weight
            $table->jsonb('note')->nullable();         // {en,cs} — hero tiles' supporting line
        });
    }

    public function down(): void
    {
        Schema::table('stats', function (Blueprint $table) {
            $table->dropColumn(['tier', 'note']);
        });
    }
};
```

`2026_08_17_000004_add_show_on_about_to_experiences_table.php`:

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
            $table->boolean('show_on_about')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('experiences', function (Blueprint $table) {
            $table->dropColumn('show_on_about');
        });
    }
};
```

- [ ] **Step 4: Write the models**

`app/Models/PathNode.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PathNode extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'year',
        'title',
        'body',
        'is_now',
        'sort_order',
    ];

    protected $casts = [
        'year' => 'array',
        'title' => 'array',
        'body' => 'array',
        'is_now' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function getTranslation(string $field, string $locale, string $fallback = 'en'): string
    {
        $value = $this->{$field};

        if (! is_array($value)) {
            return '';
        }

        return $value[$locale] ?? $value[$fallback] ?? '';
    }
}
```

`app/Models/Interest.php` — same shape plus the highlight helper, which mirrors `Review::highlightedText()` (escape first, then wrap, so the phrase can never break out of the markup):

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interest extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'title',
        'highlight',
        'body',
        'meta',
        'image_path',
        'sort_order',
    ];

    protected $casts = [
        'title' => 'array',
        'highlight' => 'array',
        'body' => 'array',
        'meta' => 'array',
        'sort_order' => 'integer',
    ];

    public function getTranslation(string $field, string $locale, string $fallback = 'en'): string
    {
        $value = $this->{$field};

        if (! is_array($value)) {
            return '';
        }

        return $value[$locale] ?? $value[$fallback] ?? '';
    }

    /**
     * HTML-safe title with the `highlight` phrase (if present and found
     * verbatim) wrapped in a <span> for the gold accent. Escaping happens
     * before the substring match, so the highlight can never break out of
     * the surrounding markup.
     */
    public function highlightedTitle(string $locale): string
    {
        $title = e($this->getTranslation('title', $locale));
        $highlight = $this->getTranslation('highlight', $locale);

        if ($highlight === '') {
            return $title;
        }

        $escapedHighlight = e($highlight);

        if (! str_contains($title, $escapedHighlight)) {
            return $title;
        }

        return str_replace($escapedHighlight, '<span>'.$escapedHighlight.'</span>', $title);
    }
}
```

In `app/Models/Stat.php`: add `'tier'` and `'note'` to `$fillable`, `'note' => 'array'` to `$casts`, and the tier list as a public const right below `CAREER_START_YEAR`:

```php
    /** Bento weights: hero tiles span two columns and carry a note; soft tiles are the jokes. */
    public const TIERS = ['hero', 'normal', 'soft'];
```

In `app/Models/Experience.php`: add `'show_on_about'` to `$fillable` and `'show_on_about' => 'boolean'` to `$casts`.

In `app/Models/Setting.php`: relax the `list()` docblock — `about_facts` returns a list of `{label, value}` maps, so the declared `list<string>` is a lie. Behaviour is unchanged:

```php
    /**
     * Localized list value for a key (e.g. the rotating hero roles, or the
     * about page's fact strip, whose entries are {label, value} maps),
     * falling back to the `en` list, then an empty array.
     *
     * @return array<int, mixed>
     */
```

- [ ] **Step 5: Write the factories**

`database/factories/PathNodeFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\PathNode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PathNode>
 */
class PathNodeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'year' => ['en' => (string) $this->faker->year()],
            'title' => ['en' => $this->faker->words(3, true)],
            'body' => ['en' => $this->faker->sentence()],
            'is_now' => false,
            'sort_order' => 0,
        ];
    }

    public function translated(): static
    {
        return $this->state(fn () => [
            'year' => ['en' => 'Now', 'cs' => 'Teď'],
            'title' => ['en' => $this->faker->words(3, true), 'cs' => $this->faker->words(3, true)],
            'body' => ['en' => $this->faker->sentence(), 'cs' => $this->faker->sentence()],
        ]);
    }

    public function now(): static
    {
        return $this->state(fn () => ['is_now' => true]);
    }
}
```

`database/factories/InterestFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\Interest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Interest>
 */
class InterestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => ['en' => $this->faker->words(3, true)],
            'highlight' => [],
            'body' => ['en' => $this->faker->sentence()],
            'meta' => ['en' => $this->faker->words(2, true)],
            'image_path' => null,
            'sort_order' => 0,
        ];
    }

    public function translated(): static
    {
        return $this->state(fn () => [
            'title' => ['en' => $this->faker->words(3, true), 'cs' => $this->faker->words(3, true)],
            'body' => ['en' => $this->faker->sentence(), 'cs' => $this->faker->sentence()],
            'meta' => ['en' => $this->faker->words(2, true), 'cs' => $this->faker->words(2, true)],
        ]);
    }
}
```

In `database/factories/StatFactory.php` add, after `translated()`:

```php
    public function hero(): static
    {
        return $this->state(fn () => [
            'tier' => 'hero',
            'note' => ['en' => $this->faker->sentence(), 'cs' => $this->faker->sentence()],
        ]);
    }

    public function soft(): static
    {
        return $this->state(fn () => ['tier' => 'soft']);
    }
```

and `'tier' => 'normal'`, `'note' => null` to `definition()`. In `database/factories/ExperienceFactory.php` add `'show_on_about' => false` to `definition()` and:

```php
    public function onAbout(): static
    {
        return $this->state(fn () => ['show_on_about' => true]);
    }
```

- [ ] **Step 6: Run the tests to verify they pass**

```bash
docker exec portfolio-2-app-1 php artisan test --compact --filter="InterestHighlight|AboutContent"
docker exec portfolio-2-app-1 vendor/bin/pint --dirty --format agent
```
Expected: PASS (9 tests).

- [ ] **Step 7: Commit**

```bash
git add app/Models database/migrations database/factories tests/Unit tests/Feature/AboutContentTest.php
git commit -m "feat(about-me): add path node and interest models, stat tiers and the about flag"
```

---

### Task 2: Seeders, settings and config

**Files:**
- Create: `database/seeders/PathNodeSeeder.php`, `database/seeders/InterestSeeder.php`
- Modify: `database/seeders/SettingSeeder.php`, `database/seeders/StatSeeder.php`, `database/seeders/ExperienceSeeder.php`, `database/seeders/DatabaseSeeder.php`
- Modify: `config/portfolio.php`
- Test: `tests/Feature/DatabaseSeederTest.php`, `tests/Feature/SettingTest.php`

**Interfaces:**
- Consumes: `PathNode`, `Interest`, `Stat::TIERS`, `Experience->show_on_about` from Task 1.
- Produces: `Setting` keys `about_intro_lead` (string), `about_intro_body` (list of 2 strings), `about_facts` (list of `['label' => string, 'value' => string]`), `about_available` (string, empty = unavailable), `about_cta_title` (string), `about_cta_sub` (string); `config('portfolio.hero_images.portrait')` (string, default `''`); `config('portfolio.cv_path')` (string, default `''`); five seeded path nodes; three seeded interests; four `show_on_about` experiences.

- [ ] **Step 1: Write the failing tests**

Append to `tests/Feature/DatabaseSeederTest.php`:

```php
test('the database seeder fills the about page with path nodes, interests and settings', function () {
    $this->seed(DatabaseSeeder::class);

    expect(PathNode::count())->toBe(5)
        ->and(PathNode::where('is_now', true)->count())->toBe(1)
        ->and(Interest::count())->toBe(3)
        ->and(Experience::where('show_on_about', true)->count())->toBeGreaterThanOrEqual(4)
        ->and(Stat::where('tier', 'hero')->count())->toBe(2)
        ->and(Stat::where('tier', 'soft')->count())->toBe(2);
});

test('every seeded about row carries both locales', function () {
    $this->seed(DatabaseSeeder::class);

    PathNode::all()->each(function (PathNode $node) {
        expect($node->year)->toHaveKeys(['en', 'cs'])
            ->and($node->title)->toHaveKeys(['en', 'cs'])
            ->and($node->body)->toHaveKeys(['en', 'cs']);
    });

    Interest::all()->each(function (Interest $interest) {
        expect($interest->title)->toHaveKeys(['en', 'cs'])
            ->and($interest->body)->toHaveKeys(['en', 'cs'])
            ->and($interest->meta)->toHaveKeys(['en', 'cs']);
    });
});

test('the hero stats carry a supporting note in both locales', function () {
    $this->seed(DatabaseSeeder::class);

    Stat::where('tier', 'hero')->get()->each(
        fn (Stat $stat) => expect($stat->note)->toHaveKeys(['en', 'cs'])
    );
});
```

with the matching `use App\Models\{Experience, Interest, PathNode, Stat};` imports at the top of the file.

Append to `tests/Feature/SettingTest.php`:

```php
test('the about fact strip reads back as a list of label/value maps', function () {
    $this->seed(SettingSeeder::class);

    $facts = Setting::list('about_facts', 'en');

    expect($facts)->toBeArray()->not->toBeEmpty();

    foreach ($facts as $fact) {
        expect($fact)->toHaveKeys(['label', 'value']);
    }
});

test('the about intro body is a two-paragraph list in both locales', function () {
    $this->seed(SettingSeeder::class);

    expect(Setting::list('about_intro_body', 'en'))->toHaveCount(2)
        ->and(Setting::list('about_intro_body', 'cs'))->toHaveCount(2)
        ->and(Setting::text('about_available', 'cs'))->not->toBe('');
});
```

(check the file's existing imports; add `use Database\Seeders\SettingSeeder;` if missing).

- [ ] **Step 2: Run the tests to verify they fail**

```bash
docker exec portfolio-2-app-1 php artisan test --compact --filter="DatabaseSeeder|SettingTest"
```
Expected: FAIL — `Class "Database\Seeders\PathNodeSeeder" not found` / counts are 0.

- [ ] **Step 3: Write the new seeders**

`database/seeders/PathNodeSeeder.php` — the copy transplanted from `about_cards` card4, rewritten clean (the old text carries typos; do not copy it verbatim):

```php
<?php

namespace Database\Seeders;

use App\Models\PathNode;
use Illuminate\Database\Seeder;

/**
 * The five turns of the about page's timeline. Re-runnable: clears the table
 * first, so `db:seed --class=PathNodeSeeder` is idempotent.
 */
class PathNodeSeeder extends Seeder
{
    public function run(): void
    {
        PathNode::query()->delete();

        $nodes = [
            [
                'year' => ['en' => '2019', 'cs' => '2019'],
                'title' => ['en' => 'Back to elementary school', 'cs' => 'Zpátky na základní školu'],
                'body' => [
                    'en' => 'Health issues pulled me out of gymnasium. The school I returned to happened to teach extended IT — the luckiest bad break I have had.',
                    'cs' => 'Kvůli zdravotním problémům jsem musel odejít z gymnázia. Základka, na kterou jsem se vrátil, měla rozšířenou výuku IT — nejšťastnější smůla, jakou jsem zažil.',
                ],
                'is_now' => false,
            ],
            [
                'year' => ['en' => '2020', 'cs' => '2020'],
                'title' => ['en' => 'First website, then no website', 'cs' => 'První web, pak žádný web'],
                'body' => [
                    'en' => 'I fell for the idea that you can just build things. Shipped my first site, lost it to a dead hosting account, and learned about backups the expensive way.',
                    'cs' => 'Nadchla mě představa, že věci se dají prostě postavit. Vydal jsem první web, přišel o něj se zrušeným hostingem a naučil se o zálohách tou dražší cestou.',
                ],
                'is_now' => false,
            ],
            [
                'year' => ['en' => '2022', 'cs' => '2022'],
                'title' => ['en' => 'SPŠE Pardubice', 'cs' => 'SPŠE Pardubice'],
                'body' => [
                    'en' => 'Chose the school that would keep pushing me. Backend clicked here — Java first, then everything around it.',
                    'cs' => 'Vybral jsem si školu, která mě nenechá usnout. Tady mi zapadl backend — nejdřív Java, pak všechno kolem ní.',
                ],
                'is_now' => false,
            ],
            [
                'year' => ['en' => '2024', 'cs' => '2024'],
                'title' => ['en' => 'Competitions and a jury to face', 'cs' => 'Soutěže a porota před očima'],
                'body' => [
                    'en' => 'Hackathon wins, a national final, and the first time I had to explain my own architecture to strangers under a clock.',
                    'cs' => 'Výhry na hackathonech, celostátní finále a poprvé jsem musel vysvětlovat vlastní architekturu cizím lidem s hodinami v zádech.',
                ],
                'is_now' => false,
            ],
            [
                'year' => ['en' => 'Now', 'cs' => 'Teď'],
                'title' => ['en' => 'Freelance and Prezz', 'cs' => 'Freelance a Prezz'],
                'body' => [
                    'en' => 'Paid client work, a web dev group to help lead, and a platform in production that people use without knowing my name.',
                    'cs' => 'Placená práce pro klienty, vývojářská skupina, kterou pomáhám vést, a platforma v produkci, kterou lidé používají, aniž by znali moje jméno.',
                ],
                'is_now' => true,
            ],
        ];

        foreach ($nodes as $i => $node) {
            PathNode::create([...$node, 'sort_order' => $i]);
        }
    }
}
```

`database/seeders/InterestSeeder.php` — card2's copy, rewritten, split into three cells. `image_path` stays `null` so the placeholder plate renders until the photos land:

```php
<?php

namespace Database\Seeders;

use App\Models\Interest;
use Illuminate\Database\Seeder;

/**
 * The three "off the screen" cells. `image_path` is null until the real
 * photographs land — the page renders the placeholder plate meanwhile.
 * Re-runnable: clears the table first.
 */
class InterestSeeder extends Seeder
{
    public function run(): void
    {
        Interest::query()->delete();

        $interests = [
            [
                'title' => ['en' => 'Chess since 2nd grade', 'cs' => 'Šachy od druhé třídy'],
                'highlight' => ['en' => '2nd grade', 'cs' => 'druhé třídy'],
                'body' => [
                    'en' => 'Local tournaments against high-schoolers taught me to think three moves out and to lose without falling apart. The knight is the piece.',
                    'cs' => 'Místní turnaje proti středoškolákům mě naučily myslet tři tahy dopředu a prohrávat bez zhroucení. Jezdec je ta figura.',
                ],
                'meta' => ['en' => '1684 elo · patience, strategy', 'cs' => '1684 elo · trpělivost, strategie'],
            ],
            [
                'title' => ['en' => 'Rock, loud', 'cs' => 'Rock, nahlas'],
                'highlight' => ['en' => 'loud', 'cs' => 'nahlas'],
                'body' => [
                    'en' => 'Most of my code was written with a guitar in one ear. Live shows whenever the calendar lets me.',
                    'cs' => 'Většina mého kódu vznikla s kytarou v jednom uchu. Koncerty vždycky, když to kalendář dovolí.',
                ],
                'meta' => ['en' => 'Concerts · playlists · headphones', 'cs' => 'Koncerty · playlisty · sluchátka'],
            ],
            [
                'title' => ['en' => 'Homelab and catfish', 'cs' => 'Homelab a sumci'],
                'highlight' => ['en' => 'catfish', 'cs' => 'sumci'],
                'body' => [
                    'en' => 'A rack of Raspberry Pis I break on purpose, and yes — catfish. Both are hobbies I get asked about more than my job.',
                    'cs' => 'Stojan Raspberry Pi, které rozbíjím schválně, a ano — sumci. Na obojí se mě lidé ptají víc než na práci.',
                ],
                'meta' => ['en' => 'Sensors · self-hosting · fishing', 'cs' => 'Senzory · self-hosting · rybaření'],
            ],
        ];

        foreach ($interests as $i => $interest) {
            Interest::create([...$interest, 'image_path' => null, 'sort_order' => $i]);
        }
    }
}
```

- [ ] **Step 4: Extend the existing seeders**

In `database/seeders/SettingSeeder.php`, add these six entries to the `$settings` array (the seeder deletes keys it no longer defines, so they must live here):

```php
            'about_intro_lead' => [
                'en' => 'I build backends that outlive the demo, and I would rather <em>ship something small</em> than describe something big.',
                'cs' => 'Stavím backendy, které přežijí demo, a radši <em>vydám něco malého</em>, než abych popisoval něco velkého.',
            ],
            'about_intro_body' => [
                'en' => [
                    'I am Richard Hývl, a student at <b>SPŠE Pardubice</b> and a freelancer. Most of my week is Java and PHP — Spring Boot on one side, Laravel on the other — plus a web dev group called <b>Prezz</b> that I help lead.',
                    'What drives me is curiosity and the wish to be useful to the people who use what I build. I learn fastest by breaking things I own, which is why the homelab exists.',
                ],
                'cs' => [
                    'Jsem Richard Hývl, student <b>SPŠE Pardubice</b> a freelancer. Většinu týdne tvoří Java a PHP — na jedné straně Spring Boot, na druhé Laravel — plus vývojářská skupina <b>Prezz</b>, kterou pomáhám vést.',
                    'Pohání mě zvědavost a chuť být užitečný lidem, kteří moje věci používají. Nejrychleji se učím tím, že rozbíjím vlastní věci — proto existuje homelab.',
                ],
            ],
            'about_facts' => [
                'en' => [
                    ['label' => 'Based in', 'value' => 'Pardubice, CZ'],
                    ['label' => 'Focus', 'value' => 'Backend & web'],
                    ['label' => 'Languages', 'value' => 'Czech, English'],
                    ['label' => 'Status', 'value' => 'Open to work'],
                ],
                'cs' => [
                    ['label' => 'Bydliště', 'value' => 'Pardubice, ČR'],
                    ['label' => 'Zaměření', 'value' => 'Backend a web'],
                    ['label' => 'Jazyky', 'value' => 'Čeština, angličtina'],
                    ['label' => 'Stav', 'value' => 'Otevřen nabídkám'],
                ],
            ],
            'about_available' => ['en' => 'Available for freelance work', 'cs' => 'Volný pro freelance zakázky'],
            'about_cta_title' => ['en' => 'Have something that needs <em>building</em>?', 'cs' => 'Máte něco, co je potřeba <em>postavit</em>?'],
            'about_cta_sub' => [
                'en' => 'Client work, a school project, or an idea you want a second opinion on — write and you will hear back.',
                'cs' => 'Zakázka, školní projekt nebo nápad, na který chcete druhý názor — napište a ozvu se.',
            ],
```

In `database/seeders/StatSeeder.php`, add `tier` and `note` to the two hero entries and `tier` to the two jokes, and pass them through in the `Stat::create()` call. The bento reads `sort_order`, so the hero tiles must sit first:

```php
        $stats = [
            [
                'value' => ['en' => '5+', 'cs' => '5+'],
                'text' => ['en' => 'Projects shipped', 'cs' => 'Vydaných projektů'],
                'tier' => 'hero',
                'note' => [
                    'en' => 'Client work, school projects and one platform running in production.',
                    'cs' => 'Klientské zakázky, školní projekty a jedna platforma běžící v produkci.',
                ],
            ],
            [
                'value' => null,
                'source' => 'years_experience',
                'text' => ['en' => 'Years writing code', 'cs' => 'Let u kódu'],
                'tier' => 'hero',
                'note' => [
                    'en' => 'Started in 2020 and have not found a reason to stop.',
                    'cs' => 'Začal jsem v roce 2020 a zatím nenašel důvod přestat.',
                ],
            ],
            ['value' => ['en' => 'Junior', 'cs' => 'Junior'], 'text' => ['en' => 'Professional Level', 'cs' => 'Profesionální úroveň']],
            ['value' => null, 'source' => 'age', 'text' => ['en' => 'Years old', 'cs' => 'Let věku']],
            ['value' => ['en' => '2', 'cs' => '2'], 'text' => ['en' => 'Countries Reached', 'cs' => 'Země dosaženy']],
            ['value' => ['en' => '18', 'cs' => '18'], 'value_id' => 'github-repos', 'text' => ['en' => 'GitHub repositories', 'cs' => 'GitHub repozitářů']],
            ['value' => ['en' => 'Loading..', 'cs' => 'Načítání..'], 'value_id' => 'elo', 'text' => ['en' => 'Highest chess elo', 'cs' => 'Nejvyšší šachové elo']],
            ['value' => ['en' => '4', 'cs' => '4'], 'text' => ['en' => 'Hackathons won', 'cs' => 'Vyhraných hackathonů']],
            ['value' => ['en' => '♞', 'cs' => '♞'], 'text' => ['en' => 'Favorite piece', 'cs' => 'Nejoblíbenější figura']],
            ['value' => ['en' => '∞', 'cs' => '∞'], 'text' => ['en' => 'Coffee consumed', 'cs' => 'Vypitých šálků kávy'], 'tier' => 'soft'],
            ['value' => ['en' => '404', 'cs' => '404'], 'text' => ['en' => 'Hours slept, not found', 'cs' => 'Hodin spánku, nenalezeno'], 'tier' => 'soft'],
        ];

        foreach ($stats as $i => $stat) {
            Stat::create([
                'value' => $stat['value'],
                'text' => $stat['text'],
                'value_id' => $stat['value_id'] ?? null,
                'source' => $stat['source'] ?? null,
                'tier' => $stat['tier'] ?? 'normal',
                'note' => $stat['note'] ?? null,
                'sort_order' => $i,
            ]);
        }
```

In `database/seeders/ExperienceSeeder.php`:

1. Add `'show_on_about' => true` to the existing `AstroPi Hackathon 2024` entry (badge `competition`) and to `Regional chess competetion of schools` (badge `competition`).
2. Add two new entries carrying card5's volunteering copy, rewritten, both flagged, using existing badge slugs (`certificate` for PEER, `hardware` is wrong here — use `it`):

```php
            [
                'type' => 'life',
                'is_special' => false,
                'show_on_about' => true,
                'sort_order' => 11,
                'year' => ['en' => '2023 — now', 'cs' => '2023 — nyní'],
                'title' => ['en' => 'PEER program', 'cs' => 'Program PEER'],
                'subtitle' => [
                    'en' => 'Trained to teach teenagers about drugs and bullying, then presented to my own peers.',
                    'cs' => 'Vyškolen, abych učil teenagery o drogách a šikaně, a pak jsem prezentoval vlastním vrstevníkům.',
                ],
                'content' => ['en' => '', 'cs' => ''],
                'badges' => ['certificate'],
            ],
            [
                'type' => 'life',
                'is_special' => false,
                'show_on_about' => true,
                'sort_order' => 12,
                'year' => ['en' => '2023', 'cs' => '2023'],
                'title' => ['en' => 'Czech Day Against Cancer', 'cs' => 'Český den proti rakovině'],
                'subtitle' => [
                    'en' => 'Sold flower badges on the street for cancer patients. The best public-speaking practice there is.',
                    'cs' => 'Prodával jsem květinové odznaky na ulici pro pacienty s rakovinou. Nejlepší trénink veřejného projevu, jaký existuje.',
                ],
                'content' => ['en' => '', 'cs' => ''],
                'badges' => ['it'],
            ],
```

3. Add `'show_on_about'` to any entry array that lacks it? No — the column defaults to `false`, and `Experience::create()` ignores absent keys. Leave the other entries untouched.

In `database/seeders/DatabaseSeeder.php`, register the two new seeders after `ReviewSeeder::class` (leave `AboutCardSeeder::class` in place — Task 8 removes it):

```php
            PathNodeSeeder::class,
            InterestSeeder::class,
```

- [ ] **Step 5: Extend the config**

In `config/portfolio.php`, add `'portrait' => ''` to `hero_images` with a comment matching the `experience_dock` precedent, and a new top-level key:

```php
        /* Intro portrait on the about page. Empty until the 900×1200 crop
           lands — the intro renders the placeholder plate meanwhile. */
        'portrait' => '',
```

```php
    /*
    |--------------------------------------------------------------------------
    | CV
    |--------------------------------------------------------------------------
    |
    | Path (relative to public/) of the downloadable CV. The about page's
    | contact band renders its "Download CV" button only when this is set —
    | a download button pointing at nothing is worse than no button.
    |
    */

    'cv_path' => '',
```

- [ ] **Step 6: Run the tests to verify they pass**

```bash
docker exec portfolio-2-app-1 php artisan migrate --no-interaction
docker exec portfolio-2-app-1 php artisan test --compact --filter="DatabaseSeeder|SettingTest"
docker exec portfolio-2-app-1 vendor/bin/pint --dirty --format agent
```
Expected: PASS.

- [ ] **Step 7: Commit**

```bash
git add database/seeders config/portfolio.php tests/Feature/DatabaseSeederTest.php tests/Feature/SettingTest.php
git commit -m "feat(about-me): seed path nodes, interests, stat tiers and the new about settings"
```

---

### Task 3: Public components and their CSS

**Files:**
- Create: `resources/views/components/portfolio/image-slot.blade.php`, `availability-dot.blade.php`, `pill-button.blade.php`
- Create: `resources/css/components/image-slot.css`, `availability-dot.css`, `pill-button.css`
- Modify: `resources/css/app.css`
- Test: `tests/Feature/PortfolioComponentsTest.php`

**Interfaces:**
- Consumes: nothing.
- Produces:
  - `<x-portfolio.image-slot :path="" :caption="" :alt="" :height="" />` — renders `<img class="image-slot-img">` when `path` is non-empty, otherwise `<figure class="image-slot">` with a `<span>` caption; `height` is a CSS length written to the inline custom property `--image-slot-h`.
  - `<x-portfolio.availability-dot />` — `<span class="availability-dot" aria-hidden="true"></span>`.
  - `<x-portfolio.pill-button :href="" variant="gold|ghost" :download="false">label</x-portfolio.pill-button>` — `<a class="pill-button pill-button--gold|--ghost">`.
  - CSS custom property `--c-available`.

- [ ] **Step 1: Write the failing test**

`tests/Feature/PortfolioComponentsTest.php`:

```php
<?php

test('the image slot renders the picture when a path is set', function () {
    $html = $this->blade(
        '<x-portfolio.image-slot path="images/about/chess.webp" caption="chess · 1200×900" alt="Chess board" height="230px" />'
    );

    expect($html)->toContain('images/about/chess.webp')
        ->and($html)->toContain('alt="Chess board"')
        ->and($html)->toContain('--image-slot-h: 230px')
        ->and($html)->not->toContain('class="image-slot"');
});

test('the image slot falls back to a captioned placeholder plate', function () {
    $html = $this->blade('<x-portfolio.image-slot caption="portrait · 900×1200" height="420px" />');

    expect($html)->toContain('class="image-slot"')
        ->and($html)->toContain('portrait · 900×1200')
        ->and($html)->toContain('aria-hidden="true"')
        ->and($html)->not->toContain('<img');
});

test('the image slot escapes a caption', function () {
    $html = $this->blade('<x-portfolio.image-slot caption="<script>x</script>" />');

    expect($html)->not->toContain('<script>');
});

test('the availability dot is decorative', function () {
    $html = $this->blade('<x-portfolio.availability-dot />');

    expect($html)->toContain('availability-dot')
        ->and($html)->toContain('aria-hidden="true"');
});

test('the pill button renders both variants and opts into download only when asked', function () {
    $gold = $this->blade('<x-portfolio.pill-button href="mailto:a@b.c">Write</x-portfolio.pill-button>');
    $ghost = $this->blade('<x-portfolio.pill-button href="/cv.pdf" variant="ghost" :download="true">CV</x-portfolio.pill-button>');

    expect($gold)->toContain('pill-button--gold')
        ->and($gold)->not->toContain('download')
        ->and($ghost)->toContain('pill-button--ghost')
        ->and($ghost)->toContain('download')
        ->and($ghost)->toContain('CV');
});
```

- [ ] **Step 2: Run the test to verify it fails**

```bash
docker exec portfolio-2-app-1 php artisan test --compact --filter=PortfolioComponents
```
Expected: FAIL — `Unable to locate a class or view for component [portfolio.image-slot]`.

- [ ] **Step 3: Write the components**

`resources/views/components/portfolio/image-slot.blade.php`:

```blade
@props([
    'path' => '',
    'caption' => '',
    'alt' => '',
    'height' => null,
])

{{--
    Either the real picture or the striped placeholder plate that names the
    asset and its target size. Every artwork slot on a public page goes
    through here, so dropping a file in is a data change, never a template
    edit. `height` is a CSS length applied through --image-slot-h so the
    caller decides the crop box without a wrapper element.
--}}
@if ($path !== '' && $path !== null)
    <img
        class="image-slot-img"
        src="{{ asset($path) }}"
        alt="{{ $alt }}"
        loading="lazy"
        @if ($height) style="--image-slot-h: {{ $height }}" @endif
    >
@else
    <figure class="image-slot" aria-hidden="true" @if ($height) style="--image-slot-h: {{ $height }}" @endif>
        <span>{{ $caption }}</span>
    </figure>
@endif
```

`resources/views/components/portfolio/availability-dot.blade.php`:

```blade
{{-- The 7px green dot with the pulsing ring. Decorative: the availability
     sentence beside it carries the meaning. The pulse lives only inside a
     prefers-reduced-motion: no-preference query. --}}
<span class="availability-dot" aria-hidden="true"></span>
```

`resources/views/components/portfolio/pill-button.blade.php`:

```blade
@props([
    'href',
    'variant' => 'gold',
    'download' => false,
])

{{--
    The public pill link. `.btn-gold` / `.btn-gold-subtle` carry the same
    semantics but live in the admin-scoped dashboard stylesheet and target
    Flux buttons, which never load on a public page. Unifying the two is
    separate, later work.
--}}
<a
    href="{{ $href }}"
    class="pill-button pill-button--{{ $variant }}"
    @if ($download) download @endif
>{{ $slot }}</a>
```

- [ ] **Step 4: Write the component CSS**

`resources/css/components/image-slot.css`:

```css
/* ── Image slot ───────────────────────────────────────────────
   The real picture, or the striped plate naming the asset that
   is still missing. Both fill the box the caller sizes with
   --image-slot-h.                                             */

.image-slot {
    position: relative;
    display: flex;
    align-items: flex-end;
    justify-content: flex-start;
    height: var(--image-slot-h, auto);
    background: var(--c-surface-sunken);
    background-image: repeating-linear-gradient(
        135deg,
        color-mix(in srgb, var(--c-primary-fade) 26%, transparent) 0 1px,
        transparent 1px 9px
    );
}

.image-slot span {
    font-family: var(--font-mono);
    font-size: 10.5px;
    letter-spacing: 0.06em;
    color: var(--c-muted);
    padding: 0.7rem 0.9rem;
}

.image-slot-img {
    display: block;
    width: 100%;
    height: var(--image-slot-h, auto);
    object-fit: cover;
}
```

`resources/css/components/availability-dot.css`:

```css
/* ── Availability dot ─────────────────────────────────────────
   The page's only ambient motion. The pulse is declared only
   inside the no-preference query, so reduced-motion users get
   a static dot rather than an overridden animation.           */

.availability-dot {
    display: inline-block;
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--c-available);
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--c-available) 22%, transparent);
    flex-shrink: 0;
}

@media (prefers-reduced-motion: no-preference) {
    .availability-dot {
        animation: availability-pulse 2.4s ease-in-out infinite;
    }
}

@keyframes availability-pulse {
    50% {
        box-shadow: 0 0 0 6px color-mix(in srgb, var(--c-available) 0%, transparent);
    }
}
```

`resources/css/components/pill-button.css`:

```css
/* ── Pill button ──────────────────────────────────────────────
   Public gold / ghost call-to-action link.                    */

.pill-button {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-family: var(--font-sans);
    font-size: 0.95rem;
    font-weight: var(--fw-semibold);
    padding: 0.75rem 1.5rem;
    border-radius: var(--r-pill);
    border: 1px solid transparent;
    text-decoration: none;
    cursor: pointer;
    transition:
        background-color var(--t-fast),
        color var(--t-fast),
        border-color var(--t-fast),
        transform var(--t-fast);
}

.pill-button--gold {
    background: var(--c-primary);
    color: var(--c-bg);
}

.pill-button--gold:hover {
    background: var(--c-primary-dk);
    color: #fff;
    box-shadow: 0 0 12px color-mix(in srgb, var(--c-primary) 40%, transparent);
}

.pill-button--ghost {
    border-color: var(--c-primary-fade);
    color: var(--c-fg);
    background: transparent;
}

.pill-button--ghost:hover {
    border-color: var(--c-primary);
    color: var(--c-primary);
}

.pill-button:active {
    transform: translateY(1px);
}
```

- [ ] **Step 5: Add the availability token**

In `resources/css/app.css`, inside the `@theme` block beside the other portfolio colours:

```css
    --color-available:                 #4ADE80;  /* availability dot (about page) */
```

in the `:root` alias list, after `--c-selection-fg`:

```css
    --c-available:      var(--color-available);
```

and in the `html:not(.dark)` block, beside the other light overrides:

```css
    /* Availability green: emerald-500 is illegible on bone — green-600 holds
       the same meaning and passes as a 7px dot on white. */
    --color-available: #16A34A;
```

- [ ] **Step 6: Run the test to verify it passes**

```bash
docker exec portfolio-2-app-1 php artisan test --compact --filter=PortfolioComponents
```
Expected: PASS (5 tests).

- [ ] **Step 7: Commit**

```bash
git add resources/views/components/portfolio resources/css/components resources/css/app.css tests/Feature/PortfolioComponentsTest.php
git commit -m "feat(about-me): add image-slot, availability-dot and pill-button components"
```

---

### Task 4: Controller, copy and the seven-section view

**Files:**
- Modify: `app/Http/Controllers/AboutMeController.php`
- Modify: `resources/views/about-me.blade.php`
- Modify: `resources/lang/en/pages/about-me.php`, `resources/lang/cs/pages/about-me.php`
- Test: `tests/Feature/AboutMePageTest.php` (rewritten)

**Interfaces:**
- Consumes: Task 1's models, Task 2's `Setting` keys and config entries, Task 3's three components.
- Produces: the view's class names, which Task 5 styles: `.about-me-intro`, `.about-me-intro-copy`, `.about-me-intro-lead`, `.about-me-intro-cols`, `.about-me-sign`, `.about-me-intro-portrait`, `.about-me-intro-facts`, `.about-me-fact`, `.about-me-fact-label`, `.about-me-fact-value`, `.about-me-path`, `.about-me-node`, `.about-me-node--now`, `.about-me-node-year`, `.about-me-node-body`, `.about-me-bento`, `.about-me-tile`, `.about-me-tile--hero`, `.about-me-tile--soft`, `.about-me-tile-value`, `.about-me-tile-label`, `.about-me-beyond`, `.about-me-beyond-cell`, `.about-me-beyond-text`, `.about-me-beyond-meta`, `.about-me-showups`, `.about-me-show`, `.about-me-show-top`, `.about-me-show-kind`, `.about-me-show-year`, `.about-me-cta`, `.about-me-cta-available`, `.about-me-cta-sub`, `.about-me-cta-actions`, `.about-me-cta-row`, `.about-me-cta-fine`. Show-up tiles carry their badge colour as the inline custom property `--show-color`.

- [ ] **Step 1: Write the failing test**

Replace `tests/Feature/AboutMePageTest.php` wholesale:

```php
<?php

use App\Models\Badge;
use App\Models\Experience;
use App\Models\Interest;
use App\Models\PathNode;
use App\Models\Review;
use App\Models\Setting;
use App\Models\Stat;
use Database\Seeders\InterestSeeder;
use Database\Seeders\PathNodeSeeder;
use Database\Seeders\SettingSeeder;
use Database\Seeders\StatSeeder;

/** Seeds everything the seven sections read. */
function seedAboutPage(): void
{
    test()->seed(SettingSeeder::class);
    test()->seed(StatSeeder::class);
    test()->seed(PathNodeSeeder::class);
    test()->seed(InterestSeeder::class);
    Experience::factory()->onAbout()->create(['title' => ['en' => 'PEER program', 'cs' => 'Program PEER']]);
    Review::factory()->create(['name' => 'Jan Novák']);
}

test('about me page returns 200', function () {
    $response = $this->get(route('about-me'));
    $response->assertOk();
});

test('the about me page renders all seven sections in order', function () {
    seedAboutPage();

    $html = $this->get(route('about-me'))->getContent();

    $sections = [
        'id="about-me"',
        'id="about-me-path"',
        'id="about-me-stats"',
        'id="about-me-interests"',
        'id="about-me-showups"',
        'id="about-me-references"',
        'id="about-me-contact"',
    ];

    $positions = array_map(fn (string $needle) => strpos($html, $needle), $sections);

    expect($positions)->not->toContain(false)
        ->and($positions)->toBe(array_values(collect($positions)->sort()->all()));
});

test('the about me intro head carries no ghost under the hero', function () {
    seedAboutPage();

    $html = $this->get(route('about-me'))->getContent();

    expect($html)->toContain('sechead--noghost')
        ->and($html)->toContain('portfolio-section--no-reveal');
});

test('the about me page carries exactly one ghost wordmark', function () {
    seedAboutPage();

    $html = $this->get(route('about-me'))->getContent();

    expect(preg_match_all('/<div class="sechead-ghost"/', $html))->toBe(1);
});

test('show ups are filtered by the about flag', function () {
    seedAboutPage();
    Experience::factory()->create(['title' => ['en' => 'Hidden from about', 'cs' => 'Skryté']]);

    $this->get(route('about-me'))
        ->assertSee('PEER program')
        ->assertDontSee('Hidden from about');
});

test('a show up without a badge renders without a kind label and without erroring', function () {
    seedAboutPage();

    $this->get(route('about-me'))
        ->assertOk()
        ->assertDontSee('about-me-show-kind', false);
});

test('a show up with a badge takes the badge colour and translated name', function () {
    seedAboutPage();
    $badge = Badge::factory()->create(['slug' => 'volunteering', 'name' => ['en' => 'Volunteering', 'cs' => 'Dobrovolnictví'], 'color' => '#34D399']);
    Experience::factory()->onAbout()->create(['title' => ['en' => 'Czech Day', 'cs' => 'Český den']])->badges()->attach($badge);

    $this->get(route('about-me'))
        ->assertSee('--show-color: #34D399', false)
        ->assertSee('Volunteering');
});

test('every section renders nothing on an empty database', function () {
    $html = $this->get(route('about-me'))->assertOk()->getContent();

    expect($html)->not->toContain('about-me-intro')
        ->and($html)->not->toContain('about-me-path')
        ->and($html)->not->toContain('about-me-bento')
        ->and($html)->not->toContain('about-me-beyond')
        ->and($html)->not->toContain('about-me-showups')
        ->and($html)->not->toContain('about-me-cta');
});

test('the czech locale renders the czech copy of the new sections', function () {
    seedAboutPage();

    $this->withSession(['locale' => 'cs'])
        ->get(route('about-me'))
        ->assertSee('Zpátky na základní školu')
        ->assertSee('Šachy od druhé třídy');
});

test('the availability dot appears in the intro and the contact band together', function () {
    seedAboutPage();

    $html = $this->get(route('about-me'))->getContent();

    expect(substr_count($html, 'class="availability-dot"'))->toBe(2);

    Setting::updateOrCreate(['key' => 'about_available'], ['value' => ['en' => '', 'cs' => '']]);

    $html = $this->get(route('about-me'))->getContent();

    expect(substr_count($html, 'class="availability-dot"'))->toBe(0);
});

test('the cv button is absent until a cv path is configured', function () {
    seedAboutPage();

    $this->get(route('about-me'))->assertDontSee('pill-button--ghost" download', false);

    config(['portfolio.cv_path' => 'cv/richard-hyvl.pdf']);

    $this->get(route('about-me'))->assertSee('cv/richard-hyvl.pdf', false);
});

test('an interest highlight is rendered as markup, not escaped, and cannot inject', function () {
    seedAboutPage();
    Interest::query()->delete();
    Interest::factory()->create([
        'title' => ['en' => '<b>Chess</b> since 2nd grade'],
        'highlight' => ['en' => '2nd grade'],
    ]);

    $this->get(route('about-me'))
        ->assertSee('<span>2nd grade</span>', false)
        ->assertDontSee('<b>Chess</b>', false);
});

test('the stats bento marks hero and soft tiles', function () {
    seedAboutPage();

    $html = $this->get(route('about-me'))->getContent();

    expect(substr_count($html, 'about-me-tile--hero'))->toBe(2)
        ->and(substr_count($html, 'about-me-tile--soft'))->toBe(2);
});

test('the path node marked now takes the now modifier', function () {
    seedAboutPage();

    $html = $this->get(route('about-me'))->getContent();

    expect(substr_count($html, 'about-me-node--now'))->toBe(1)
        ->and(PathNode::where('is_now', true)->count())->toBe(1);
});
```

Note on the locale test: check how `tests/Feature/LanguageToggleTest.php` sets the locale and use exactly that mechanism instead of `withSession` if it differs.

- [ ] **Step 2: Run the test to verify it fails**

```bash
docker exec portfolio-2-app-1 php artisan test --compact --filter=AboutMePage
```
Expected: FAIL — the page still renders `about-me-content`.

- [ ] **Step 3: Write the controller**

`app/Http/Controllers/AboutMeController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Experience;
use App\Models\Interest;
use App\Models\PathNode;
use App\Models\Review;
use App\Models\Stat;

class AboutMeController extends Controller
{
    public function __invoke()
    {
        $pathNodes = PathNode::orderBy('sort_order')->get();
        $interests = Interest::orderBy('sort_order')->get();
        $stats = Stat::orderBy('sort_order')->get();

        /* Eager loaded: each tile reads its first badge for the kind label and
           the accent colour, so four tiles would otherwise mean four queries. */
        $showUps = Experience::where('show_on_about', true)
            ->with('badges')
            ->orderBy('sort_order')
            ->get();

        $reviews = Review::orderBy('sort_order')->get();

        return view('about-me', compact('pathNodes', 'interests', 'stats', 'showUps', 'reviews'));
    }
}
```

- [ ] **Step 4: Write the copy**

`resources/lang/en/pages/about-me.php` — keep `hero_wordmark`, `hero_tags`, `head_eyebrow`, `head_note`, `stats_head_ghost`, `stats_head_eyebrow`, `stats_head_note`, `title`; delete every `card*` key; add:

```php
    'intro_sign' => 'Richard Hývl',
    'portrait_alt' => 'Richard Hývl',
    'portrait_slot' => 'portrait · 900×1200 · replace',
    'fact_available_label' => 'Availability',

    'path_head_eyebrow' => 'How did we get here',
    'path_head_title' => 'The <em>short</em> version',
    'path_head_note' => 'Five turns that decided what I do with my days.',

    'interests_head_eyebrow' => 'Off the screen',
    'interests_head_title' => 'What I do when the <em>laptop</em> closes',
    'interests_head_note' => 'The parts of me that keep showing up in how I work.',
    'interests_slot' => ':title · 1200×900 · replace',

    'showups_head_eyebrow' => 'Not sitting still',
    'showups_head_title' => 'Things I <em>show up</em> for',
    'showups_head_note' => 'Not a trophy shelf — the full record lives on the <a href=":url">experience page</a>. Just proof the weeks are full.',

    'references_head_eyebrow' => 'In other words',
    'references_head_title' => 'What people I have <em>worked with</em> say',
    'references_head_note' => 'Teachers, teammates and clients.',

    'cta_email' => 'Write me',
    'cta_cv' => 'Download CV',
    'cta_fine' => 'Replies within 24 hours · CET',
```

`resources/lang/cs/pages/about-me.php` — same keys, Czech:

```php
    'intro_sign' => 'Richard Hývl',
    'portrait_alt' => 'Richard Hývl',
    'portrait_slot' => 'portrét · 900×1200 · doplnit',
    'fact_available_label' => 'Dostupnost',

    'path_head_eyebrow' => 'Jak jsme se sem dostali',
    'path_head_title' => '<em>Krátká</em> verze',
    'path_head_note' => 'Pět zatáček, které rozhodly, čím trávím dny.',

    'interests_head_eyebrow' => 'Mimo obrazovku',
    'interests_head_title' => 'Co dělám, když <em>zavřu notebook</em>',
    'interests_head_note' => 'Části mě, které se pořád objevují i v tom, jak pracuji.',
    'interests_slot' => ':title · 1200×900 · doplnit',

    'showups_head_eyebrow' => 'Neseděl jsem doma',
    'showups_head_title' => 'Věci, na které <em>chodím</em>',
    'showups_head_note' => 'Není to police s trofejemi — celý záznam je na <a href=":url">stránce zkušeností</a>. Jen důkaz, že týdny jsou plné.',

    'references_head_eyebrow' => 'Jinými slovy',
    'references_head_title' => 'Co říkají lidé, se kterými jsem <em>pracoval</em>',
    'references_head_note' => 'Učitelé, spolupracovníci a klienti.',

    'cta_email' => 'Napište mi',
    'cta_cv' => 'Stáhnout CV',
    'cta_fine' => 'Odpovídám do 24 hodin · SEČ',
```

- [ ] **Step 5: Write the view**

`resources/views/about-me.blade.php` — the hero block at the top stays byte-for-byte as it is today; everything below it is replaced:

```blade
<x-portfolio-layout :title="__('layout/header.about_title')" :description="__('layout/header.about_desc')" :styles="['resources/css/pages/about-me.css']">

    @php
        $locale = app()->getLocale();
        $introLead = \App\Models\Setting::text('about_intro_lead', $locale);
        $introBody = \App\Models\Setting::list('about_intro_body', $locale);
        $facts = \App\Models\Setting::list('about_facts', $locale);
        $available = \App\Models\Setting::text('about_available', $locale);
        $ctaTitle = \App\Models\Setting::text('about_cta_title', $locale);
        $ctaSub = \App\Models\Setting::text('about_cta_sub', $locale);
    @endphp

    {{-- Hero — unchanged --}}
    <x-portfolio.dock-hero
        :eyebrow="\App\Models\Setting::text('about_hero_suptitle', $locale)"
        :title="\App\Models\Setting::text('about_hero_title', $locale)"
        :roles="\App\Models\Setting::list('about_hero_roles', $locale)"
        :tags="__('pages/about-me.hero_tags')"
        :wordmark="__('pages/about-me.hero_wordmark')"
        :photo="config('portfolio.hero_images.about')"
        photo-alt=""
        photo-position="50% 25%"
    />

    {{-- 1 · Intro. No fade-up: the hero leaves this section's top edge inside
         the first viewport, so it must already be there when the page paints. --}}
    @if ($introLead !== '' || $introBody !== [])
        <section id="about-me" class="portfolio-section portfolio-section--no-reveal">
            <x-portfolio.section-head
                :eyebrow="__('pages/about-me.head_eyebrow')"
                :title="\App\Models\Setting::text('about_title', $locale)"
                :note="__('pages/about-me.head_note')"
            />
            <div class="about-me-intro">
                <div class="about-me-intro-copy">
                    @if ($introLead !== '')
                        <p class="about-me-intro-lead">{!! $introLead !!}</p>
                    @endif
                    <div class="about-me-intro-cols">
                        @foreach ($introBody as $paragraph)
                            <p>{!! $paragraph !!}</p>
                        @endforeach
                    </div>
                    <p class="about-me-sign">{{ __('pages/about-me.intro_sign') }}</p>
                </div>

                <div class="about-me-intro-portrait">
                    <x-portfolio.image-slot
                        :path="config('portfolio.hero_images.portrait')"
                        :caption="__('pages/about-me.portrait_slot')"
                        :alt="__('pages/about-me.portrait_alt')"
                        height="100%"
                    />
                </div>

                @if ($facts !== [] || $available !== '')
                    <div class="about-me-intro-facts">
                        @foreach ($facts as $fact)
                            <div class="about-me-fact">
                                <span class="about-me-fact-label">{{ $fact['label'] ?? '' }}</span>
                                <span class="about-me-fact-value">{{ $fact['value'] ?? '' }}</span>
                            </div>
                        @endforeach
                        @if ($available !== '')
                            <div class="about-me-fact">
                                <span class="about-me-fact-label">{{ __('pages/about-me.fact_available_label') }}</span>
                                <span class="about-me-fact-value">
                                    <x-portfolio.availability-dot />{{ $available }}
                                </span>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- 2 · The short version --}}
    @if ($pathNodes->isNotEmpty())
        <section id="about-me-path" class="portfolio-section">
            <x-portfolio.section-head
                :eyebrow="__('pages/about-me.path_head_eyebrow')"
                :title="__('pages/about-me.path_head_title')"
                :note="__('pages/about-me.path_head_note')"
            />
            <div class="about-me-path">
                @foreach ($pathNodes as $node)
                    <div @class(['about-me-node', 'about-me-node--now' => $node->is_now])>
                        <div class="about-me-node-year">{{ $node->getTranslation('year', $locale) }}</div>
                        <div class="about-me-node-body">
                            <h3>{{ $node->getTranslation('title', $locale) }}</h3>
                            <p>{{ $node->getTranslation('body', $locale) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- 3 · Stats bento — the page's single ghost wordmark --}}
    @if ($stats->isNotEmpty())
        <section id="about-me-stats" class="portfolio-section">
            <x-portfolio.section-head
                :ghost="__('pages/about-me.stats_head_ghost')"
                :eyebrow="__('pages/about-me.stats_head_eyebrow')"
                :title="\App\Models\Setting::text('stats_title', $locale)"
                :note="__('pages/about-me.stats_head_note')"
            />
            <div class="about-me-bento">
                @foreach ($stats as $stat)
                    <div @class([
                        'about-me-tile',
                        'about-me-tile--hero' => $stat->tier === 'hero',
                        'about-me-tile--soft' => $stat->tier === 'soft',
                    ])>
                        <span class="about-me-tile-value" @if ($stat->value_id) id="{{ $stat->value_id }}" @endif>{{ $stat->displayValue($locale) }}</span>
                        <span class="about-me-tile-label">{{ $stat->getTranslation('text', $locale) }}</span>
                        @if ($stat->tier === 'hero' && $stat->getTranslation('note', $locale) !== '')
                            <p>{{ $stat->getTranslation('note', $locale) }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- 4 · Off the screen --}}
    @if ($interests->isNotEmpty())
        <section id="about-me-interests" class="portfolio-section">
            <x-portfolio.section-head
                :eyebrow="__('pages/about-me.interests_head_eyebrow')"
                :title="__('pages/about-me.interests_head_title')"
                :note="__('pages/about-me.interests_head_note')"
            />
            <div class="about-me-beyond">
                @foreach ($interests as $interest)
                    <div class="about-me-beyond-cell">
                        <x-portfolio.image-slot
                            :path="$interest->image_path"
                            :caption="__('pages/about-me.interests_slot', ['title' => $interest->getTranslation('title', $locale)])"
                            :alt="$interest->getTranslation('title', $locale)"
                            height="230px"
                        />
                        <div class="about-me-beyond-text">
                            <h3>{!! $interest->highlightedTitle($locale) !!}</h3>
                            <p>{{ $interest->getTranslation('body', $locale) }}</p>
                            <p class="about-me-beyond-meta">{{ $interest->getTranslation('meta', $locale) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- 5 · Things I show up for --}}
    @if ($showUps->isNotEmpty())
        <section id="about-me-showups" class="portfolio-section">
            <x-portfolio.section-head
                :eyebrow="__('pages/about-me.showups_head_eyebrow')"
                :title="__('pages/about-me.showups_head_title')"
                :note="__('pages/about-me.showups_head_note', ['url' => route('experience')])"
            />
            <div class="about-me-showups">
                @foreach ($showUps as $showUp)
                    @php $badge = $showUp->badges->first(); @endphp
                    <div class="about-me-show" @if ($badge?->color) style="--show-color: {{ $badge->color }}" @endif>
                        <div class="about-me-show-top">
                            @if ($badge)
                                <span class="about-me-show-kind">{{ $badge->getTranslation('name', $locale) }}</span>
                            @endif
                            <span class="about-me-show-year">{{ $showUp->getTranslation('year', $locale) }}</span>
                        </div>
                        <h3>{{ $showUp->getTranslation('title', $locale) }}</h3>
                        <p>{{ $showUp->getTranslation('subtitle', $locale) }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- 6 · References — the home page's carousel, same markup and JS --}}
    @if ($reviews->isNotEmpty())
        <section id="about-me-references" class="portfolio-section">
            <x-portfolio.section-head
                :eyebrow="__('pages/about-me.references_head_eyebrow')"
                :title="__('pages/about-me.references_head_title')"
                :note="__('pages/about-me.references_head_note')"
            />
            <div class="reviews-carousel" data-reviews-carousel>
                <div class="reviews-carousel-viewport">
                    <article class="reviews-row" role="list">
                        @foreach ($reviews as $review)
                            <div class="reviews-row-card" role="listitem">
                                <div class="reviews-row-card-mark" aria-hidden="true">&ldquo;</div>
                                <p>{!! $review->highlightedText($locale) !!}</p>
                                <div class="reviews-row-card-who">
                                    <div class="reviews-row-card-avatar" aria-hidden="true">{{ $review->initials() }}</div>
                                    <div class="reviews-row-card-text">
                                        <p class="reviews-row-card-name">{{ $review->name }}</p>
                                        <p class="mini">{{ $review->getTranslation('position', $locale) }}</p>
                                    </div>
                                    @if ($review->source)
                                        <span class="reviews-row-card-badge" style="--badge-color: {{ $review->source_color ?? 'var(--c-primary)' }}">{{ $review->source }}</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </article>
                </div>
                <div class="reviews-carousel-dots"></div>
            </div>
        </section>
    @endif

    {{-- 7 · Contact — the page's end punctuation, the only tinted surface --}}
    @if ($ctaTitle !== '')
        <section id="about-me-contact" class="portfolio-section">
            <div class="about-me-cta">
                <div>
                    @if ($available !== '')
                        <p class="about-me-cta-available">
                            <x-portfolio.availability-dot />{{ $available }}
                        </p>
                    @endif
                    <h2>{!! $ctaTitle !!}</h2>
                    @if ($ctaSub !== '')
                        <p class="about-me-cta-sub">{{ $ctaSub }}</p>
                    @endif
                </div>
                <div class="about-me-cta-actions">
                    <div class="about-me-cta-row">
                        <x-portfolio.pill-button :href="config('portfolio.social.email')">{{ __('pages/about-me.cta_email') }}</x-portfolio.pill-button>
                        @if (config('portfolio.cv_path'))
                            <x-portfolio.pill-button :href="asset(config('portfolio.cv_path'))" variant="ghost" :download="true">{{ __('pages/about-me.cta_cv') }}</x-portfolio.pill-button>
                        @endif
                    </div>
                    <div class="about-me-cta-row">
                        <x-portfolio.pill-button :href="config('portfolio.social.github')" variant="ghost">GitHub</x-portfolio.pill-button>
                        <x-portfolio.pill-button :href="config('portfolio.social.linkedin')" variant="ghost">LinkedIn</x-portfolio.pill-button>
                    </div>
                    <p class="about-me-cta-fine">{{ __('pages/about-me.cta_fine') }}</p>
                </div>
            </div>
        </section>
    @endif

</x-portfolio-layout>
```

- [ ] **Step 6: Run the tests to verify they pass**

```bash
docker exec portfolio-2-app-1 php artisan test --compact --filter=AboutMePage
docker exec portfolio-2-app-1 vendor/bin/pint --dirty --format agent
```
Expected: PASS (14 tests). If the `@class`/`@style` output differs from what an assertion expects, fix the assertion to match Blade's real output — never weaken it to `assertOk()`.

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/AboutMeController.php resources/views/about-me.blade.php resources/lang tests/Feature/AboutMePageTest.php
git commit -m "feat(about-me): recompose the page into seven sections"
```

---

### Task 5: Page CSS and browser coverage

**Files:**
- Modify: `resources/css/pages/about-me.css` (rewritten)
- Modify: `tests/Browser/PublicPagesTest.php`, `tests/Browser/LightThemeTest.php`
- Verify: `vite.config.js` unchanged (`about-me.css` stays the page's single entry; the three component files are `@import`ed)

**Interfaces:**
- Consumes: Task 4's class names, Task 3's component CSS files.
- Produces: nothing later tasks read.

- [ ] **Step 1: Write the failing browser tests**

In `tests/Browser/PublicPagesTest.php`, add:

```php
test('the about page bento lays the hero tiles across two columns', function () {
    seedAboutPage();

    $page = visit('/about-me')->resize(1440, 900);

    $columns = <<<'JS'
        getComputedStyle(document.querySelector('.about-me-bento')).gridTemplateColumns.split(' ').length
    JS;

    $heroSpan = <<<'JS'
        getComputedStyle(document.querySelector('.about-me-tile--hero')).gridColumnStart
    JS;

    expect($page->script($columns))->toBe(4)
        ->and($page->script($heroSpan))->toContain('span 2');
});

test('the about page availability dot animates when motion is allowed', function () {
    seedAboutPage();

    $page = visit('/about-me')->resize(1440, 900);

    expect($page->script("getComputedStyle(document.querySelector('.availability-dot')).animationName"))
        ->toBe('availability-pulse');
});

test('the about page reviews carousel advances on its own', function () {
    seedAboutPage();
    Review::query()->delete();
    foreach (range(0, 4) as $i) {
        Review::factory()->create(['sort_order' => $i, 'name' => "Reviewer $i"]);
    }

    $page = visit('/about-me')->resize(1280, 900);
    $page->wait(6);

    expect($page->script("document.querySelector('#about-me-references .reviews-carousel-viewport').scrollLeft"))
        ->toBeGreaterThan(0);
});
```

`seedAboutPage()` is defined in `tests/Feature/AboutMePageTest.php`, which browser tests do not autoload — move the helper into `tests/Pest.php` (below the `pest()->extend(...)` calls, as a plain `function`) and delete the copy in the feature test file, so both suites share one definition.

`/about-me` is already covered by the file's first test (`assertNoJavaScriptErrors()->assertNoConsoleLogs()`); leave that test alone — it now exercises the new page.

In `tests/Browser/LightThemeTest.php`, the light-mode contrast test seeds `AboutCard::factory()` and measures `.about-me-card p`, both of which are gone. Repoint it at the new prose:

```php
test('light-mode body and muted text clear WCAG AA on their own surface', function () use ($goLight, $contrastOf, $themeTransition) {
    seedAboutPage();

    $page = visit('/about-me')->resize(1440, 900);
    $page->script($goLight);
    $page->wait($themeTransition);

    expect($page->script($contrastOf('.portfolio-page h3', '.portfolio-page')))->toBeGreaterThanOrEqual(4.5)
        ->and($page->script($contrastOf('.about-me-intro-cols p', '.about-me-intro')))->toBeGreaterThanOrEqual(4.5);
});
```

and drop the `use App\Models\AboutCard;` import.

- [ ] **Step 2: Run the browser tests to verify they fail**

```bash
docker exec portfolio-2-app-1 php artisan test --compact tests/Browser/PublicPagesTest.php
```
Expected: FAIL — `.about-me-bento` has no grid columns (the page CSS is still the old masonry).

- [ ] **Step 3: Rewrite the page CSS**

`resources/css/pages/about-me.css` — `.about-me-content`, `.about-me-card` and `.about-me-stats-cards` are deleted outright:

```css
@import '../components/dock-hero.css';
@import '../components/section-head.css';
@import '../components/image-slot.css';
@import '../components/availability-dot.css';
@import '../components/pill-button.css';

/* ================================================================
   ABOUT ME PAGE
   ================================================================
   Seven sections, seven silhouettes: a bordered intro frame, a
   borderless timeline, a bento, three photo cells in one frame, a
   flat tile strip, the shared reviews carousel and a tinted
   contact band. Section rhythm comes from .portfolio-section.
   ================================================================ */

/* ── 1 · Intro ─────────────────────────────────────────────── */
.about-me-intro {
    display: grid;
    grid-template-columns: 1fr 360px;
    grid-template-rows: 1fr auto;
    border: var(--border-w) solid var(--c-primary-fade);
    border-radius: var(--r-card);
    /* --c-bg, not --c-surface: it reads as a frame on the page, not a card. */
    background: var(--c-bg);
    overflow: hidden;
}

.about-me-intro-copy {
    display: flex;
    flex-direction: column;
    gap: 1.6rem;
    padding: 3.4rem 3.2rem 3rem;
}

.about-me-intro-lead {
    font-family: var(--font-display);
    font-size: clamp(1.7rem, 1rem + 1.9vw, 2.35rem);
    font-weight: var(--fw-bold);
    line-height: 1.14;
    letter-spacing: -0.02em;
    max-width: 26ch;
    text-wrap: pretty;
}

.about-me-intro-lead em {
    font-style: normal;
    font-weight: 500;
    color: var(--c-primary);
}

.about-me-intro-cols {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.6rem 2.4rem;
}

.about-me-intro-cols p {
    font-size: 0.95rem;
    font-weight: var(--fw-light);
    line-height: 1.7;
    color: var(--c-muted);
    text-wrap: pretty;
}

.about-me-intro-cols p b {
    color: var(--c-fg);
    font-weight: var(--fw-semibold);
}

.about-me-sign {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-top: auto;
    padding-top: 0.4rem;
    font-family: var(--font-display);
    font-size: 1rem;
    color: var(--c-fg);
}

.about-me-sign::before {
    content: '';
    width: 34px;
    height: 1px;
    background: var(--c-primary-fade);
}

.about-me-intro-portrait {
    position: relative;
    min-height: 420px;
    border-left: 1px solid color-mix(in srgb, var(--c-primary-fade) 55%, transparent);
}

.about-me-intro-portrait .image-slot,
.about-me-intro-portrait .image-slot-img {
    width: 100%;
    height: 100%;
}

.about-me-intro-facts {
    grid-column: 1 / -1;
    display: flex;
    background: var(--c-surface);
    border-top: var(--border-w) solid var(--c-primary-fade);
}

.about-me-fact {
    flex: 1;
    display: grid;
    align-content: start;
    gap: 0.2rem;
    padding: 1.05rem 1.6rem;
    border-left: 1px solid color-mix(in srgb, var(--c-primary-fade) 45%, transparent);
}

.about-me-fact:first-child {
    border-left: 0;
}

.about-me-fact-label {
    font-size: 10.5px;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--c-muted);
}

.about-me-fact-value {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    font-size: 0.95rem;
    font-weight: var(--fw-semibold);
}

/* ── 2 · The short version ─────────────────────────────────── */
.about-me-path {
    display: flex;
    flex-direction: column;
}

.about-me-node {
    position: relative;
    display: grid;
    grid-template-columns: 88px 1fr;
    gap: 2rem;
    padding: 1.6rem 0;
    border-top: 1px solid color-mix(in srgb, var(--c-primary-fade) 50%, transparent);
}

.about-me-node:first-of-type {
    border-top: 0;
}

/* The rail, clipped at the first and last node so it starts and ends on a dot. */
.about-me-node::before {
    content: '';
    position: absolute;
    left: calc(88px + 1rem - 1px);
    top: 0;
    bottom: 0;
    width: 1px;
    background: color-mix(in srgb, var(--c-primary-fade) 60%, transparent);
}

.about-me-node:first-of-type::before {
    top: 2.35rem;
}

.about-me-node:last-of-type::before {
    bottom: auto;
    height: 2.35rem;
}

.about-me-node::after {
    content: '';
    position: absolute;
    left: calc(88px + 1rem - 5px);
    top: 2rem;
    width: 9px;
    height: 9px;
    border-radius: 50%;
    background: var(--c-primary);
    box-shadow: 0 0 0 4px var(--c-bg);
}

.about-me-node-year {
    font-family: var(--font-mono);
    font-size: 0.9rem;
    letter-spacing: 0.04em;
    color: var(--c-primary);
    padding-top: 1.4rem;
}

.about-me-node-body {
    padding-top: 1.15rem;
    max-width: 62ch;
}

.about-me-node-body h3 {
    font-size: 1.25rem;
    font-weight: var(--fw-regular);
    line-height: 1.25;
    margin-bottom: 0.4rem;
}

.about-me-node-body p {
    font-size: 0.95rem;
    font-weight: var(--fw-light);
    line-height: 1.7;
    color: var(--c-muted);
    text-wrap: pretty;
}

.about-me-node--now .about-me-node-body h3 {
    color: var(--c-primary);
}

/* ── 3 · Stats bento ───────────────────────────────────────── */
.about-me-bento {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.75rem;
}

.about-me-tile {
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    gap: 0.3rem;
    padding: 1.3rem 1.4rem;
    min-height: 118px;
    border: var(--border-w) solid color-mix(in srgb, var(--c-primary-fade) 80%, transparent);
    border-radius: var(--r-card-sm);
    background: var(--c-surface);
    transition: border-color var(--t-fast);
}

.about-me-tile:hover {
    border-color: var(--c-primary);
}

.about-me-tile-value {
    font-family: var(--font-display);
    font-size: 2.1rem;
    font-weight: var(--fw-bold);
    line-height: 1;
    color: var(--c-primary);
}

.about-me-tile-label {
    font-size: 0.85rem;
    font-weight: var(--fw-light);
    color: var(--c-muted);
}

.about-me-tile--hero {
    grid-column: span 2;
    min-height: 186px;
    background: var(--c-surface-sunken);
    border-color: var(--c-primary-lt);
}

.about-me-tile--hero .about-me-tile-value {
    font-size: clamp(3rem, 2rem + 2.6vw, 4.4rem);
}

.about-me-tile--hero .about-me-tile-label {
    font-size: 0.95rem;
    color: var(--c-fg);
}

.about-me-tile--hero p {
    font-size: 0.85rem;
    font-weight: var(--fw-light);
    line-height: 1.6;
    color: var(--c-muted);
    max-width: 30ch;
    margin-top: 0.3rem;
}

/* The jokes: an aside in the mix, not fenced off in their own box. */
.about-me-tile--soft {
    background: transparent;
}

.about-me-tile--soft .about-me-tile-value {
    color: var(--c-fg);
    opacity: 0.55;
}

/* ── 4 · Off the screen ────────────────────────────────────── */
.about-me-beyond {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    border: var(--border-w) solid var(--c-primary-fade);
    border-radius: var(--r-card);
    overflow: hidden;
    background: var(--c-surface);
}

.about-me-beyond-cell {
    display: flex;
    flex-direction: column;
    border-left: 1px solid color-mix(in srgb, var(--c-primary-fade) 45%, transparent);
}

.about-me-beyond-cell:first-child {
    border-left: 0;
}

.about-me-beyond-text {
    padding: 1.5rem 1.5rem 1.7rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    flex: 1;
}

.about-me-beyond-text h3 {
    font-size: 1.15rem;
    font-weight: var(--fw-regular);
}

.about-me-beyond-text h3 span {
    color: var(--c-primary);
}

.about-me-beyond-text p {
    font-size: 0.9rem;
    font-weight: var(--fw-light);
    line-height: 1.65;
    color: var(--c-muted);
    text-wrap: pretty;
}

/* margin-top:auto pins the metas to the bottom so they align across cells
   regardless of how long the copy above them runs. */
.about-me-beyond-meta {
    margin-top: auto;
    padding-top: 0.9rem;
    font-family: var(--font-mono);
    font-size: 10.5px;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--c-primary);
}

/* ── 5 · Things I show up for ──────────────────────────────── */
.about-me-showups {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 0.75rem;
}

/* Transparent and hairline on purpose — lighter than every card above, so
   this reads as a list of facts rather than a set of achievements. */
.about-me-show {
    display: flex;
    flex-direction: column;
    gap: 0.45rem;
    padding: 1.2rem 1.3rem;
    border: 1px solid color-mix(in srgb, var(--c-primary-fade) 70%, transparent);
    border-radius: var(--r-card-sm);
    background: transparent;
    transition: border-color var(--t-fast), background var(--t-fast);
}

.about-me-show:hover {
    border-color: var(--c-primary);
    background: var(--c-surface);
}

.about-me-show-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.about-me-show-kind {
    font-size: 10.5px;
    font-weight: var(--fw-semibold);
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--show-color, var(--c-primary));
}

.about-me-show-year {
    font-family: var(--font-mono);
    font-size: 10.5px;
    color: var(--c-muted);
}

.about-me-show h3 {
    font-size: 1.05rem;
    font-weight: var(--fw-regular);
    line-height: 1.3;
}

.about-me-show p {
    font-size: 0.875rem;
    font-weight: var(--fw-light);
    line-height: 1.6;
    color: var(--c-muted);
    text-wrap: pretty;
}

/* ── 7 · Contact ───────────────────────────────────────────── */
.about-me-cta {
    display: grid;
    grid-template-columns: 1fr auto;
    align-items: center;
    gap: 2.5rem;
    padding: 2.8rem 3rem;
    border: var(--border-w) solid var(--c-primary-lt);
    border-radius: var(--r-card);
    /* The page's only tinted surface, so it lands as the end punctuation. */
    background: linear-gradient(
        120deg,
        color-mix(in srgb, var(--c-primary) 9%, var(--c-surface)),
        var(--c-surface) 62%
    );
}

.about-me-cta-available {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 11px;
    font-weight: var(--fw-semibold);
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--c-fg);
    margin-bottom: 0.9rem;
}

.about-me-cta h2 {
    font-family: var(--font-display);
    font-size: clamp(1.8rem, 1.1rem + 2vw, 2.5rem);
    font-weight: var(--fw-bold);
    line-height: 1.05;
    letter-spacing: -0.02em;
}

.about-me-cta h2 em {
    font-style: normal;
    font-weight: 500;
    color: var(--c-primary);
}

.about-me-cta-sub {
    margin-top: 0.7rem;
    font-size: 0.95rem;
    font-weight: var(--fw-light);
    color: var(--c-muted);
    max-width: 44ch;
}

.about-me-cta-actions {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.8rem;
}

.about-me-cta-row {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.about-me-cta-fine {
    font-family: var(--font-mono);
    font-size: 10.5px;
    letter-spacing: 0.05em;
    color: var(--c-muted);
}

/* ── Responsive ────────────────────────────────────────────── */
@media (max-width: 1100px) {
    .about-me-intro { grid-template-columns: 1fr; }

    .about-me-intro-portrait {
        min-height: 300px;
        border-left: 0;
        border-top: 1px solid color-mix(in srgb, var(--c-primary-fade) 55%, transparent);
        order: -1;
    }

    .about-me-bento { grid-template-columns: repeat(3, 1fr); }
    .about-me-tile--hero { grid-column: span 3; }

    .about-me-beyond { grid-template-columns: 1fr; }

    .about-me-beyond-cell {
        border-left: 0;
        border-top: 1px solid color-mix(in srgb, var(--c-primary-fade) 45%, transparent);
    }

    .about-me-beyond-cell:first-child { border-top: 0; }

    .about-me-cta { grid-template-columns: 1fr; }
    .about-me-cta-actions { align-items: flex-start; }
    .about-me-cta-row { justify-content: flex-start; }
}

@media (max-width: 760px) {
    .about-me-intro-copy { padding: 2.2rem 1.5rem 2rem; }
    .about-me-intro-cols { grid-template-columns: 1fr; }
    .about-me-intro-facts { flex-wrap: wrap; }

    .about-me-fact {
        flex: 1 1 50%;
        border-left: 0;
        border-top: 1px solid color-mix(in srgb, var(--c-primary-fade) 40%, transparent);
    }

    .about-me-bento { grid-template-columns: repeat(2, 1fr); }
    .about-me-tile--hero { grid-column: span 2; }

    .about-me-node { grid-template-columns: 64px 1fr; gap: 1.1rem; }
    .about-me-node::before { left: calc(64px + 0.55rem); }
    .about-me-node::after { left: calc(64px + 0.55rem - 4px); }

    .about-me-cta { padding: 2rem 1.5rem; }
}
```

- [ ] **Step 4: Build the assets and run the browser tests**

```bash
npm run build            # on the HOST, not in the container
docker exec portfolio-2-app-1 php artisan test --compact tests/Browser/PublicPagesTest.php tests/Browser/LightThemeTest.php
```
Expected: PASS. If the light-mode contrast assertion fails, adjust the CSS colour (never the threshold) — muted text at weight 200 must clear 4.5:1 on its own surface.

- [ ] **Step 5: Commit**

```bash
git add resources/css/pages/about-me.css tests/Browser tests/Pest.php tests/Feature/AboutMePageTest.php
git commit -m "feat(about-me): restyle the page for the seven-section composition"
```

---

### Task 6: Admin — path nodes and interests

**Files:**
- Create: `resources/views/pages/manage/⚡path-nodes.blade.php`, `resources/views/pages/manage/⚡interests.blade.php`
- Modify: `routes/web.php`, `resources/views/pages/⚡dashboard.blade.php`, `resources/views/layouts/app/sidebar.blade.php`
- Test: `tests/Feature/PathNodesManagementTest.php`, `tests/Feature/InterestsManagementTest.php`, `tests/Feature/DashboardTest.php`

**Interfaces:**
- Consumes: `PathNode`, `Interest` from Task 1.
- Produces: routes `manage.path-nodes`, `manage.interests`.

- [ ] **Step 1: Write the failing tests**

`tests/Feature/PathNodesManagementTest.php`:

```php
<?php

use App\Models\PathNode;
use App\Models\User;
use Livewire\Livewire;

test('an admin can create a path node', function () {
    Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.path-nodes')
        ->set('year', ['en' => '2019', 'cs' => '2019'])
        ->set('nodeTitle', ['en' => 'Back to school', 'cs' => 'Zpátky do školy'])
        ->set('body', ['en' => 'Health issues.', 'cs' => 'Zdravotní problémy.'])
        ->call('save')
        ->assertHasNoErrors();

    expect(PathNode::first()->title)->toEqual(['en' => 'Back to school', 'cs' => 'Zpátky do školy']);
});

test('a path node requires an english year, title and body', function () {
    Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.path-nodes')
        ->set('year', ['en' => '', 'cs' => ''])
        ->set('nodeTitle', ['en' => '', 'cs' => ''])
        ->set('body', ['en' => '', 'cs' => ''])
        ->call('save')
        ->assertHasErrors(['year.en', 'nodeTitle.en', 'body.en']);
});

test('an admin can edit, flag as now, and delete a path node', function () {
    $node = PathNode::factory()->create();

    Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.path-nodes')
        ->call('openEdit', $node->id)
        ->set('nodeTitle', ['en' => 'Renamed', 'cs' => 'Přejmenováno'])
        ->set('isNow', true)
        ->call('save')
        ->assertHasNoErrors();

    expect($node->fresh()->getTranslation('title', 'en'))->toBe('Renamed')
        ->and($node->fresh()->is_now)->toBeTrue();

    Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.path-nodes')
        ->call('confirmDelete', $node->id)
        ->call('delete');

    expect(PathNode::count())->toBe(0);
});

test('reordering path nodes rewrites sort order', function () {
    $first = PathNode::factory()->create(['sort_order' => 0]);
    $second = PathNode::factory()->create(['sort_order' => 1]);

    Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.path-nodes')
        ->call('reorder', $second->id, 0);

    expect($second->fresh()->sort_order)->toBe(0)
        ->and($first->fresh()->sort_order)->toBe(1);
});
```

`tests/Feature/InterestsManagementTest.php` — the same four tests against `pages::manage.interests`, with the property names `interestTitle`, `highlight`, `body`, `meta`, `imagePath`, and one extra:

```php
test('an interest keeps its highlight and image path', function () {
    Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.interests')
        ->set('interestTitle', ['en' => 'Chess since 2nd grade', 'cs' => 'Šachy od druhé třídy'])
        ->set('highlight', ['en' => '2nd grade', 'cs' => 'druhé třídy'])
        ->set('body', ['en' => 'Tournaments.', 'cs' => 'Turnaje.'])
        ->set('meta', ['en' => '1684 elo', 'cs' => '1684 elo'])
        ->set('imagePath', 'images/about/chess.webp')
        ->call('save')
        ->assertHasNoErrors();

    $interest = Interest::first();

    expect($interest->highlight)->toEqual(['en' => '2nd grade', 'cs' => 'druhé třídy'])
        ->and($interest->image_path)->toBe('images/about/chess.webp')
        ->and($interest->highlightedTitle('en'))->toContain('<span>2nd grade</span>');
});
```

In `tests/Feature/DashboardTest.php`, extend whatever tile/route assertion exists so `manage.path-nodes` and `manage.interests` are covered; if the file has no such assertion, add:

```php
test('the dashboard links to the path node and interest managers', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertSee(route('manage.path-nodes'), false)
        ->assertSee(route('manage.interests'), false);
});
```

- [ ] **Step 2: Run the tests to verify they fail**

```bash
docker exec portfolio-2-app-1 php artisan test --compact --filter="PathNodesManagement|InterestsManagement|Dashboard"
```
Expected: FAIL — `Unable to find component: [pages::manage.path-nodes]`.

- [ ] **Step 3: Write the path-nodes manage page**

`resources/views/pages/manage/⚡path-nodes.blade.php` — copy the structure of `⚡about-cards.blade.php` exactly (search, drag reorder, create/edit modal with locale tabs, delete confirmation) with these differences: the model is `PathNode`, the title property is `$nodeTitle`, and there are two extra fields:

```php
    public array $year = ['en' => '', 'cs' => ''];
    public array $nodeTitle = ['en' => '', 'cs' => ''];
    public array $body = ['en' => '', 'cs' => ''];
    public bool $isNow = false;
```

`save()` validation:

```php
        $validated = $this->validate([
            'year' => ['required', 'array'],
            'year.en' => ['required', 'string', 'max:30'],
            'year.cs' => ['nullable', 'string', 'max:30'],
            'nodeTitle' => ['required', 'array'],
            'nodeTitle.en' => ['required', 'string', 'max:150'],
            'nodeTitle.cs' => ['nullable', 'string', 'max:150'],
            'body' => ['required', 'array'],
            'body.en' => ['required', 'string', 'max:2000'],
            'body.cs' => ['nullable', 'string', 'max:2000'],
            'isNow' => ['boolean'],
        ]);

        $data = [
            'year' => array_filter($validated['year'], fn ($v) => filled($v)),
            'title' => array_filter($validated['nodeTitle'], fn ($v) => filled($v)),
            'body' => array_filter($validated['body'], fn ($v) => filled($v)),
            'is_now' => $this->isNow,
        ];
```

`#[Computed] public function nodes()` searches on `title->>'en'` exactly as `cards()` does. The table columns are: drag handle, Year (EN), Title (EN), Now, actions — the `Now` cell renders `{{ $node->is_now ? '●' : '—' }}`. The modal adds, below the locale tabs:

```blade
            <flux:field variant="inline">
                <flux:checkbox wire:model="isNow" label="Mark as the current node" />
                <flux:error name="isNow" />
            </flux:field>
```

Header: `<x-manage.page-header title="Path nodes" subtitle="The timeline on the about-me page">` with an `Add node` button; empty row message `No path nodes found.`; `<x-manage.delete-modal entity="node" />`.

- [ ] **Step 4: Write the interests manage page**

`resources/views/pages/manage/⚡interests.blade.php` — same skeleton, model `Interest`, properties:

```php
    public array $interestTitle = ['en' => '', 'cs' => ''];
    public array $highlight = ['en' => '', 'cs' => ''];
    public array $body = ['en' => '', 'cs' => ''];
    public array $meta = ['en' => '', 'cs' => ''];
    public string $imagePath = '';
```

validation:

```php
        $validated = $this->validate([
            'interestTitle' => ['required', 'array'],
            'interestTitle.en' => ['required', 'string', 'max:150'],
            'interestTitle.cs' => ['nullable', 'string', 'max:150'],
            'highlight' => ['nullable', 'array'],
            'highlight.en' => ['nullable', 'string', 'max:150'],
            'highlight.cs' => ['nullable', 'string', 'max:150'],
            'body' => ['required', 'array'],
            'body.en' => ['required', 'string', 'max:2000'],
            'body.cs' => ['nullable', 'string', 'max:2000'],
            'meta' => ['nullable', 'array'],
            'meta.en' => ['nullable', 'string', 'max:120'],
            'meta.cs' => ['nullable', 'string', 'max:120'],
            'imagePath' => ['nullable', 'string', 'max:255'],
        ]);

        $data = [
            'title' => array_filter($validated['interestTitle'], fn ($v) => filled($v)),
            'highlight' => array_filter($validated['highlight'] ?? [], fn ($v) => filled($v)),
            'body' => array_filter($validated['body'], fn ($v) => filled($v)),
            'meta' => array_filter($validated['meta'] ?? [], fn ($v) => filled($v)),
            'image_path' => $validated['imagePath'] ?: null,
        ];
```

Per-locale fields in the tabs: Title (required EN), Highlight, Body (textarea), Meta. Below the tabs, one non-translatable field:

```blade
            <flux:field>
                <flux:label>Image path</flux:label>
                <flux:input wire:model="imagePath" placeholder="images/about/chess.webp" />
                <flux:description>Relative to public/. Empty renders the striped placeholder plate.</flux:description>
                <flux:error name="imagePath" />
            </flux:field>
```

Add a `flux:text` line under the modal heading: `The highlight is rendered gold inside the title — it must appear in the title verbatim.`

- [ ] **Step 5: Register the routes and navigation**

`routes/web.php`, inside the admin group beside the other manage routes:

```php
    Route::livewire('dashboard/path-nodes', 'pages::manage.path-nodes')->name('manage.path-nodes');
    Route::livewire('dashboard/interests', 'pages::manage.interests')->name('manage.interests');
```

`resources/views/pages/⚡dashboard.blade.php` — import `App\Models\Interest` and `App\Models\PathNode`, then add to `tiles()`:

```php
            ['label' => __('Path nodes'), 'icon' => 'map', 'count' => PathNode::count(), 'route' => 'manage.path-nodes'],
            ['label' => __('Interests'), 'icon' => 'sparkles', 'count' => Interest::count(), 'route' => 'manage.interests'],
```

and to `quickActions()`:

```php
            ['label' => __('Add path node'), 'icon' => 'map', 'route' => 'manage.path-nodes'],
            ['label' => __('Add interest'), 'icon' => 'sparkles', 'route' => 'manage.interests'],
```

If either icon is missing from the installed Heroicons set the page will throw — verify by loading `/dashboard` in the test run; fall back to `identification` and `heart` if so.

`resources/views/layouts/app/sidebar.blade.php`, in the Content group:

```blade
                    <flux:sidebar.item icon="map" :href="route('manage.path-nodes')" :current="request()->routeIs('manage.path-nodes')" wire:navigate>
                        {{ __('Path nodes') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="sparkles" :href="route('manage.interests')" :current="request()->routeIs('manage.interests')" wire:navigate>
                        {{ __('Interests') }}
                    </flux:sidebar.item>
```

- [ ] **Step 6: Run the tests to verify they pass**

```bash
docker exec portfolio-2-app-1 php artisan test --compact --filter="PathNodesManagement|InterestsManagement|Dashboard|AdminAccess"
docker exec portfolio-2-app-1 vendor/bin/pint --dirty --format agent
```
Expected: PASS.

- [ ] **Step 7: Commit**

```bash
git add resources/views/pages routes/web.php resources/views/layouts/app/sidebar.blade.php tests/Feature
git commit -m "feat(admin): manage path nodes and interests"
```

---

### Task 7: Admin — stats tiers, the about flag and the new settings

**Files:**
- Modify: `resources/views/pages/manage/⚡stats.blade.php`, `⚡experiences.blade.php`, `⚡site-content.blade.php`
- Test: `tests/Feature/SiteContentManagementTest.php`, `tests/Feature/ExperienceManagementTest.php`

**Interfaces:**
- Consumes: `Stat::TIERS`, `Stat->note`, `Experience->show_on_about`, the six `Setting` keys.
- Produces: nothing later tasks read.

- [ ] **Step 1: Write the failing tests**

Append to `tests/Feature/SiteContentManagementTest.php`:

```php
test('a stat can be saved as a hero tile with a note', function () {
    Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.stats')
        ->set('value', ['en' => '5+', 'cs' => '5+'])
        ->set('text', ['en' => 'Projects shipped', 'cs' => 'Vydaných projektů'])
        ->set('tier', 'hero')
        ->set('note', ['en' => 'Client work and one platform.', 'cs' => 'Zakázky a jedna platforma.'])
        ->call('save')
        ->assertHasNoErrors();

    $stat = Stat::first();

    expect($stat->tier)->toBe('hero')
        ->and($stat->note)->toEqual(['en' => 'Client work and one platform.', 'cs' => 'Zakázky a jedna platforma.']);
});

test('a stat rejects an unknown tier', function () {
    Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.stats')
        ->set('text', ['en' => 'Whatever', 'cs' => 'Cokoliv'])
        ->set('tier', 'gigantic')
        ->call('save')
        ->assertHasErrors(['tier']);
});

test('the site content editor persists the about page copy and the fact strip', function () {
    $component = Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.site-content')
        ->set('texts.about_intro_lead', ['en' => 'I build backends.', 'cs' => 'Stavím backendy.'])
        ->set('texts.about_available', ['en' => 'Available', 'cs' => 'Volný'])
        ->set('texts.about_cta_title', ['en' => 'Need something built?', 'cs' => 'Potřebujete něco postavit?'])
        ->set('texts.about_cta_sub', ['en' => 'Write me.', 'cs' => 'Napište mi.'])
        ->set('lists.about_intro_body', ['en' => "First paragraph.\nSecond paragraph.", 'cs' => "První odstavec.\nDruhý odstavec."])
        ->set('facts.about_facts', ['en' => "Based in | Pardubice, CZ\nFocus | Backend", 'cs' => "Bydliště | Pardubice, ČR\nZaměření | Backend"]);

    // The editor saves every key at once, so the other required fields must be filled.
    $component->call('save')->assertHasNoErrors();

    expect(Setting::list('about_intro_body', 'en'))->toBe(['First paragraph.', 'Second paragraph.'])
        ->and(Setting::list('about_facts', 'cs'))->toBe([
            ['label' => 'Bydliště', 'value' => 'Pardubice, ČR'],
            ['label' => 'Zaměření', 'value' => 'Backend'],
        ]);
});

test('an empty availability line is allowed and means unavailable', function () {
    Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.site-content')
        ->set('texts.about_available', ['en' => '', 'cs' => ''])
        ->call('save')
        ->assertHasNoErrors();

    expect(Setting::text('about_available', 'en'))->toBe('');
});
```

The existing `site content editor persists settings and every rotating role list` test sets every key the component validates; extend that `->set(...)` chain with the new keys so it keeps passing, and mount the component in the two new site-content tests the same way (seed `SettingSeeder` first if `mount()` needs existing values).

Append to `tests/Feature/ExperienceManagementTest.php`:

```php
test('an experience can be flagged onto the about page', function () {
    $experience = Experience::factory()->create();

    Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.experiences')
        ->call('openEdit', $experience->id)
        ->set('show_on_about', true)
        ->call('save')
        ->assertHasNoErrors();

    expect($experience->fresh()->show_on_about)->toBeTrue();
});
```

- [ ] **Step 2: Run the tests to verify they fail**

```bash
docker exec portfolio-2-app-1 php artisan test --compact --filter="SiteContentManagement|ExperienceManagement"
```
Expected: FAIL — `Property [$tier] not found`.

- [ ] **Step 3: Extend the stats manage page**

In `⚡stats.blade.php`: add `public string $tier = 'normal';` and `public array $note = ['en' => '', 'cs' => ''];`, hydrate both in `openEdit()`, reset them in `resetForm()`, add validation

```php
            'tier' => ['required', 'in:'.implode(',', Stat::TIERS)],
            'note' => ['nullable', 'array'],
            'note.en' => ['nullable', 'string', 'max:300'],
            'note.cs' => ['nullable', 'string', 'max:300'],
```

and to `$data`:

```php
            'tier' => $validated['tier'],
            'note' => $validated['tier'] === 'hero'
                ? (array_filter($validated['note'] ?? [], fn ($v) => filled($v)) ?: null)
                : null,
```

Add a `Tier` column to the table between `Caption (EN)` and `Source`, rendering `{{ $stat->tier }}`. In the modal's non-translatable grid add:

```blade
                <flux:field>
                    <flux:label>Bento tier</flux:label>
                    <flux:select wire:model.live="tier">
                        <flux:select.option value="hero">Hero — spans two columns, carries a note</flux:select.option>
                        <flux:select.option value="normal">Normal</flux:select.option>
                        <flux:select.option value="soft">Soft — the jokes, dimmed</flux:select.option>
                    </flux:select>
                    <flux:error name="tier" />
                </flux:field>
```

and, inside the locale tabs, the note fields shown only for the hero tier (`wire:model.live` on the select is what makes this react):

```blade
                    @if ($tier === 'hero')
                        <flux:field>
                            <flux:label>Supporting line</flux:label>
                            <flux:textarea wire:model="note.en" rows="2" placeholder="e.g. Client work, school projects and one platform in production." />
                            <flux:error name="note.en" />
                        </flux:field>
                    @endif
```

(and the `cs` twin in the Czech slot).

- [ ] **Step 4: Extend the experiences manage page**

In `⚡experiences.blade.php`: add `public bool $show_on_about = false;`, hydrate it in `openEdit()` (`$this->show_on_about = (bool) $experience->show_on_about;`), reset it in the reset method, validate `'show_on_about' => ['boolean']`, add `'show_on_about' => $this->show_on_about,` to the saved `$data`, and add the checkbox next to the existing `is_special` one:

```blade
                    <flux:checkbox wire:model="show_on_about" label="Show on the about page" />
                    <flux:error name="show_on_about" />
```

- [ ] **Step 5: Extend the site-content page**

In `⚡site-content.blade.php`:

1. Add `about_intro_lead`, `about_available`, `about_cta_title`, `about_cta_sub` to `$textKeys`.
2. Rename nothing; add a third bucket for plain string lists so `about_intro_body` does not sit in `$roleListKeys` (whose label copy says "rotating role"):

```php
    /** Plain translatable string lists: key => ['en'=>.., 'cs'=>..], newline-separated. */
    public array $lists = [];

    /** @var array<int, string> Keys edited as a newline-separated list of paragraphs. */
    public array $listKeys = ['about_intro_body'];

    /** Label/value strips: key => ['en'=>.., 'cs'=>..], one `label | value` per line. */
    public array $facts = [];

    /** @var array<int, string> Keys edited as `label | value` lines. */
    public array $factKeys = ['about_facts'];
```

3. `mount()` fills them:

```php
        foreach ($this->listKeys as $key) {
            $this->lists[$key] = [
                'en' => implode("\n", Setting::list($key, 'en')),
                'cs' => implode("\n", Setting::list($key, 'cs')),
            ];
        }

        foreach ($this->factKeys as $key) {
            $this->facts[$key] = [
                'en' => $this->joinFacts(Setting::list($key, 'en')),
                'cs' => $this->joinFacts(Setting::list($key, 'cs')),
            ];
        }
```

4. Two helpers beside `splitLines()`:

```php
    /**
     * `label | value` lines to a list of maps, dropping malformed lines.
     *
     * @return array<int, array{label: string, value: string}>
     */
    private function splitFacts(string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $value))
            ->map(fn (string $line) => array_map('trim', explode('|', $line, 2)))
            ->filter(fn (array $parts) => count($parts) === 2 && filled($parts[0]) && filled($parts[1]))
            ->map(fn (array $parts) => ['label' => $parts[0], 'value' => $parts[1]])
            ->values()
            ->all();
    }

    /** @param array<int, mixed> $facts */
    private function joinFacts(array $facts): string
    {
        return collect($facts)
            ->filter(fn ($fact) => is_array($fact))
            ->map(fn (array $fact) => ($fact['label'] ?? '').' | '.($fact['value'] ?? ''))
            ->implode("\n");
    }
```

5. `save()` validates the new buckets — `about_available` is **nullable**, since empty means unavailable, so it must not go into `$textKeys`' required loop. Give it its own rule after the loop:

```php
        $rules['texts.about_available.en'] = ['nullable', 'string', 'max:2000'];
        $rules['texts.about_available.cs'] = ['nullable', 'string', 'max:2000'];

        foreach ($this->listKeys as $key) {
            $rules["lists.{$key}.en"] = ['required', 'string'];
            $rules["lists.{$key}.cs"] = ['nullable', 'string'];
        }

        foreach ($this->factKeys as $key) {
            $rules["facts.{$key}.en"] = ['required', 'string'];
            $rules["facts.{$key}.cs"] = ['nullable', 'string'];
        }
```

and persists them:

```php
        foreach ($this->listKeys as $key) {
            Setting::updateOrCreate(['key' => $key], ['value' => [
                'en' => $this->splitLines($this->lists[$key]['en']),
                'cs' => $this->splitLines($this->lists[$key]['cs'] ?: $this->lists[$key]['en']),
            ]]);
        }

        foreach ($this->factKeys as $key) {
            Setting::updateOrCreate(['key' => $key], ['value' => [
                'en' => $this->splitFacts($this->facts[$key]['en']),
                'cs' => $this->splitFacts($this->facts[$key]['cs'] ?: $this->facts[$key]['en']),
            ]]);
        }
```

The `about_available` write must skip the `?:` fallback the other text keys use — an empty Czech line means unavailable, not "copy the English".

6. Add an `'About me page' => ['about_intro_lead', 'about_intro_body', 'about_facts', 'about_available', 'about_cta_title', 'about_cta_sub']` entry to `$groups`, and teach the template's `@if` chain about the two new field types — `$this->isList($key)` renders a textarea bound to `lists.…`, `$this->isFacts($key)` renders a textarea bound to `facts.…` with the description `One fact per line, as "Label | Value".`. Both helpers mirror `isRoleList()`.

- [ ] **Step 6: Run the tests to verify they pass**

```bash
docker exec portfolio-2-app-1 php artisan test --compact --filter="SiteContentManagement|ExperienceManagement"
docker exec portfolio-2-app-1 vendor/bin/pint --dirty --format agent
```
Expected: PASS.

- [ ] **Step 7: Commit**

```bash
git add resources/views/pages/manage tests/Feature
git commit -m "feat(admin): edit stat tiers, the about flag and the about page copy"
```

---

### Task 8: Retire about_cards and correct the docs

Do not start this task until `/about-me` has been verified in the browser in **both** locales (Task 5 done, `npm run build` run, page loaded at http://localhost:8008/about-me and with the language toggled). `about_cards` holds the only copy of the transplanted prose; the drop is irreversible.

**Files:**
- Create: `database/migrations/2026_08_17_000005_drop_about_cards_table.php`
- Delete: `app/Models/AboutCard.php`, `database/factories/AboutCardFactory.php`, `database/seeders/AboutCardSeeder.php`, `resources/views/pages/manage/⚡about-cards.blade.php`
- Modify: `database/seeders/DatabaseSeeder.php`, `routes/web.php`, `resources/views/pages/⚡dashboard.blade.php`, `resources/views/layouts/app/sidebar.blade.php`, `resources/lang/{en,cs}/pages/about-me.php`, `tests/Feature/SiteContentManagementTest.php`, `CLAUDE.md`

**Interfaces:**
- Consumes: everything above; nothing consumes this task.

- [ ] **Step 1: Take the database dump**

```bash
docker exec portfolio-2-db-1 pg_dump -U postgres portfolio > /tmp/portfolio-before-about-cards-drop.sql
ls -lh /tmp/portfolio-before-about-cards-drop.sql
```
(Adjust the user if `docker exec portfolio-2-db-1 env | grep POSTGRES_USER` says otherwise.) Expected: a non-empty file. **Do not continue without it.**

- [ ] **Step 2: Write the failing test**

Replace the `can create an about card` test in `tests/Feature/SiteContentManagementTest.php` with a guard that the route is gone:

```php
test('the about cards manager is retired', function () {
    expect(Route::has('manage.about-cards'))->toBeFalse()
        ->and(class_exists(\App\Models\AboutCard::class))->toBeFalse();
});
```

with `use Illuminate\Support\Facades\Route;` at the top, and drop the `use App\Models\AboutCard;` import.

- [ ] **Step 3: Run it to verify it fails**

```bash
docker exec portfolio-2-app-1 php artisan test --compact --filter="about cards manager is retired"
```
Expected: FAIL — the route and the class both still exist.

- [ ] **Step 4: Remove everything**

Write the migration:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('about_cards');
    }

    /**
     * Recreates the empty shell only. The card copy lived here alone and now
     * lives in path_nodes, interests, experiences and the about_* settings —
     * rolling back does not bring the prose back.
     */
    public function down(): void
    {
        Schema::create('about_cards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->jsonb('title')->nullable();
            $table->jsonb('text')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }
};
```

Then delete the four files, remove `AboutCardSeeder::class` from `DatabaseSeeder`, remove the route line from `routes/web.php`, remove the tile, the quick action and the `use App\Models\AboutCard;` import from `⚡dashboard.blade.php`, remove the sidebar item, and delete the `card1_*`…`card5_*` keys from both `resources/lang/{en,cs}/pages/about-me.php`.

Verify nothing is left:

```bash
grep -rn "AboutCard\|about-cards\|about_cards" --include="*.php" --include="*.blade.php" . --exclude-dir=vendor --exclude-dir=node_modules --exclude-dir=docs
```
Expected: only the drop migration's own comment.

- [ ] **Step 5: Correct CLAUDE.md**

In the **Admin (dashboard)** section, replace the five-page list with the eleven that exist after this work:

```
Livewire 4 single-file components in `resources/views/pages/manage/⚡{experiences,badges,articles,links,projects,stats,reviews,path-nodes,interests,site-content}.blade.php` (note the `⚡` filename prefix), registered with `Route::livewire()` in `routes/web.php` behind `auth` + `verified` + `admin`.
```

and, in the **Frontend** section, add one line after the section-head bullet:

```
- `/about-me` is composed of seven sections (intro frame, timeline, stats bento, interest cells, show-up strip, reviews carousel, contact band) backed by `PathNode`, `Interest`, tiered `Stat`, `show_on_about` `Experience`, `Review` and the `about_*` `Setting` keys. Rules: `resources/css/pages/about-me.css` plus `components/{image-slot,availability-dot,pill-button}.css`.
```

- [ ] **Step 6: Run the whole suite**

```bash
docker exec portfolio-2-app-1 php artisan migrate --no-interaction
docker exec portfolio-2-app-1 php artisan db:seed --no-interaction
docker exec portfolio-2-app-1 php artisan test --compact
docker exec portfolio-2-app-1 vendor/bin/pint --dirty --format agent
```
Expected: the full suite passes. Any failure naming `AboutCard` is a leftover reference — fix the file, do not delete the test.

- [ ] **Step 7: Commit**

```bash
git add -A
git commit -m "refactor(about-me): retire the about_cards table and its admin page"
```

---

## Self-Review Notes

- **Spec coverage.** New tables → Task 1; seeders/settings/config → Task 2; components + `--c-available` → Task 3; controller/view/copy → Task 4; CSS + browser tests → Task 5; new admin pages → Task 6; changed admin pages → Task 7; retirement + `CLAUDE.md` → Task 8. The spec's "out of scope" toolbox is absent by design, and the section order leaves its gap between sections 1 and 2.
- **Deliberate deviations** are listed under *Plan-level decisions* above: the token alias split, the contact band without a section head, the missing reduced-motion browser assertion, and the `label | value` fact editor.
- **Naming consistency.** `highlightedTitle()` (Interest) mirrors but does not share a name with `highlightedText()` (Review) — both exist, on different models. Livewire title properties are `$nodeTitle` / `$interestTitle` because `$title` collides with the `#[Title]` attribute convention used by `⚡about-cards`' `$cardTitle`.
