# Page Heroes for the Public Subpages — Design

**Date:** 2026-08-12
**Status:** implemented — see `docs/superpowers/plans/2026-08-12-page-heroes.md`

## Problem

The home page opens with a hero (eyebrow, big `h1`, typewriter rotator, photo, full viewport
height). The three subpages — About me, Experience, Projects — drop the visitor straight into
content cards. Their only title is the decorative outlined watermark `h2`, which is deliberately
pulled under the first card, so each page starts mid-thought.

Verified in the current tree: `about-me.blade.php`, `experience.blade.php` and
`projects.blade.php` contain **no `<h1>` at all**. Only `welcome.blade.php` (public) and
`components/manage/page-header.blade.php` (behind auth) render one. So the subpages are missing
their primary heading entirely — this is a real SEO and accessibility defect, not a cosmetic one.

## Goal

Every one of the four main public pages opens with a hero built from the same component. On the
three subpages the hero stops short of the fold so the next section — its watermark `h2` plus a
sliver of content — peeks in and invites the scroll. The home hero keeps its full-viewport
drama. All hero copy is editable in the admin, in both locales.

## Non-goals

- DB-derived meta chips (entry counts, year spans, tech chips) — deferred.
- Page-specific hero artwork — the home portrait is reused everywhere until real assets exist.
- The rest of `docs/design-upgrade-ideas.md` (§2–§8).
- Repairing `docs/superpowers/plans/2026-08-11-design-upgrade.md`, whose Phase A and B steps are
  checked off but were never implemented (no `page-hero` files exist; the subpages still carry
  the inline `padding-top: var(--sp-section)` those tasks claim to have removed). That plan is
  superseded by this spec for the page-opener work.

## Architecture

### Component

`resources/views/components/portfolio/page-hero.blade.php` — one shared Blade component, adopted
by all four pages.

| Prop | Type | Required | Notes |
|---|---|---|---|
| `title` | string (HTML) | yes | Rendered inside `<h1>`; `<span>` marks the gold accent |
| `eyebrow` | string | no | Rendered as `.hero-suptitle`; omitted when empty |
| `roles` | array\<string\> | no | Rotator lines; block omitted when fewer than 2 |
| `image` | string | no | Asset path; the image `<article>` is omitted when empty |
| `imageAlt` | string | no | Defaults to an empty alt (decorative) |
| `full` | bool | no | `true` = full-viewport variant (home only) |

Markup mirrors the existing home hero exactly, so the rotator JS and the staggered entrance
animation keep working with no changes:

```blade
<section class="hero-page {{ $full ? 'hero-page--full' : '' }} portfolio-section">
    <article class="hero-page-text">
        <p class="hero-suptitle">{{ $eyebrow }}</p>
        <h1>{!! $title !!}</h1>
        <h4 class="underh1">
            <span id="hero-rotator" data-roles='@json($roles)' aria-live="polite">{!! $roles[0] !!}</span>
            <span class="hero-caret" aria-hidden="true"></span>
        </h4>
    </article>
    <article class="hero-page-image"><img src="…" alt="…"></article>
</section>
```

### Height and the peek

A single token in the `@theme` block of `resources/css/app.css`:

```css
--hero-peek: clamp(4rem, 11svh, 8rem);
```

```css
.hero-page        { min-height: calc(100svh - var(--hero-peek)); }
.hero-page--full  { min-height: 100svh; }
```

`svh` rather than `vh` so mobile browser chrome does not make the hero jump. The peek is
expressed in `rem`/`svh` only — no pixel constants — so it scales with the viewport and with the
root font size. The home page changes from `height: 100vh` to `min-height: 100svh`; that is the
one intentional behavioural change to the home hero.

### Styles

`resources/css/components/page-hero.css` holds all hero styling, moved out of
`resources/css/pages/index.css`. It is `@import`-ed by `index.css`, `about-me.css`,
`experience.css` and `projects.css` — the same pattern `project-row.css` already uses, so
`vite.config.js` needs no change. Class names are preserved verbatim during the move.

### Content storage

All hero copy lives in the `settings` table, same as the home hero, and is edited in
`resources/views/pages/manage/⚡site-content.blade.php`.

Nine new keys:

```
about_hero_suptitle       about_hero_title       about_hero_roles
experience_hero_suptitle  experience_hero_title  experience_hero_roles
projects_hero_suptitle    projects_hero_title    projects_hero_roles
```

`*_suptitle` and `*_title` are `{en, cs}` strings read with `Setting::text()`; `*_roles` are
`{en: [...], cs: [...]}` lists read with `Setting::list()`.

Four orphaned keys already sit in the dev database from the abandoned plan —
`about_hero_subtitle`, `experience_hero_subtitle`, `projects_hero_subtitle`, `about_hero_meta`.
No code reads them. The seeder deletes them.

### Admin

`⚡site-content` currently hardcodes one `roles` textarea and a flat `$textKeys` list. It is
generalised to a grouped structure:

- `$textKeys` grows with the six new `*_suptitle` / `*_title` keys.
- The single `$roles` property becomes `$roleLists`, keyed by setting key
  (`hero_roles`, `about_hero_roles`, `experience_hero_roles`, `projects_hero_roles`), each still
  edited as a newline-separated textarea and split by the existing `splitLines()` helper.
- Fields are grouped under headings — Home, About me, Experience, Projects — inside both locale
  tabs; fifteen ungrouped inputs per tab would be unusable.
- Validation keeps the existing shape: EN required, CS nullable and falling back to EN on save.

### Controllers

No controller changes. The pages read `Setting::text()` / `Setting::list()` inline, exactly as
`welcome.blade.php` does today.

### Images

`config/portfolio.php` gains a `hero_images` map:

```php
'hero_images' => [
    'home'       => 'images/id-photo-portrait-businessman-suit-260nw-1505360618 1.png',
    'about'      => 'images/id-photo-portrait-businessman-suit-260nw-1505360618 1.png',
    'experience' => 'images/id-photo-portrait-businessman-suit-260nw-1505360618 1.png',
    'projects'   => 'images/id-photo-portrait-businessman-suit-260nw-1505360618 1.png',
],
```

All four point at the existing home portrait for now. Swapping in real artwork later is a
one-line change per page with no template edits.

## Copy

`<span>` marks the gold accent. Rotator lines are one per line in the admin textarea.

### About me

| Key | EN | CS |
|---|---|---|
| `about_hero_suptitle` | `👤 whoami` | `👤 whoami` |
| `about_hero_title` | `A bit <span>about me</span>,` | `Něco <span>o mně</span>,` |
| `about_hero_roles` | `Student <span>by day</span>` / `Freelancer <span>by night</span>` / `<span>Chess</span> player` / `Arch Linux <span>enjoyer</span>` / `Coffee → <span>code</span>` | `Ve dne <span>student</span>` / `V noci <span>freelancer</span>` / `<span>Šachista</span>` / `Arch Linux <span>nadšenec</span>` / `Káva → <span>kód</span>` |

### Experience

| Key | EN | CS |
|---|---|---|
| `experience_hero_suptitle` | `🗓️ Where I've been` | `🗓️ Kudy jsem prošel` |
| `experience_hero_title` | `My <span>journey</span>,` | `Moje <span>cesta</span>,` |
| `experience_hero_roles` | `Certificates & <span>competitions</span>` / `Work that <span>shipped</span>` / `Life <span>outside code</span>` / `From <span>2021</span> to now` | `Certifikáty a <span>soutěže</span>` / `Práce, co <span>vyšla</span>` / `Život <span>mimo kód</span>` / `Od <span>2021</span> dodnes` |

### Projects

| Key | EN | CS |
|---|---|---|
| `projects_hero_suptitle` | `🛠️ What I've built` | `🛠️ Co jsem postavil` |
| `projects_hero_title` | `Things I've <span>shipped</span>,` | `Věci, co jsem <span>postavil</span>,` |
| `projects_hero_roles` | `Laravel <span>monoliths</span>` / `Spring Boot <span>APIs</span>` / `Side projects that <span>survived</span>` / `Deployed with <span>Docker</span>` | `Laravel <span>monolity</span>` / `Spring Boot <span>API</span>` / `Vedlejšáky, co <span>přežily</span>` / `Nasazeno přes <span>Docker</span>` |

No copy hardcodes a count or a derived fact. `2021` is a fixed historical start date, not a
derived value.

## Heading hierarchy and SEO

- Exactly one `<h1>` per public page. Home already has one; the three subpages gain theirs.
- The `<span>` inside an `h1` only carries colour — the heading text stays one continuous string,
  so a crawler reads the full sentence.
- The watermark headings stay `h2`, giving a clean `h1 → h2 → h3` order on every page.
- `<title>` and meta description in `portfolio-layout` are untouched.

## Accepted trade-off

On About me, the `h1` ("A bit about me") and the watermark `h2` immediately below it
(`about_title`, "O mně" / "About me") say much the same thing, and both are visible in the peek.
This is accepted for now — `about_title` is editable in the admin, so it can be renamed later
without a code change. Experience and Projects do not have this overlap.

## Testing

Feature tests (`tests/Feature/PageHeroTest.php`, new):

- each subpage returns 200 and contains exactly one `<h1>`
- the eyebrow, title and rotator lines from `settings` appear in the HTML
- `data-roles` carries the role list
- the CS locale renders the CS values

Browser tests (`tests/Browser/PageHeroTest.php`, new) at 1440×900 and 390×844:

- on each subpage the hero bottom is above the fold and the following section intersects the
  viewport — peek greater than 0 and under roughly 200px
- the home hero fills the viewport (no peek)
- the rotator text changes within a couple of seconds

Existing suites to re-run: `HomePageTest`, `AboutMePageTest`, `ExperiencePageTest`,
`SiteContentManagementTest`, `PublicPagesTest`.

`SiteContentManagementTest` is extended to cover saving the nine new keys and the three new role
lists.

## Risks

1. **Moving the hero CSS out of `index.css` can regress the home page.** Mitigation: write the
   home-hero geometry browser test *before* the move, so the move is verified, not assumed.
2. **`100vh` → `100svh` on home** slightly shortens the mobile hero. Intentional and consistent;
   called out here so it is not mistaken for a bug.
3. **The admin form doubles in size.** Mitigation: grouped headings per page inside each locale
   tab.
4. **The rotator JS binds to `#hero-rotator` by id.** One hero per page keeps that valid; the
   feature test asserts a single rotator element per page.

## Build and verification notes

- Artisan, Pint and tests run in the container: `docker exec portfolio-app-1 …`.
- Vite builds run on the host: `npm run build`, required after any CSS change and before browser
  tests.
- Translatable JSON columns are asserted with `toEqual`, never `toBe`.
