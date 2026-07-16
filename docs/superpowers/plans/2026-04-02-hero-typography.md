# Hero Typography Refresh Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Tighten the hero section's vertical rhythm and introduce stronger typographic contrast between the suptitle, h1, and subtitle.

**Architecture:** Pure CSS changes scoped to `resources/css/app.css`. No Blade, JS, or backend changes needed. The existing CSS custom property system (`--c-*`, `--fs-*`, `--fw-*`) is kept intact; only specific selector overrides are added or modified.

**Tech Stack:** Tailwind v4, custom CSS, Inter variable font (already loaded)

---

### Task 1: Update suptitle style in hero

**Files:**
- Modify: `resources/css/app.css` — section 6 (HOME PAGE), `#hero-page-text h3` rule

The suptitle "👋 Hello world!" currently inherits the global `h3` style (`--fs-h3`, `--fw-regular`). We override it with a small muted uppercase label.

- [ ] **Step 1: Open `resources/css/app.css` and locate the hero section**

Find this existing rule around line 630:

```css
#hero-page-text h3 { font-weight: var(--fw-light); }
```

- [ ] **Step 2: Replace the suptitle rule**

Replace that line with:

```css
#hero-page-text h3 {
    font-size: 0.85rem;
    font-weight: 400;
    color: #a3a3a3;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: 0.2rem;
}
```

- [ ] **Step 3: Build assets and visually verify**

```bash
npm run build
```

Open the site and confirm "👋 Hello world!" is now small, muted grey, uppercase.

- [ ] **Step 4: Commit**

```bash
git add resources/css/app.css
git commit -m "style: hero suptitle — small muted uppercase label"
```

---

### Task 2: Tighten the h1

**Files:**
- Modify: `resources/css/app.css` — section 3 (BASE STYLES), `.portfolio-page h1` rule

- [ ] **Step 1: Locate the h1 rule**

Find around line 217:

```css
.portfolio-page h1 {
    font-size: var(--fs-h1);
    font-weight: var(--fw-semibold);
}
```

- [ ] **Step 2: Replace with tighter style**

```css
.portfolio-page h1 {
    font-size: var(--fs-h1);
    font-weight: 700;
    line-height: 0.95;
    letter-spacing: -0.02em;
    margin-bottom: 0;
}
```

`--fw-semibold` was 600 — we go to 700 for more punch. `line-height: 0.95` pulls the text tight. `margin-bottom: 0` removes the gap before the subtitle below.

**Do NOT touch** the `h1 span` rule below it — `font-family: sans-serif` and `-webkit-text-stroke` on "projektant-pata" stay unchanged.

- [ ] **Step 3: Build and verify**

```bash
npm run build
```

Confirm h1 is bolder and the gap between h1 and "Full-stack developer" is gone.

- [ ] **Step 4: Commit**

```bash
git add resources/css/app.css
git commit -m "style: hero h1 — bolder weight, tight line-height, no bottom margin"
```

---

### Task 3: Restyle the subtitle (#underh1)

**Files:**
- Modify: `resources/css/app.css` — section 6 (HOME PAGE), `#underh1` rule

- [ ] **Step 1: Locate the existing underh1 rule**

Find around line 634:

```css
#underh1 {
    font-size: clamp(2rem, 3vw, 3.5rem);
    font-weight: 500;
}
```

- [ ] **Step 2: Replace with the lighter, smaller style**

```css
#underh1 {
    font-size: 1.3rem;
    font-weight: 300;
    color: #a3a3a3;
    letter-spacing: 0.04em;
    line-height: 1.1;
}
```

- [ ] **Step 3: Add accent rule for the span inside #underh1**

Directly after the `#underh1` rule, add:

```css
#underh1 span {
    color: var(--c-primary);
    font-weight: 500;
}
```

This makes "Full-stack" gold and medium weight while the rest stays muted.

- [ ] **Step 4: Build and verify**

```bash
npm run build
```

Confirm "Full-stack developer" is now small, light, muted — with "Full-stack" in gold.

- [ ] **Step 5: Commit**

```bash
git add resources/css/app.css
git commit -m "style: hero subtitle — light weight, muted grey, gold accent on Full-stack"
```

---

## Self-Review

**Spec coverage:**
- ✅ Suptitle from B style (small, muted, uppercase) → Task 1
- ✅ h1/underh1 tight together (margin-bottom: 0 on h1) → Task 2
- ✅ h1 bolder, tight line-height → Task 2
- ✅ projektant-pata span font unchanged → Task 2 (explicit note)
- ✅ Subtitle smaller, lighter, muted grey → Task 3
- ✅ "Full-stack" gold accent → Task 3

**Placeholder scan:** No TBDs, no vague steps, all code is explicit.

**Type consistency:** Only CSS selectors — no type mismatches possible.
