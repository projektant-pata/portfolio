# Responsive refinement of the public portfolio — design

**Date:** 2026-07-21
**Scope:** public pages only — `home`, `about-me`, `experience`, `projects`.
Admin (`dashboard/*`, `manage/*`), auth, and settings pages are **out of scope**.

## Problem

The public portfolio is already largely responsive: fluid `clamp()` headings, a
token system, and a phone-nav that becomes a slide-in panel below 992px. Mobile
(≤576), tablet (577–991), and PC (≥1440) all render well; **PC is the design
target**.

The weak tier is the **notebook range (992–1439px)**. Above 992px the layout
keeps its 2-column form — a fixed **415px (`--sidebar-w: 26rem`) phone-nav
sidebar** on the left — but the sidebar never shrinks. On a 1280px laptop the
content column is starved to ~770px, so grids authored for the ~1150px PC column
get squished.

Confirmed issues (from live headless screenshots + CSS review):

| Issue | Cause | Location |
|---|---|---|
| Reviews jump 3-col → 1-col at 1440 | no 2-col step; lone narrow column on notebooks | `resources/css/pages/index.css:262` |
| Stats stay 4-across down to 992 | 4 cards in ~770px → label wrap | `index.css:44`, `:275` |
| Tools 5-col with 11 items | orphan card; cramped on notebooks | `index.css:197` |
| Projects 500px image + text | text column squeezed to ~250px | `index.css:261` |
| Hero image floats far right | `space-between` leaves a dead gap | `index.css:7` |
| Footer label grids overlap ("Instagran X X") | narrow content width at notebook | `resources/css/app.css:443` |
| Coarse breakpoints 1440/992/576 | hard cliff at 992; nothing tuned for laptops | all |

## Decision

**Keep the signature phone-nav visible on laptops, but shrink it to fit and
relax the content grids** (chosen over "collapse to full-width earlier" and
"fully fluid rewrite"). This preserves the design's identity on every device
while de-cramping the notebook tier. Purely additive responsive rules — the PC
(≥1440) design is left byte-for-byte unchanged.

## Tier system

Documented as `--bp-*` comment markers in `app.css`. Values keep the existing
set plus formalize the notebook band:

| Tier | Range | Layout |
|---|---|---|
| **PC** | ≥ 1440px | Current design — untouched (the target) |
| **Notebook** | 992–1439px | NEW — phone sidebar scales down, grids relax |
| **Tablet** | 577–991px | Existing full-width single-column + slide-in nav |
| **Mobile** | ≤ 576px | Existing |

## Changes

### 1. Give the notebook content room — `app.css` (layout section)

The root fix, applied in the 992–1439 band:

- `--sidebar-w`: `26rem` → fluid `clamp(20rem, 24vw, 26rem)`.
- Scale `#mobile-nav` to ≈`0.85` so the 370px phone still fits its narrowed
  column (mirrors the existing `scale: 0.8` at ≤576). **Exact scale + sidebar
  floor tuned live** so the phone never overflows or clips.
- Result: content column gains ~100–130px, de-cramping stats, projects, and
  reviews before any per-grid change.

### 2. Relax the grids — `index.css` (add a 992–1439 step)

- **Reviews:** 3-col (≥1440) → **2-col** (992–1439) → 1-col (≤991).
- **Stats:** 4-col (≥1440) → **2×2** (577–1439) → 1-col (≤576). (Tablet already
  2-col — unchanged; notebook stops being 4-col.)
- **Tools:** 5-col (≥1440) → **4-col** (≤1439) → 2-col (≤576). Center the
  incomplete last row (`justify-items` / centered track) so the 11th tool isn't
  an awkward orphan.
- **Projects:** image width `500px` → fluid `clamp(300px, 40%, 500px)` with a
  text-column min-width floor so the text never collapses. Existing column-stack
  at ≤576 stays.
- **Hero:** replace `justify-content: space-between` with an explicit `gap` and a
  capped text column, removing the dead gap on notebooks. Existing column-stack
  at ≤992 stays.

### 3. Footer → full-bleed band — `portfolio-layout.blade.php` + `app.css`

- **Markup:** move `<x-portfolio-footer />` out of `.portfolio-wrapper` to be a
  direct child of `<body>`, immediately after the wrapper closes. This frees it
  from the centered, max-width-capped grid so the band spans the full viewport
  width.
- **CSS (`.portfolio-footer`):** drop `border-radius` and the side/bottom
  borders — keep **only `border-top: var(--border-w) solid var(--c-primary-lt)`**
  ("just the up border"). Full-width surface band.
- **Inner content (`.portfolio-footer-inner`):** `max-width: var(--layout-max);
  margin-inline: auto` + horizontal padding, so on ≥1700px screens the columns
  align with the rest of the page rather than stretching edge to edge. The band
  is full-width; the content stays aligned.
- Tidy the inner label grids (`.portfolio-footer-nav-links`,
  `.portfolio-footer-social-links`) so they wrap cleanly; the full-width band
  largely resolves the notebook overlap on its own — verify and adjust.

### 4. Guardrails

- No changes to token PC values, colors, or phone-nav internals — additive
  responsive rules only, so the PC (≥1440) design is provably unchanged.
- `about-me` reuses the shared `.stats-cards` / phone patterns, so its notebook
  tier improves for free; verify no page-specific regressions.

## Verification

Re-run the headless screenshot pass (Playwright + cached Chromium, the script in
`scratchpad/shoot.mjs`) across **6 widths** — 390, 768, 1024, 1280, 1366, 1700 —
on all four public pages, before and after. Confirm each fix and check for
regressions, with specific attention to:

- 1280 & 1366 (the notebook band the work targets),
- 1700 unchanged vs. the pre-change baseline (PC untouched),
- the phone-nav never clips/overflows its scaled sidebar,
- the footer spanning full viewport width with only a top border.

## Out of scope

- Admin / auth / settings pages.
- Color, typography, or content changes.
- Any change to PC (≥1440) layout beyond the footer full-bleed.
