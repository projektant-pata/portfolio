# About-me page recomposition — design

**Date:** 2026-08-17
**Status:** approved for planning
**Source:** `docs/handoff/about-me/` (design handoff: `README.md`, `about.html`, `about.css`)

## Problem

`/about-me` currently renders two sections: five identically-styled prose cards in a
two-column masonry (`.about-me-content`), and eleven equal-weight stat cards in a
staggered 4:3:4 pyramid (`.about-me-stats-cards`). Every card carries the same border,
background and padding, so nothing on the page has hierarchy — a serious metric and a
joke about coffee are typeset identically, and the prose is a wall with no entry point.
There is no photograph of Richard anywhere between the hero and the footer, which is the
page's largest credibility gap.

## Goal

Replace the page body with seven sections whose silhouettes differ on purpose, so the
page has rhythm instead of repetition, and so the content that carries weight looks like
it does. Keep every existing token, component and admin pattern; introduce new structure
only where no existing structure fits.

## Scope

Seven of the handoff's eight sections ship in this work:

| # | Section | Silhouette | Data source |
|---|---------|------------|-------------|
| 1 | Intro | bordered frame: copy + portrait + fact strip | `Setting` keys |
| 2 | The short version | borderless left-rail timeline | new `PathNode` model |
| 3 | Stats | bento — 2 hero tiles, 6 plain, 2 soft | existing `Stat` model, extended |
| 4 | Off the screen | 3 photo cells in one frame | new `Interest` model |
| 5 | Things I show up for | flat outlined tile strip | existing `Experience`, filtered |
| 6 | References | existing reviews carousel | existing `Review` model |
| 7 | Contact | filled gold-tinted band | `Setting` keys + `config/portfolio.php` |

**Out of scope:** the handoff's section 2, "The toolbox" (a four-column frame of
technologies with usage frequencies). The owner has decided to approach the toolbox
differently in later work. Nothing in this design blocks adding it between sections 1 and
2 later; the section order above leaves the gap where it belongs.

Dropping the toolbox costs one contrast beat the handoff designed for: framed intro,
framed toolbox, then a deliberately borderless timeline. With the toolbox gone the page
reads framed → borderless, which still works but is a weaker transition. No compensation
is planned; revisit when the toolbox lands.

## What the codebase already provides

Verified before writing this design:

- **Every token the handoff uses already exists** in the `@theme` block of
  `resources/css/app.css`: `--c-bg`, `--c-fg`, `--c-muted`, `--c-surface`,
  `--c-surface-sunken`, `--c-primary`, `--c-primary-lt`, `--c-primary-dk`,
  `--c-primary-fade`, `--r-card`, `--r-card-sm`, `--r-pill`, `--border-w`, `--sp-section`,
  `--t-fast`, `--font-display`, `--font-mono`, `--font-sans`, and the `--fw-*` weights.
  No new tokens are needed except one (below).
- **`<x-portfolio.section-head>`** already implements the handoff's `.ab-sec-head`
  system — gold-rule eyebrow, optional ghost wordmark, `h2` with gold `<em>`, right-hand
  note. Sections map onto it one-to-one.
- **The ghost-wordmark descender bug** the handoff calls out ("My stats" rendering as
  "Mu stats") is already fixed in `resources/css/components/section-head.css:49-50` via
  `line-height: 1.12` + `padding-bottom: .1em`.
- **The reviews carousel already ships** on the home page: `.reviews-carousel` markup in
  `resources/views/welcome.blade.php:136-165`, a `Review` model with `initials()` and
  `highlightedText()`, an admin page, and eight real reviews in the database. The
  handoff's placeholder testimonials are not needed.
- **Badge hexes match the handoff exactly.** The `badges` table holds
  `competition #EAB308`, `certificate #34D399`, `it #818CF8`, `hardware #F59E0B` and
  others whose values are literally the `--bc` colours in `about.html`. The handoff was
  authored against this palette.
- **Admin CRUD already exists** for about-cards, stats, reviews and site-content, all as
  Livewire single-file components under `resources/views/pages/manage/`. `CLAUDE.md` is
  out of date on this point and should be corrected as part of the work.
- **Contact links are already centralised** in `config/portfolio.php` under `social`
  (email, github, linkedin, instagram, x, chess).

## Deviations from the handoff

The handoff is a design reference, not code to lift. Three deliberate divergences:

1. **Ghost wordmark placement.** `about.css` positions `.ab-ghost` absolutely above the
   section head. The project's `sechead-ghost` sits in flow between the eyebrow and the
   `h2`, is `aria-hidden`, and is covered by `tests/Feature/SectionHeadTest.php`. The
   project's version wins; the stats section passes `:ghost` to the existing component.
2. **Page rhythm.** The handoff wraps sections in `.ab-page` with
   `gap: var(--sp-section)`. The project already gets that rhythm from
   `.portfolio-section`, which additionally owns the scroll-reveal behaviour. No
   `.ab-page` wrapper is introduced.
3. **Class prefix.** `.ab-*` was chosen in the handoff to avoid collisions inside the
   design-system project. In this repo the page's classes follow the existing
   `about-me-*` convention.

## Data layer

### New tables

**`path_nodes`** — the timeline in section 2.

| Column | Type | Notes |
|---|---|---|
| `id` | uuid | `HasUuids`, matching `AboutCard` / `Stat` / `Review` |
| `year` | json | translatable — holds `"2019"` but also `"Now"` / `"Teď"` |
| `title` | json | translatable |
| `body` | json | translatable |
| `is_now` | boolean | default false; drives the gold title on the final node |
| `sort_order` | integer | |

**`interests`** — the three off-screen cells in section 4.

| Column | Type | Notes |
|---|---|---|
| `id` | uuid | |
| `title` | json | translatable |
| `highlight` | json | translatable; the phrase rendered gold inside the title |
| `body` | json | translatable |
| `meta` | json | translatable; the monospace footer line |
| `image_path` | string, nullable | empty renders the placeholder plate |
| `sort_order` | integer | |

`highlight` follows the `Review::highlightedText()` precedent: escape the title first,
then wrap the escaped highlight in a `<span>` only if it appears verbatim. Never render
raw model text unescaped.

### Altered tables

**`stats`** gains:

- `tier` — string, default `'normal'`, one of `hero` / `normal` / `soft`. Two `hero`
  tiles span two bento columns and carry a supporting sentence; `soft` tiles are the
  jokes, rendered transparent with the number at 55% opacity.
- `note` — json, nullable, translatable. The hero tiles' supporting sentence. Ignored for
  the other tiers.

**`experiences`** gains:

- `show_on_about` — boolean, default false. Selects which experience rows surface as
  show-up tiles in section 5.

### Retired

**`about_cards`** is dropped, along with `App\Models\AboutCard`, its factory, seeder,
`resources/views/pages/manage/⚡about-cards.blade.php`, its route
(`routes/web.php:31`), its dashboard tile and sidebar entry
(`resources/views/pages/⚡dashboard.blade.php:33,53`,
`resources/views/layouts/app/sidebar.blade.php:49`), and its CSS.

Its five cards' copy is transplanted, not discarded:

| Old card | New home |
|---|---|
| card1 *About me* | intro lead + first prose column (`Setting`) |
| card2 *What do I like?* | off-screen cells: chess, music (`Interest`) |
| card3 *What drives me?* | intro second prose column (`Setting`) |
| card4 *How did we get here?* | timeline nodes 2019 / 2020 / 2022 / 2024 (`PathNode`) |
| card5 *Volunteering?* | show-up tiles: PEER, Czech Day Against Cancer (`Experience`) |

The card copy also carries typos and unfinished text ("rom a young age", "Lorem Ipsum
Pyco"). Transplanting is a rewrite, not a copy — the new seeded copy is clean.

### New `Setting` keys

Edited through the existing `⚡site-content` page, seeded by `SettingSeeder`:

| Key | Shape | Used by |
|---|---|---|
| `about_intro_lead` | translatable string, may contain `<em>` | intro lead statement |
| `about_intro_body` | translatable list of 2 strings, may contain `<b>` | intro prose columns |
| `about_facts` | translatable list of `{label, value}` maps | intro fact strip |
| `about_available` | translatable string; empty means unavailable | availability line, both places |
| `about_cta_title` | translatable string, may contain `<em>` | contact heading |
| `about_cta_sub` | translatable string | contact sub-line |

`Setting::list()` is documented as returning `list<string>`. `about_facts` returns a list
of maps, so the docblock is relaxed to `array<int, mixed>` and the method's behaviour is
left unchanged. The fact strip's final cell renders the availability dot when
`about_available` is non-empty; the contact band reads the same key. One flag, two
surfaces — the handoff's requirement.

### Derived values, not invented ones

The handoff notes several bento numbers could be computed. `Stat` already computes `age`
and `years_experience` through its `source` column and `displayValue()`. This work adds
no new computed sources — `github-repos` and the rest stay manual, exactly as today.
Extending `source` is a separate, later change.

## Controller

`AboutMeController::__invoke()` loads:

```php
$pathNodes = PathNode::orderBy('sort_order')->get();
$interests = Interest::orderBy('sort_order')->get();
$stats     = Stat::orderBy('sort_order')->get();
$showUps   = Experience::where('show_on_about', true)
    ->with('badges')
    ->orderBy('sort_order')
    ->get();
$reviews   = Review::orderBy('sort_order')->get();
```

`with('badges')` is required: each show-up tile reads its first badge for the kind label
and the tile's accent colour, and without eager loading four tiles mean four extra
queries.

The bento groups `$stats` by `tier` in the view, not the query — the hero tiles must
render in `sort_order` position relative to the rest, and a second query would lose that.

## View

`resources/views/about-me.blade.php` is rewritten. The hero
(`<x-portfolio.dock-hero>`) is untouched. Each section is a `.portfolio-section` opened by
`<x-portfolio.section-head>`, except the intro, which keeps
`portfolio-section--no-reveal` for the reason already documented in the file: the hero
leaves the intro's top edge inside the first viewport, so it must be painted, not
revealed.

Three new components under `resources/views/components/portfolio/`:

- **`image-slot`** — props `path`, `caption`, `alt`, `height`. Renders an `<img>` when
  `path` is set, otherwise the striped placeholder plate with a monospace caption naming
  the asset and its target size. Used four times here (portrait plus three interests) and
  reusable by any future section that is waiting on artwork.
- **`availability-dot`** — the 7px green dot with the pulsing ring, suppressed under
  `prefers-reduced-motion`. Used in the intro fact strip and the contact band.
- **`pill-button`** — props `href`, `variant` (`gold` / `ghost`), `download`. The
  contact row's four buttons. The existing `.btn-gold` / `.btn-gold-subtle` rules carry
  the same semantics but live in `resources/css/pages/dashboard.css` and style Flux
  `<flux:button>` elements inside the admin, which never loads on a public page. The
  public buttons therefore get their own component rather than reaching into an
  admin-scoped stylesheet. Unifying the two is a later, separate cleanup.

Show-up tile mapping, per `Experience` row: kind label = first badge's translated name,
accent colour = that badge's `color`, year = `year`, title = `title`, one-line body =
`subtitle`. Rows with no badge fall back to `--c-primary` for the accent and omit the kind
label rather than rendering an empty one.

Every section renders nothing — not an empty frame — when its collection is empty. The
page must survive a fresh database, as the other public pages already do.

## CSS

`resources/css/pages/about-me.css` is rewritten. `.about-me-content` (masonry),
`.about-me-card` and the `.about-me-stats-cards` pyramid are deleted outright.

Three files are extracted to `resources/css/components/`, following the
`section-head.css` precedent, and imported by `about-me.css`:

- `image-slot.css`
- `availability-dot.css`
- `pill-button.css`

`vite.config.js` entries are unchanged: `about-me.css` remains the page's single entry and
the new component files are `@import`ed into it, exactly as `dock-hero.css` and
`section-head.css` already are.

One new token joins the `@theme` block in `resources/css/app.css`:

```css
--c-available: #4ADE80;
```

with a light-theme override alongside the other `.light` colour overrides. The handoff
calls this the page's only literal; making it a token keeps the literal in one place
rather than in five rules across two components.

## Admin

- **New:** `resources/views/pages/manage/⚡path-nodes.blade.php` and
  `⚡interests.blade.php`, both copying the structure of `⚡about-cards.blade.php`
  (search, drag reorder writing `sort_order`, create/edit modal with `en`/`cs` fields,
  delete confirmation). Routes registered in `routes/web.php` beside the existing manage
  routes; dashboard tiles and sidebar entries added.
- **Changed:** `⚡stats` gains a `tier` select and the `note` fields (shown only when
  tier is `hero`). `⚡experiences` gains a `show_on_about` checkbox. `⚡site-content`
  gains the six new `Setting` keys, including a repeatable editor for `about_facts`.
- **Removed:** `⚡about-cards` and everything pointing at it.

## Assets

No image blocks this work. Placeholders ship and are replaced with no code change:

- `images/about/portrait.webp` — 900×1200, the intro portrait
- `images/about/chess.webp`, `music.webp`, `homelab.webp` — 1200×900, the interest cells

The interest cells read `image_path` from the model, so those three are droppable through
the admin. The portrait is not model-backed; it gets a `portrait` entry in the existing
`config/portfolio.php` `hero_images` array — empty by default, so the intro renders the
placeholder plate until a file lands, and swapping it in is a one-line config change with
no template edit. That mirrors the `experience_dock` entry already handled that way.

`config/portfolio.php` gains `cv_path`, default `''`. The contact band renders the
"Download CV" button only when it is non-empty — a download button pointing at nothing is
worse than no button.

## Testing

Every change is programmatically tested, per the project's test-enforcement rule. Run with
`docker exec portfolio-2-app-1 php artisan test --compact`.

**`tests/Feature/AboutMePageTest.php`** — rewritten. The existing assertions on
`about-me-content` and `about-me-stats-cards` die with those classes. New coverage:

- all seven sections render, in order
- the page carries exactly one ghost wordmark (existing assertion, kept)
- the intro head still carries no ghost (existing assertion, kept)
- `show_on_about` filters show-ups: a row with the flag false never appears
- a show-up with no badge omits the kind label and does not error
- each section renders nothing on an empty database
- `cs` locale renders Czech copy for the new sections
- the availability dot appears in both the intro and the contact band when
  `about_available` is set, and in neither when it is empty
- the CV button is absent when `config('portfolio.cv_path')` is empty

**Unit** — `Stat` tier grouping; `Setting::list()` returning the `about_facts` map shape;
`Interest` highlight escaping (a title containing `<script>` must not render as markup).

**Livewire** — `PathNodesManagementTest` and `InterestsManagementTest` covering create,
update, delete and reorder, mirroring the existing management tests. Additions to the
stats and experiences management tests for `tier`/`note` and `show_on_about`.

**Browser** (`tests/Browser/`) — the about page loads with no console errors, the reviews
carousel advances, and the dot's animation is absent under `prefers-reduced-motion`.

**Removed** — any test asserting the `about_cards` route, model or page.

`DatabaseSeederTest` must be updated for the new seeders and the removed one.

Czech copy is drafted for every new lang string and every seeded row, for the owner to
review afterwards.

## Migration safety

`about_cards` holds the only copy of the card2/4/5 prose. The order is not negotiable:

1. Create `path_nodes` and `interests`; add `tier` / `note` to `stats`; add
   `show_on_about` to `experiences`.
2. Write the new seeders (`PathNodeSeeder`, `InterestSeeder`) and extend `SettingSeeder`,
   `StatSeeder` and `ExperienceSeeder` with the transplanted, rewritten copy.
3. Build the page, verify it renders correctly in both locales.
4. Only then run the migration dropping `about_cards`, and delete the model, factory,
   seeder, admin page, routes and nav entries.

Take a database dump before step 4. The drop is irreversible and the source copy is
prose that exists nowhere else in the repository except `resources/lang/*/pages/about-me.php`,
whose `card*` keys are also removed in this work.

## Documentation

`CLAUDE.md` is corrected in the same change: the admin section lists only five manage
pages when nine exist today, and eleven after this work.

## Open items — not blockers

1. Photographs: portrait, chess, music, homelab. Placeholders ship without them.
2. CV file. Button hidden until `cv_path` is set.
3. The toolbox section, deferred by the owner to separate work.
4. Unifying the public `pill-button` styles with the admin's `.btn-gold` rules, once both
   surfaces are known to want the same thing.
