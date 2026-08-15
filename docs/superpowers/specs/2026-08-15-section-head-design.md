# Section head (`sechead`) — design

**Date:** 2026-08-15
**Source:** `handoffballs.zip` → `design_handoff_section_start/` (README, `section-start.html`, `section-start.css`)
**Scope:** the component plus its rollout to the **home page only**. `about-me`,
`experience` and `projects` keep their current headings; the component is built
so those pages need one `@import` and a markup swap when their turn comes.

## Problem

Every section on the home page is introduced by a single centred outlined
wordmark and nothing else — `My Stats`, `Work & Life`, `Projects`, `Tools`,
`Reviews`. That word is the `h2` itself, styled by the global rule at
`resources/css/app.css:348`:

```css
.portfolio-page h2 {
    text-align: center;
    margin-bottom: -0.45em;
    color: color-mix(in srgb, var(--c-watermark) 12%, transparent);
    -webkit-text-stroke: 1px var(--c-watermark);
}
```

Three problems fall out of that:

1. **No context.** The outlined word carries all the meaning. No category, no
   sentence, no room for a caveat — five sections read as five unrelated blocks.
2. **Clipped descenders.** The rule inherits a tight line box, so `My Stats`
   renders as `Mu Stats` and `Projects` loses the tail of its `j`.
3. **Accessibility.** A screen reader hears the decorative word as the section
   heading — `Mu Stats`, `Tools` — with no context around it.

## Solution

One reusable component: the outlined word stays (it is the site's signature) but
becomes decorative, and a real head goes in front of it.

```
        ┌ ghost wordmark (outlined, decorative, aria-hidden)
        │
▬▬ EYEBROW                         ← 26×1px gold rule + 11px uppercase category
Title with one gold word           ← the real h2
                                     right column: optional note / link
```

| # | Part | Class | Required |
|---|------|-------|----------|
| 1 | Ghost wordmark | `.sechead-ghost` | no — 2–3 per page |
| 2 | Eyebrow + gold rule | `.sechead-eyebrow` | yes |
| 3 | Title | `.sechead-row h2` | yes |
| 4 | Note / comment | `.sechead-note` | no |

Left-aligned, per the handoff recommendation and matching `dock-hero` and the
Experience page. The `--center` variant ships in the CSS but the home page does
not use it.

## Component

`resources/views/components/portfolio/section-head.blade.php`, following the
`x-portfolio.dock-hero` conventions (flat `@props`, no class-based component):

```blade
@props([
    'ghost' => '',
    'eyebrow',
    'title',
    'note' => '',
    'variant' => 'default',   // default | noghost | behind | center
])
```

- `ghost` renders only when passed, always escaped and
  `aria-hidden="true"` — it is a printed mark, never content.
- `title` and `note` render through `{!! !!}` so the gold `<em>` and the note's
  link live in the copy, not in code. Both come from our own lang files; no user
  input reaches them.
- `variant` maps to a `.sechead--*` class. Omitting `ghost` is what makes a head
  ghost-less, so `noghost` is effectively the default when `ghost` is absent —
  the variant name stays available for the explicit case.
- Everything else is CSS. No per-section rules.

## CSS

`resources/css/components/section-head.css` — the handoff's `section-start.css`
ported as-is, `.sechead-*` prefix kept, no new tokens and no new colours.
Loaded with `@import '../components/section-head.css';` at the top of
`resources/css/pages/index.css`, matching how `dock-hero.css` and
`project-row.css` are already wired. Each future page adds the same one line.

Load-bearing values carried over verbatim:

- `.sechead-ghost` — `line-height:1.12` **plus** `padding-bottom:.1em`. This is
  the fix for the clipped descenders; at `line-height:1` the bug returns.
- `.sechead-row` — `align-items:flex-end`, so the note's last line sits on the
  title's baseline block.
- `.sechead-eyebrow::before` — the 26×1px `--c-primary` bar, the system's
  section marker already used by the Experience hero.
- `h2 em` — weight 500 `--c-primary`, **not italic**. One gold phrase per title.

### Overriding the global `h2`

The global watermark rule stays in place — `about-me`, `experience` and
`projects` still depend on it. `section-head.css` overrides it for heads only:

```css
.portfolio-page .sechead-row h2 {
    text-align: left;
    margin-bottom: 0;
    color: var(--c-fg);
    -webkit-text-stroke: 0;
    /* + the handoff's own type values */
}
```

When the component reaches every public page, the global rule gets deleted and
this override collapses into the plain `.sechead-row h2` block. Noted here so
the next person knows the override is deliberate and temporary.

### Horizontal overflow

The ghost is `white-space:nowrap` and sits at `top:-4.6rem`, i.e. **above** its
section box. So `overflow-x:clip` must not go on `.portfolio-section` — that
would clip the ghost itself. It goes one level up, on `.portfolio-col`.
Implementation must verify this does not clip the full-bleed `dock-hero` photo;
if it does, the fallback is a dedicated `.sechead-page` wrapper inside `main`.

The existing spacing system (`.portfolio-section { margin-bottom: var(--sp-section) }`)
stays — the handoff's `.sechead-page` flex-gap wrapper is not adopted, only its
`overflow-x:clip`.

## Copy

Lang files, not Settings. Each home section already has its own file, so the head
copy lands next to the copy it introduces:

`resources/lang/{en,cs}/home/{stats,experience,projects,tools,reviews}.php`,
keys `head_ghost`, `head_eyebrow`, `head_title`, `head_note`.

The existing `title` keys stay — `experience.title` and `projects.title` are read
by the Experience and Projects pages. The Settings keys `stats_title`,
`tools_title`, `reviews_title` also stay (about-me still reads `stats_title`);
the home page simply stops reading them. They remain editable in
`⚡site-content` until the rollout finishes.

Links inside a note use a `:url` placeholder resolved at render:

```php
'head_note' => 'Two of eighteen repositories. <a href=":url">All projects →</a>',
```
```blade
:note="__('home/projects.head_note', ['url' => route('projects')])"
```

### Drafts — sign off before implementation

| Section | Ghost | Eyebrow | Title | Note |
|---|---|---|---|---|
| Stats | `My stats` / `Statistiky` | By the numbers / V číslech | Some of it is *serious* / Něco z toho je *vážně* | Two numbers I'd defend in an interview, and one I wouldn't. / Dvě čísla bych obhájil u pohovoru, jedno ne. |
| Work & Life | — | Track record / Dosavadní dráha | Where I've *been* since 2021 / Kde jsem *byl* od roku 2021 | Toggle the two lists. Full record on the *Experience page*. / Přepni mezi seznamy. Celý přehled na *stránce Zkušenosti*. |
| Projects | `Projects` / `Projekty` | Selected work / Vybraná práce | Things I *shipped*, not things I started / Věci, které jsem *dokončil*, ne ty, co jsem začal | Two of eighteen repositories. *All projects →* / Dva z osmnácti repozitářů. *Všechny projekty →* |
| Tools | — | Daily drivers / Denní výbava | What I actually *open* every day / Co si *otevřu* každý den | Ordered by how often, not by how impressive. / Seřazeno podle četnosti, ne podle působivosti. |
| Reviews | `Reviews` / `Reference` | What people say / Co říkají ostatní | Words from people who *worked* with me / Slova lidí, kteří se mnou *pracovali* | — |

*Italics* above mark the gold `<em>` in titles and the link text in notes.

Content rules from the handoff, binding for future sections:

- Ghost is the section's plain label, 1–2 words, and must **not** repeat the
  title string.
- Title is a sentence with a point of view, not a label.
- Eyebrow is the category the visitor is scanning for.
- Note is one aside, two lines maximum. Leave it out if there is nothing honest
  to put there.
- Two or three ghosts per page, never on consecutive sections.

## Home page migration

`resources/views/welcome.blade.php` — each `<h2>` becomes an
`<x-portfolio.section-head>`. Ghost order (never consecutive): Stats ✓,
Work & Life ✗, Projects ✓, Tools ✗, Reviews ✓. Work & Life stays ghost-less
because the tab switcher sits directly under its head.

## Accessibility

- One `h2` per section; `h1` stays with the page hero; card titles inside bodies
  stay `h3`.
- Ghost is `aria-hidden="true"`, `pointer-events:none`, `user-select:none`.
- Eyebrow is a `p`, not a heading — making it a heading would break the outline.
- Note stays at `.95rem` weight 200 `--c-muted`; do not drop below `.875rem`.

## Responsive

- **≤1100px** — note drops to its own full-width row (`flex-basis:100%`).
- **≤760px** — ghost `top:-2.6rem`, `clamp(44px, 13vw, 72px)`; head
  `margin-bottom:1.4rem`; title `max-width` released.
- Long ghosts stay one line by design. If a Czech translation makes one too
  wide, that section drops its ghost rather than wrapping it.

## Interactions

Static. The only transition is `--t-fast` on the note link's colour and border.
The ghost never animates — no parallax, no scroll-driven reveal. Sections keep
the existing `.portfolio-section` fade-up reveal, which the head rides along
with.

## Testing

- `tests/Feature/HomePageTest.php` — new eyebrows and `h2` titles render in both
  locales; the ghost carries `aria-hidden="true"`; ghost text differs from its
  `h2` text; the projects note link resolves to the projects route.
- `tests/Browser/` — one check at 360px width that
  `document.documentElement.scrollWidth <= clientWidth`. The ghost's `nowrap`
  makes horizontal overflow a real risk, not a theoretical one.
- `npm run build` on the host, then a visual pass at 1440 / 1100 / 760 / 360 in
  both themes.

## Non-goals

- Rolling the component out to `about-me`, `experience`, `projects` — later, one
  `@import` and a markup swap each.
- Deleting the global `.portfolio-page h2` watermark rule — it goes when the
  last page migrates.
- Removing `tools_title` / `reviews_title` from Settings and the
  `⚡site-content` manage page — same trigger.
- Reviews content edits (the handoff's note about the `ChatGPT — The best AI`
  quote) — a copy decision, not part of this component.
