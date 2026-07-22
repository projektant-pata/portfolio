# Frontend fix: heading font + responsiveness + mobile phone-nav

**Date:** 2026-07-20
**Files:** `resources/css/app.css` (only), plus doc updates in `docs/typography.md`
**Scope:** public portfolio pages (`.portfolio-page`) — no Blade or JS changes.

This note documents three linked frontend fixes and, more importantly, the
**gotchas** behind them so future changes don't reintroduce the same bugs.

---

## What was wrong (reported)

1. **Heading font ("krásné nápisy").** The outlined "watermark" heading look is
   *wanted* and stays — but it was drawn in the generic `sans-serif` system font,
   which looked broken and overlapped/swallowed content. Fix = keep the outline,
   use a proper **display font** (Space Grotesk) + a faint interior fill (so the
   letters aren't just hollow frames), and make the overlap scale with the font.
2. **Not responsive.** On phones the page scrolled horizontally.
3. **Mobile nav never opened.** On mobile there was no visible ☰ button and the
   smartphone-style nav could not be opened ("nevidím otevření mobilu na mobilu").
4. **Watermark labels vanished / sat too low on mobile.** The fixed `-50px`
   overlap swallowed the whole label under the section card once the font went
   fluid.

> **History note for future edits:** a first pass replaced the outline with a
> solid fill — that was **wrong**, the outline is a deliberate design choice. The
> current state keeps the outline. Don't "flatten" these headings to a solid color.

---

## Root causes (verified with Playwright)

### 1. Heading font
`.portfolio-page h1 span` and `.portfolio-page h2` forced `font-family: sans-serif`
(a generic system font) together with `color: transparent` + `-webkit-text-stroke`.
The outline itself is fine/wanted; the **generic font** is what looked broken, and
purely `transparent` fill made the letters read as empty frames. Note: **light mode
already filled `h2` solid** (`html:not(.dark) .portfolio-page h2`); that override is
kept because the stroke is too faint on the light parchment background.

### 2. Horizontal overflow
`--fs-h1` (4.1rem / 66px) and `--fs-h2` (6.56rem / 105px) were **fixed**. The long
word `projektant-pata` at 66px has a min-content width of ~496px. The content
column is a CSS grid `1fr` track whose `min-width` defaults to `auto` (= min-content),
so the whole column — and the page — expanded to ~496px and overflowed any
viewport narrower than that. Measured: viewport 390px → `scrollWidth` 516px.

### 3. Mobile nav couldn't open  ← **the important gotcha**
The toggle button, overlay, and phone frame all live inside
`<aside class="portfolio-sidebar">` (see `components/portfolio-layout.blade.php`
→ `<x-mobile-nav/>`). At ≤992px the CSS did:
```css
.portfolio-sidebar { display: none; }   /* ← kills everything inside */
```
The toggle/nav were `position: fixed` and *expected* to escape, but **`position:
fixed` does NOT escape an ancestor with `display: none`** — a `display:none`
subtree generates no boxes at all. So `#toggle-mobile-nav` and `#mobile-nav` had
`offsetParent: null` and a `0×0` rect: invisible, unclickable.

---

## The fixes (all in `resources/css/app.css`)

1. **Outlined headings in a real display font.** New `@theme` token
   `--font-display: 'Space Grotesk', …`, loaded via Google Fonts in
   `components/portfolio-layout.blade.php` **and** `partials/head.blade.php`.
   - `.portfolio-page h1 span` → `font-family: var(--font-display)`,
     `-webkit-text-stroke: 2px var(--c-primary)`, and a faint fill
     `color: color-mix(in srgb, var(--c-primary) 16%, transparent)`.
   - `.portfolio-page h2` → same idea with `--c-primary-lt`, 1px stroke, 12% fill.
   - **To change the heading font**, edit the one `--font-display` token (and the
     `family=` param in the two Blade `<link>`s if the new font needs loading).

2. **Fluid heading sizes + scaled overlap.** `--fs-h4/h3/h1/h2` are now
   `clamp(min, base+vw, max)` with `max` = the old desktop size.
   `overflow-wrap: anywhere` on `h1`/`h2`; `min-width: 0` on `.portfolio-col`.
   `h2` overlap changed from `margin-bottom: -50px` → **`-0.45em`** so it scales
   with the fluid size (a fixed -50px buried the whole label under the card on
   mobile — the "labels vanished / too far down" bug).

3. **Mobile nav.** `.portfolio-sidebar` at ≤992px is now `display: contents`
   instead of `display: none`. `display: contents` removes the aside's own box
   from layout but **keeps its children rendering**, so the fixed toggle/overlay/nav
   work. (Their `position: fixed` already keeps them out of the grid flow.)

---

## Gotchas for future edits

- **Never `display: none` an ancestor of something you positioned `fixed`/`absolute`
  to "pull out".** Use `display: contents` (unwrap) or move the element in the DOM.
- **All portfolio heading styles are global and scoped to `.portfolio-page`**
  (on `<body>`). Changing `h1/h2/h3/h4` in `app.css` affects every public page.
- **Two themes.** Dark is default (`.dark` on `<html>`); light is
  `html:not(.dark)`. `h2` has a **separate light-mode color override** — change
  both if you retune the watermark.
- **Assets are pre-built, not dev-served.** The portfolio container serves
  `public/build/*`. After editing any CSS/JS you **must** run `npm run build`
  (`vite build`) or the change won't show. There is no vite dev server running
  for this app.
- Heading sizes are tokens (`--fs-*`) in `app.css` section 1 — edit the token,
  not individual rules.

---

## How to verify (Playwright)

App runs at **http://localhost:8008** (docker: `portfolio-nginx-1`). Checklist:

- Desktop 1440px: hero `h1` accent word is solid gold; watermark labels
  (`My Stats`, `Work & Life`, …) are solid, subtle.
- Mobile 390px, all of `/ , /about-me , /experience , /projects`:
  - `document.documentElement.scrollWidth === clientWidth` (no h-overflow).
  - `#toggle-mobile-nav` has a non-null bounding box (visible ☰, top-right).
  - Clicking it adds `.active` to `#mobile-nav` and the phone slides on-screen
    (`getBoundingClientRect().width > 0`, x within viewport).
- Toggle the `theme` cookie to `light` and confirm headings stay readable.
