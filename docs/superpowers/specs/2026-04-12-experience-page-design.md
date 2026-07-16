# Experience Page — Design Spec

**Date:** 2026-04-12

## Overview

Dedicated public `/experience` page replacing the current placeholder (which rendered the home page). Combines elements from three brainstormed variants: multi-select filter tabs, Pinterest-style masonry grid with a center vertical timeline line and per-card dots, and styled experience cards with badge display.

---

## Layout

### Filter Tabs

Positioned above the grid. Pills for:
- **Práce** (`data-filter="work"`)
- **Život** (`data-filter="life"`)
- One tab per distinct badge attached to any experience (using badge slug as filter key, badge color for styling)

**Behavior:**
- Default: nothing selected → all cards visible
- Multiple tabs can be active simultaneously (OR logic)
- Clicking an active tab deactivates it
- No "All" tab — deselecting everything shows everything

### Grid

Two-column masonry layout with a center vertical timeline line:

```
  [ Card A (left) ]     |     [ Card B (right) ]
                    [dot]|[dot]
  [ Card C (left) ]     |     [ Card D (right) ]
                    [dot]|[dot]
```

- Container: `display: flex` with two columns + a positioned center line div
- **Masonry distribution:** JavaScript places each visible card into the column with smaller `offsetHeight`
- Center line: `position: absolute; left: 50%; width: 2px; background: var(--c-primary-fade)`
- Dots: `::after` pseudo-element on each card, pointing toward center line; centered vertically on the card

### Experience Card

- Border: `1px solid var(--c-primary-lt)`, radius `var(--r-card)`
- Optional circular logo image (48px)
- Year label (`.mini`, gold color)
- Title (`h4`)
- Subtitle (`p`, muted)
- Optional content paragraph
- Badges row: colored pills using `badge.color` for border + text
- Links row: icon links (if any)
- Hover: border → `var(--c-primary)`, subtle box-shadow
- Special cards (`is_special = true`): larger dot + subtle gold background tint

---

## Implementation

### Files

| File | Action |
|---|---|
| `app/Http/Controllers/ExperienceController.php` | Create |
| `routes/web.php` | Update `/experience` route |
| `resources/views/experience.blade.php` | Create |
| `resources/css/pages/experience.css` | Fill (currently empty) |
| `resources/lang/en/layout/header.php` | Add `experience_title`, `experience_desc` |
| `resources/lang/cs/layout/header.php` | Add same keys in Czech |

### ExperienceController

```php
public function __invoke()
{
    $experiences = Experience::with('badges')
        ->orderBy('sort_order')
        ->get();

    $badges = $experiences
        ->flatMap(fn($e) => $e->badges)
        ->unique('id')
        ->values();

    return view('experience', compact('experiences', 'badges'));
}
```

### View Structure

```blade
<x-portfolio-layout :title="__('layout/header.experience_title')" ...>
  <section id="experience" class="portfolio-section" style="padding-top: var(--sp-section)">
    <h2>{{ __('home/experience.title') }}</h2>

    {{-- Filter tabs --}}
    <div id="exp-filters">
      <button class="exp-filter" data-filter="work">Práce</button>
      <button class="exp-filter" data-filter="life">Život</button>
      @foreach ($badges as $badge)
        <button class="exp-filter" data-filter="badge:{{ $badge->slug }}"
          style="--badge-color: {{ $badge->color }}">
          {{ $badge->getTranslation('name', $locale) }}
        </button>
      @endforeach
    </div>

    {{-- Hidden card pool (masonry source) --}}
    <div id="exp-cards-pool" style="display:none">
      @foreach ($experiences as $exp)
        <div class="exp-card {{ $exp->is_special ? 'exp-card--special' : '' }}"
             data-type="{{ $exp->type }}"
             data-badges="{{ json_encode($exp->badges->pluck('slug')) }}">
          {{-- card content --}}
        </div>
      @endforeach
    </div>

    {{-- Masonry grid --}}
    <div id="exp-grid">
      <div id="exp-grid-line"></div>
      <div id="exp-col-left" class="exp-col"></div>
      <div id="exp-col-right" class="exp-col"></div>
    </div>
  </section>
</x-portfolio-layout>
```

### JavaScript (multi-select + masonry)

```js
const pool = document.getElementById('exp-cards-pool');
const colLeft = document.getElementById('exp-col-left');
const colRight = document.getElementById('exp-col-right');
const allCards = Array.from(pool.querySelectorAll('.exp-card'));
const activeFilters = new Set();

function matchesFilters(card) {
  if (activeFilters.size === 0) return true;
  for (const f of activeFilters) {
    if (f.startsWith('badge:')) {
      const slugs = JSON.parse(card.dataset.badges || '[]');
      if (slugs.includes(f.slice(6))) return true;
    } else {
      if (card.dataset.type === f) return true;
    }
  }
  return false;
}

function layoutMasonry() {
  colLeft.replaceChildren();
  colRight.replaceChildren();
  allCards.forEach(card => {
    if (!matchesFilters(card)) return;
    const target = colLeft.offsetHeight <= colRight.offsetHeight ? colLeft : colRight;
    target.appendChild(card.cloneNode(true));
  });
}

document.querySelectorAll('.exp-filter').forEach(btn => {
  btn.addEventListener('click', () => {
    const f = btn.dataset.filter;
    if (activeFilters.has(f)) { activeFilters.delete(f); btn.classList.remove('active'); }
    else { activeFilters.add(f); btn.classList.add('active'); }
    layoutMasonry();
  });
});

layoutMasonry();
```

### Responsive

- `≤ 992px`: single column, center line hidden, dots hidden
- `≤ 576px`: padding reduced, filter tabs wrap

---

## Verification

1. Route `php artisan route:list --name=experience` → points to `ExperienceController`
2. `/experience` renders cards with correct data, year, badges, links
3. Filter tabs toggle; multiple can be active; deselecting all shows everything
4. Masonry distributes cards to shorter column
5. Dots aligned to center line
6. All existing tests pass: `php artisan test --compact`
7. Pint clean: `vendor/bin/pint --dirty --format agent`
