# Hero Typography Refresh

**Date:** 2026-04-02  
**Status:** Approved  
**Scope:** `resources/css/app.css` only — no Blade changes required

---

## Goal

Make the hero section's first section more visually appealing by tightening the vertical rhythm between text elements and introducing stronger typographic contrast between the suptitle, h1, and subtitle.

---

## Changes

### 1. Suptitle — `#hero-page-text h3`

Override the global `h3` styles with a small muted label style:

- `font-size: 0.85rem`
- `font-weight: 400`
- `color: #a3a3a3`
- `text-transform: uppercase`
- `letter-spacing: 0.08em`
- `margin-bottom: 0.2rem`

### 2. H1 — `.portfolio-page h1`

Tighten the heading and increase weight:

- `font-weight: 700` (up from 600/semibold)
- `line-height: 0.95`
- `letter-spacing: -0.02em`
- `margin-bottom: 0` (remove gap before subtitle)

The `h1 span` (projektant-pata outlined text) is **unchanged** — `font-family: sans-serif` and `-webkit-text-stroke` stay as-is.

### 3. Subtitle — `#underh1`

Replace large subtitle with a smaller, lighter style:

- `font-size: 1.3rem` (down from `clamp(2rem, 3vw, 3.5rem)`)
- `font-weight: 300`
- `color: #a3a3a3`
- `letter-spacing: 0.04em`
- `line-height: 1.1`

### 4. Subtitle accent — `#underh1 span`

Explicit rule for the "Full-stack" accent span inside `#underh1`:

- `color: var(--c-primary)` (#FACC15)
- `font-weight: 500`

---

## Files Affected

- `resources/css/app.css` — sections 3 (Base Styles) and 6 (Home Page)

---

## Out of Scope

- No other sections touched
- No font changes (Inter stays, projektant-pata span stays sans-serif)
- No Blade template changes
- No site-wide typography changes
