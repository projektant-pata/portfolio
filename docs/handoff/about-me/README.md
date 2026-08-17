# Handoff: About me page

## Overview
A full recomposition of the **body** of `/about-me`. The hero (phone dock + outlined `About me` wordmark) and the footer are **unchanged** — they are in `about.html` only so the new sections can be judged in context.

What was wrong with the current page: five identically-styled text cards (a wall of prose with no hierarchy), a `My Stats` block of eleven equal cards arranged in a jagged pyramid where serious metrics and jokes carry the same weight, and no imagery anywhere between hero and footer.

What replaces it — eight sections with deliberately different silhouettes so the page has rhythm instead of repetition:

| # | Section | Silhouette | Replaces / new |
|---|---------|-----------|----------------|
| 1 | Intro (`.ab-intro`) | bordered composition, copy + portrait + fact strip | merges old *About me* + *What drives me* |
| 2 | Toolbox (`.ab-stack`) | one frame, four divided columns | **new** — biggest gap on the old page |
| 3 | The short version (`.ab-path`) | borderless left-rail timeline | replaces the *How did we get here?* prose card |
| 4 | Stats (`.ab-bento`) | bento: 2 hero tiles + 8 small | replaces the pyramid grid |
| 5 | Off the screen (`.ab-beyond`) | 3 photo cells in one frame | replaces the *What do I like?* card |
| 6 | Things I show up for (`.ab-showups`) | flat outlined tile strip | absorbs *Volunteering?* |
| 7 | References (`.tcar`) | existing testimonials carousel | **new**, reuses `components.css` |
| 8 | Contact (`.ab-cta`) | filled gold-tinted band | **new** |

Section 6 is deliberately **not** a trophy shelf — it is evidence the weeks are full, and it links out to the Experience page for the actual record.

## About the Design Files
These are **design references written in HTML** — intended look and behaviour, not production code to lift verbatim. Rebuild in the portfolio's own environment with its component patterns. All values below resolve from the existing tokens; nothing new was invented except the availability green (`#4ADE80`), which is the only colour outside the token set.

## Fidelity
**High-fidelity.** Final type, spacing, borders, radii, transitions.

## Structure

### Shared section system
- Page column: `.ab-page { display:flex; flex-direction:column; gap: var(--sp-section) }` (`6.25rem`, `4.5rem` under 760px).
- Every section opens with `.ab-sec-head`: left `.ab-eyebrow` (11px / 600 / `.18em` / uppercase / `--c-muted`, with a `26×1px --c-primary` rule via `::before`) + `h2` (Space Grotesk 700, `clamp(1.9rem, 1.2rem + 2vw, 2.6rem)`, `line-height:1`, `-.02em`; `em` = weight 500 `--c-primary`, not italic), right `.ab-sec-note` (`.95rem`, weight 200, `max-width:38ch`). Head margin-bottom `1.875rem`.
- The eyebrow + rule is the system's section marker — same one the Experience hero uses. No emoji, no icons.
- Image placeholders (`.ab-slot`): `--c-surface-sunken` + 135° 1px/9px stripes at 26% `--c-primary-fade`, monospace 10.5px caption bottom-left naming the asset and target size.

### 1 · Intro
`grid-template-columns: 1fr 360px; grid-template-rows: 1fr auto`, `--r-card` radius, `--border-w solid --c-primary-fade`, `overflow:hidden`, background `--c-bg` (not surface — it reads as a frame on the page, not a card).
- Copy column: padding `3.4rem 3.2rem 3rem`, gap `1.6rem`. Lead statement Space Grotesk 700 `clamp(1.7rem, 1rem + 1.9vw, 2.35rem)`, `line-height:1.14`, `max-width:26ch`. Then two prose columns (`.95rem`, weight 200, `line-height:1.7`, `--c-muted`; `b` = `--c-fg` 600). Signature `.ab-sign` = display 1rem after a `34×1px` rule.
- Portrait: `min-height:420px`, `border-left` at 55% fade, full-bleed to the frame's edges. **Needs a real portrait** — the page currently has no photo of Richard at all, which is the single biggest credibility gap.
- Fact strip: `grid-column:1/-1`, `--c-surface`, hard `--border-w` top edge, four `flex:1` cells divided by 45%-fade left borders, label 10.5px uppercase / value `.95rem` 600. The last value carries `.ab-dot` — a 7px `#4ADE80` dot with a pulsing ring (`ab-pulse`, 2.4s, suppressed under `prefers-reduced-motion`). **Drive the availability state from one flag** — it appears here and in section 8.

### 2 · Toolbox
One `--c-surface` frame, four columns divided by 45%-fade left borders (no gaps — shared edges, like the Experience hero strip). Column head 11px / 600 / `.14em` uppercase `--c-primary`. Items (`.ab-item`): `.95rem` weight 200 with a 6px `--bc` dot and a right-aligned monospace 10px frequency (`daily` / `often` / `sometimes`). `--bc` values come from the documented badge palette in `tokens/colors.css` — reuse the same hue per technology everywhere on the site.
- Footer row (`.ab-stack-foot`): `--c-surface-sunken`, `Learning now` label + `.ab-chip` pills.
- **The frequency column, not a percentage bar, is the point** — no fake skill percentages.
- Content is plausible but unverified: confirm the actual list and frequencies before shipping.

### 3 · The short version
Deliberately borderless — after two framed sections the page needs air, and this is the one place a rail reads better than cards. `grid-template-columns: 88px 1fr; gap:2rem` per node, 1px top border between nodes, a 1px vertical rail at `88px + 1rem` clipped at the first/last node, and a 9px `--c-primary` dot with a `4px --c-bg` ring at `top:2rem`. Year = monospace `.9rem` `--c-primary`; title 1.25rem/400; body `.95rem` weight 200 `max-width:62ch`. `.ab-node--now` turns its title gold.
- **Do not reuse the Experience page's two-column centre axis here** — that composition is that page's signature; this single rail keeps the pages distinguishable.

### 4 · Stats
`.ab-ghost` outlined `My stats` wordmark sits above the head (`clamp(64px, 9vw, 118px)`, 1px stroke at 80% fade, `aria-hidden`) — the same device the hero uses, used exactly once mid-page. `line-height:1.12` + `padding-bottom:.1em` are load-bearing: at `line-height:1` the `y` descender clips and it reads "Mu stats" (the bug visible on the live page today).
- Bento: `repeat(4, 1fr)`, gap `.75rem`, `--r-card-sm`. Two `.ab-tile--hero` tiles span 2 columns, `min-height:186px`, `--c-surface-sunken` + `--c-primary-lt` border, number `clamp(3rem, 2rem + 2.6vw, 4.4rem)`, plus one supporting line. Small tiles `min-height:118px`, number `2.1rem`, label `.85rem` `--c-muted`, hover border → `--c-primary`.
- Jokes stay in the mix (`∞ Coffee`, `404 Hours slept, not found`) but as `.ab-tile--soft`: transparent background, number in `--c-fg` at 55% opacity. They read as an aside without being fenced off in their own box.
- **Derive the derivable ones**: repos from the GitHub API, projects from the project collection, hackathons from the experience entries tagged `competition`. `1684` and `19` are manual.

### 5 · Off the screen
Three cells in one bordered frame, divided by 45%-fade borders. Each: `.ab-slot` photo plate at `230px` → title (1.15rem/400, `span` gold) → 1–2 short paragraphs → `.ab-beyond-meta` (monospace 10.5px uppercase gold) pinned to the bottom with `margin-top:auto` so the metas align across cells regardless of copy length.
- Needs three real photos. Placeholders name them: chess, music, homelab/catfish.

### 6 · Things I show up for
`repeat(auto-fit, minmax(220px, 1fr))`, gap `.75rem`. Tiles are **transparent with a 70%-fade 1px border** — visually lighter than every card above so this reads as a list of facts, not a set of achievements. Hover fills with `--c-surface`. Kind label 10.5px uppercase in `--bc` (badge palette) + monospace year on one row, then title 1.05rem/400, then one line of `.875rem` weight 200.

### 7 · References
Reuses `.tcar*` from `components.css` verbatim — only the head copy is new (the eyebrow is `.ab-eyebrow` for consistency with the other sections). Three placeholder quotes; **replace with real ones or drop the section** — invented testimonials are worse than none.

### 8 · Contact
`grid-template-columns: 1fr auto`, padding `2.8rem 3rem`, `--c-primary-lt` border, background `linear-gradient(120deg, color-mix(in srgb, var(--c-primary) 9%, var(--c-surface)), var(--c-surface) 62%)` — the only tinted surface on the page, so it lands as the page's end punctuation.
- Left: availability line (11px / 600 / `.14em` uppercase + `.ab-dot`), `h2` `clamp(1.8rem, 1.1rem + 2vw, 2.5rem)`, sub `.95rem` weight 200 `max-width:44ch`.
- Right: `.ab-btn--gold` mailto `richard.hyvl@gmail.com`, `.ab-btn--ghost` Download CV, second row GitHub / LinkedIn, then `.ab-cta-fine` monospace 10.5px `Replies within 24 hours · CET`.
- Buttons: `--r-pill`, `.75rem 1.5rem`, `.95rem` 600; gold hover → `--c-primary-dk` + white text + gold glow; ghost hover → gold border and text; both `translateY(1px)` on active. These match `.btn-gold` / `.btn-gold-subtle` semantics — **promote to the existing `Button` component** rather than shipping `.ab-btn` if the codebase already renders it.
- **Wire Download CV to a real file** or remove it.

## Interactions & Behavior
- Static except the testimonials carousel (native scroll-snap + prev/next + dots, ~8 lines of JS in `about.html`) and the theme toggle.
- Hovers: `--t-fast` (`.3s ease-out`) on borders/colours only.
- Availability dot pulse is the only ambient motion; it stops under `prefers-reduced-motion`.
- Accessibility: `h1` stays the hero wordmark; sections use `h2`, cards `h3`. The stats ghost wordmark is `aria-hidden`. Carousel buttons carry `aria-label`. Contrast on `--c-muted` passes AA at these sizes in both themes; keep weight 200 at `.875rem` or larger.
- Each section carries `data-screen-label` for review tooling only — drop it in production.

## Responsive
- **≤1100px** — intro goes single-column with the portrait moved above the copy (`order:-1`, `min-height:300px`); toolbox → 2 columns; bento → 3 columns with hero tiles spanning all 3; off-screen → 1 column (borders flip to top); contact stacks and its actions left-align.
- **≤760px** — section gap `4.5rem`; intro prose single column; fact strip wraps to 2×2; toolbox → 1 column; bento → 2 columns; timeline rail moves to a `64px` year column; contact padding `2rem 1.5rem`.
- The `360px` portrait column and `88px` year column are fixed on purpose (crop quality; year alignment).

## Design Tokens
No new tokens. Everything resolves from `tokens/colors.css`, `typography.css`, `layout.css` — dark default / `html.light` overrides, `--border-w` `1px` / `2px`. Sole literal: availability `#4ADE80`; badge `--bc` hues come from the palette documented in `tokens/colors.css`.

Type: Inter 200 / 400 / 500 / 600 / 700, Space Grotesk 500 / 700.

## Assets
- `assets/phone.png` — hero dock mockup, copied from the Experience hero bundle (same placeholder, same replacement note).
- **Four images to supply:** portrait (`900×1200`), chess, music, homelab/catfish (`1200×900` each). Every slot is labelled in-page.
- No icons or vectors; the footer's glyph tiles are the existing footer's.

## Files
- `about-standalone.html` — **open this one.** Same page, self-contained: links `./_deps/styles.css`. Works straight out of the zip.
- `about.html` — identical, but links `../styles.css` (its place inside the design-system project).
- `about.css` — all `.ab-*` rules. Links after `styles.css`.
- `assets/phone.png`.
- `_deps/` — copies of the design system's `styles.css`, `components.css` and `tokens/*` so the reference renders offline. **Do not port these** — the target project already has them; they are here only so the page opens. Fonts load from Google Fonts (needs a network on first open).

## Open decisions
1. Real testimonials, or cut section 7.
2. Verified toolbox list + frequencies.
3. CV file for the download button.
4. Four photographs.
