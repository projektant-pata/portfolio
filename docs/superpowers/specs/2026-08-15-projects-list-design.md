# Projects list — design

**Date:** 2026-08-15
**Handoff:** `newProjects.zip` → `design_handoff_projects/` (`README.md`, `projects.html`, `projects.css`)
**Route:** `GET /projects` → `ProjectsController` → `resources/views/projects.blade.php`

## Problem

The `/projects` page prints the year as the biggest thing in every row: an outlined `h2` at
`--fs-h2`, repeated once per project, with the project's own name below it at a third of the
size. A screen reader hears "2026", never "Portfolio", and three 2025 projects print "2025"
three times. Rows alternate left/right (`--reverse`), a marketing device that breaks the scan
line on an index and makes project names start at two different x positions.

Nothing on the page can be filtered, and no row carries kind, status, role, or a labelled link
— the only per-row metadata is the badge list.

## Solution

Keep the year, demote it to a **group divider**: the same stroked-gold treatment at a third of
the size, printed **once per year** on a rule above its rows, with that year's project count at
the far end. The row rail carries kind and status only. The project name becomes the heading.
A sticky filter bar sits above the list.

```
┌ dock hero (unchanged) ─ ghost wordmark "Projects" ─────────────────┐
└────────────────────────────────────────────────────────────────────┘

[ KIND (All|Personal|Client|School) · STACK ○chips ]  3 shown · 13 total   ← sticky top:0

2026 ──────────────────────────────────────────────── 1 PROJECT           ← sticky top:3.5rem
────────────────────────────────────────────────────────────────────
 Personal ┌────────────┐  Portfolio                          Details
 ● Live   │    shot    │  One paragraph, then stack chips
          └────────────┘  ▸ facts + links open here
────────────────────────────────────────────────────────────────────
 Client   ┌────────────┐  U Sladovny                         Details
```

## Decisions taken before the design

1. **No `.sechead` page head.** The handoff was written before the dock-hero rollout
   (`df6f01f`) and assumes `/projects` has no head at all. It does: `x-portfolio.dock-hero`
   already renders eyebrow, `h1`, roles, tags, and the ghost wordmark `Projects`. Adding a
   `.sechead` would put two ghosts on one page, which the handoff itself forbids. The dock hero
   stays; the filter bar becomes the first thing under it. The unstarted section-head plan
   (`docs/superpowers/plans/2026-08-15-section-head.md`) is untouched and stays home-page-only.
2. **Full data model.** `kind`, `client`, `status`, `role`, and typed links are added, with
   admin CRUD. The rail, the Kind filter, and the expanded panel are meaningless without them.
3. **No lazy loading.** The handoff specifies 6-row pages, an `IntersectionObserver` sentinel,
   skeleton rows, a server-side filter endpoint, and a live region for arrivals. The database
   holds 13 projects, 3 of them real. Every row renders; filtering runs in the browser, the way
   the experience page already filters. Skeleton CSS is not ported.
4. **Test rows are purged.** `Test #1`–`Test #10` are deleted and the three real projects
   (Portfolio, U Sladovny, SPŠE Hub) get real `kind`, `status`, `role`, and descriptions in both
   locales. The page ships with 3 rows across 3 years — every year head shows a single project,
   which is honest and is what the list actually is.

## Data model

### `projects` — new columns

| Column | Type | Notes |
|---|---|---|
| `kind` | `varchar(20)`, not null, default `'personal'` | `personal` \| `client` \| `school` |
| `client` | `varchar(255)`, nullable | Rail prints `Client · PekneWeby` when set and `kind = client` |
| `status` | `varchar(20)`, nullable | `live` \| `archived` \| `wip`; null hides the status line |
| `role` | `json`, nullable | `{"en": …, "cs": …}`, read with the existing `getTranslation()` |

`kind` and `status` are plain string columns with an application-level allow-list, matching
`experiences.type` (also a plain `varchar`). No Postgres enum, no PHP enum class — the project
has no enum precedent and a migration for a new value must stay cheap.

### `links` — new column

| Column | Type | Notes |
|---|---|---|
| `kind` | `varchar(20)`, not null, default `'live'` | `live` \| `repo` \| `article` |

The link **label is not stored**. It is read from `resources/lang/{en,cs}/pages/projects.php`
by kind (`Live site` / `Zdrojový kód` / …), which keeps both locales correct for free and keeps
one field out of the admin form. `links.img_url` keeps its column and its admin field; the
projects page stops rendering link icons, because the new design's links are text pills.

A project with **zero links** renders one dashed, non-interactive `.proj-link--none` pill
("No public repo"). Nothing is stored for that state.

### Backfill

- The 10 `Test #n` projects are deleted.
- Portfolio / U Sladovny / SPŠE Hub get `kind`, `status`, `role` (en + cs), and a one-paragraph
  first-person `description` in both locales.
- The 4 existing links get `kind`: `github.com` host → `repo`, everything else → `live`, then
  checked by hand.
- Delivered as an idempotent seeder so a fresh clone lands in the same state.

## Ordering

`ProjectsController` currently orders `sort_order` **first**, then `year`, then name, and only
then `groupBy('year')`. A global `sort_order` cannot survive year grouping — it scrambles which
project leads which year. Corrected to:

```php
Project::with(['badges', 'links'])
    ->orderBy('year', 'desc')
    ->orderBy('sort_order')
    ->orderByRaw("header->>'en'")
    ->get()
    ->groupBy('year');
```

Newest year first, `sort_order` breaking ties inside a year, name last. No manual featuring
order across years — the year does the sorting.

## Components

| File | Responsibility |
|---|---|
| `resources/views/components/portfolio/project-row.blade.php` | **Rewrite.** Rail, shot, body, expand panel, toggle. `reverse` prop deleted. |
| `resources/views/components/portfolio/project-year-head.blade.php` | **Create.** `h2` year, gradient rule, project count. |
| `resources/css/components/project-list.css` | **Create.** All `.proj-*` rules. |
| `resources/css/components/project-row.css` | **Delete.** Old `.projects-row*` zigzag. |
| `resources/css/pages/projects.css` | **Modify.** Import `project-list.css`; drop `.projects-year-*`. |

### Row markup

```html
<article class="proj-item" data-kind="client" data-stack='["laravel","php"]'>
  <div class="proj-rail">
    <p class="proj-kind">Client · PekneWeby</p>
    <p class="proj-status" data-state="live">Live</p>
  </div>
  <div class="proj-shot"><img src="…" alt="U Sladovny home page"></div>
  <div class="proj-body">
    <h3 class="proj-name">U Sladovny</h3>
    <p class="proj-desc">One paragraph.</p>
    <div class="proj-chips">…</div>
    <div class="proj-more" id="proj-more-{slug}">
      <dl class="proj-facts">…</dl>
      <div class="proj-links">…</div>
    </div>
  </div>
  <div class="proj-act">
    <button class="proj-toggle" aria-expanded="false" aria-controls="proj-more-{slug}">Details</button>
  </div>
</article>
```

`data-kind` and `data-stack` are what the filter JS reads — the same hidden-attribute contract
`exp-card` uses (`data-type`, `data-badges`).

### Values that must survive exactly

Grid `5.5rem 18rem 1fr 6rem`, gap `2rem`, padding `1.75rem 0`, hairline `border-top` in
`color-mix(--c-primary-fade 55%)` dropped on each group's first row. Shot `18rem × 11.5rem`,
`--r-card-sm`, `--c-surface-sunken`, `object-fit:cover`. Year head `2.6rem` Space Grotesk 700,
`line-height:.85`, `letter-spacing:-.04em`, `color:transparent`, `-webkit-text-stroke:1px
var(--c-primary)` — **full gold, not the faded stroke**, which vanishes at this size. Name
`1.4rem`, no gold `em`. Desc `.92rem`, weight 200, `line-height:1.6`, `max-width:52ch`. Chips
11px 600, border and text in `var(--bc)` at 55%.

No new tokens, no new colours. One literal: `#4ADE80` for the live dot.

### Specificity trap

`app.css:348` styles `.portfolio-page h2` as the centred watermark — `text-align:center`,
`margin-bottom:-0.45em`, `--fs-h2`, `--c-watermark` stroke. That is `0,1,1` and ties with a
bare `.proj-yhead h2`, leaving the year head at the mercy of stylesheet order. Every colliding
rule is written `.proj-list .proj-yhead h2` (`0,2,1`) so it wins outright — the same defence
`dock-hero.css:142` and the section-head spec already use.

`position: sticky` is safe: no ancestor of `.portfolio-main` sets `overflow` to anything but
`visible`.

## Filter bar

Client-side, no Apply button, no page reload. Vanilla JS in a `<script>` block at the foot of
`projects.blade.php`, matching `experience.blade.php` — the public pages carry no Livewire.

- `.proj-filters` — `position:sticky; top:0; z-index:5`, paints `var(--c-bg)`, hairline top and
  bottom, plus a `1.25rem` gradient fade below via `::after` so rows dissolve under it.
- **Kind** — `.proj-seg`, single-select, one pill with a sliding gold `.proj-seg-thumb`. Same
  control as `.exp-scope`: measure the thumb from the pressed button's `offsetWidth` /
  `offsetLeft`, re-measure on resize and after `document.fonts.ready`. Options: All, Personal,
  Client, School. A kind with no projects is still shown — the empty state explains itself.
- **Stack** — `.proj-fchip` multi-select, union (a row matches if it carries *any* pressed
  badge). Outline pill, 6px hue dot from `--bc`, pressed = 14% hue fill + halo ring. Built from
  the badges actually attached to projects, not the whole badge table.
- Both groups are real `<button>`s with `aria-pressed`, labelled by their `.proj-flabel` via
  `aria-labelledby`.
- `.proj-fcount` pushes right: `<b>3</b> shown · 13 total`, `aria-live="polite"`. Czech plurals
  through `data-one` / `data-few` / `data-many`, as `exp-count` already does.
- `.proj-fclear` resets both groups; hidden while nothing is active.
- **The year range filter is gone** — the group heads are the year index.
- **Filtering hides whole groups.** A year with no matching rows hides its head with its rows;
  the head's count updates to the number of *visible* rows in that group.

### URL state

Active filters are mirrored into the query string with `history.replaceState`:
`?kind=client&stack=laravel,php`. Read on load, applied before first paint of the list where
possible. `kind=all` and an empty stack list drop out of the URL entirely.

### Empty state

When every row is hidden, the list is replaced by `.proj-empty`: an `h3`, a sentence naming the
active filters back to the visitor, and exactly one action — `Clear filters`. The count reads
`0 shown · 13 total`. Filters may empty the list; they never silently drop themselves.

## Expanded panel

- `Details` toggles `.is-open` on the row. `.proj-more` goes `display:none` → `grid`. Instant is
  acceptable; do not spring it.
- Label flips `Details` ⇄ `Close`; `aria-expanded` follows; `aria-controls` points at the panel.
- Multiple rows may be open at once. Opening one never closes another. Opening does not move
  focus.
- Panel holds `.proj-facts` — a `dl` of 2–3 pairs: **Role**, **Client** (or **Kind** when there
  is no client), **Stack** — and `.proj-links`, one pill per link plus the dashed
  `.proj-link--none` when a project has none.
- The whole row is **not** a link. Row hover moves one thing: the shot border goes
  `--c-primary`.

## Content rules

- **One ghost wordmark per page.** It lives in the dock hero and says `Projects`. A row never
  gets a ghost — that was the old year treatment and it is what this replaces.
- **Description is one paragraph**, first person, about the decision or the role — not a feature
  list.
- **Status is honest.** `Archived` on old work beats pretending everything is live.
- **Every row shows a link row**, even when the link row says there is no public repo.

## i18n

`resources/lang/{en,cs}/pages/projects.php` gains: filter group labels (Kind, Stack), the four
kind labels, the three status labels, the count strings (`one` / `few` / `many` for Czech —
`1 projekt`, `2–4 projekty`, `5+ projektů`), the year-head count, `Clear filters`, the empty
state copy, `Details` / `Close`, the three fact labels, the three link labels, and
`No public repo`. Row content (name, description, role) stays in the database as `{"en","cs"}`
JSON.

## Admin

- `⚡projects.blade.php` — kind select, client text input (shown when kind is `client`), status
  select, role inputs for en and cs. Existing validation style and Flux components.
- `⚡links.blade.php` — kind select.

## Responsive

- **≤1100px** — grid `5rem 13rem 1fr`, shot `9rem`, year head `2.1rem`, toggle drops under the
  body in column 3, `.proj-desc` max-width released.
- **≤760px** — single column; rail becomes a horizontal `kind · status` line; shot full width at
  `12rem`; row padding `1.6rem 0`; year head `1.9rem`, sticky at `top:0`. The filter bar keeps
  both groups and wraps, the count taking its own line. `.proj-seg` scrolls horizontally before
  it wraps — a segmented control must never break onto two lines.

## Accessibility

- `h1` (dock hero title) → `h2` (year) → `h3` (project name). The outline reads
  `Projects → 2026 → Portfolio` instead of `2026` alone.
- The year is a real `h2`: readable, selectable, not `aria-hidden`. The only `aria-hidden`
  things on the page are the dock hero's ghost wordmark and the seg thumb.
- Focus order follows the DOM: rail → shot → name → toggle.
- The status dot is decorative; the word `Live` carries the meaning.
- `prefers-reduced-motion` is respected by the thumb transition.

## Testing

`tests/Feature/ProjectsPageTest.php`
- Years descend; `sort_order` orders inside a year.
- One year head per distinct year; its count matches that year's rows.
- Heading hierarchy: one `h1`, an `h2` per year, an `h3` per project.
- Kind, client, status, role render; a null status renders no status line.
- A project with no links renders the dashed no-link pill.
- Czech locale renders Czech kind/status/role/description.
- No projects at all → empty state.

`tests/Browser/ProjectsPageTest.php`
- Kind seg switches, thumb moves, count updates.
- Two stack chips union rather than intersect.
- A year whose rows are all filtered out hides its head.
- `Details` expands, `aria-expanded` flips, a second row opens without closing the first.
- No horizontal scroll at 1440 / 1100 / 760 / 390.

Translatable JSON assertions use `toEqual`, never `toBe` — `badges.name` is `jsonb` and Postgres
reorders its keys.

## Task order

1. Migration, models, admin CRUD, feature tests for the new fields.
2. Purge the test rows; backfill the three real projects and their links (seeder).
3. Row and year-head components, `project-list.css`, controller ordering — static list, no
   filters yet. Old zigzag deleted.
4. Expanded panel: facts, links, toggle.
5. Filter bar: seg, chips, count, clear, URL sync, group hiding, empty state.
6. Responsive pass, browser tests, light theme check.

## Out of scope

- The `.sechead` page head (the dock hero owns the page's head and its only ghost).
- Lazy loading, skeleton rows, the sentinel observer, the polite live region for arrivals, and
  any server-side filter endpoint.
- Handoff open decisions 2–4: whether Stack filters on curated chips or a repo's full tech list;
  where hackathon write-up links point; folding thin years into a combined `2022 · 2019`
  divider. Revisit when the list passes ~20 projects.
