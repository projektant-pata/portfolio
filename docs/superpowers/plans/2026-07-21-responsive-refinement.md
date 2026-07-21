# Responsive Refinement Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** De-cramp the notebook tier (992–1439px) of the public portfolio by scaling the phone-nav sidebar to free content width, adding an intermediate breakpoint that relaxes the stats/tools/reviews/projects grids, and turning the footer into a full-bleed top-border band — without altering the PC (≥1440) design above the footer.

**Architecture:** Pure CSS + one Blade markup move. All changes are additive responsive rules keyed to a new `992–1440px` media band, plus edits to the existing `index.css` responsive section and the `app.css` layout/footer rules. The footer is relocated in `portfolio-layout.blade.php` to escape the max-width-capped grid wrapper. No JS, no token PC-value changes, no color/typography changes.

**Tech Stack:** Laravel Blade, Tailwind v4 (`@theme` tokens) + hand-written CSS in `resources/css/app.css` and `resources/css/pages/index.css`. Verification via headless Chromium (Playwright-core) screenshot script.

## Global Constraints

- **Scope:** public pages only — `home`, `about-me`, `experience`, `projects`. Admin/auth/settings untouched.
- **PC (≥1440px) layout is unchanged** except the footer, which intentionally becomes full-bleed on all widths.
- **Notebook band** = `@media (min-width: 993px) and (max-width: 1440px)`. **Tablet/mobile** keep the existing `@media (max-width: 992px)` and `@media (max-width: 576px)` blocks.
- No changes to `@theme` token PC values, colors, typography, or phone-nav internal structure.
- Build on the existing uncommitted working-tree state (WIP already modifies `app.css`, `index.css`, `portfolio-layout.blade.php`) — do **not** revert it.
- The live app runs at **http://localhost:8008** (already serving). Verification screenshots target it.
- Work happens on branch `responsive-refinement` (already created; spec already committed there).
- The verify script lives at `scratchpad/shoot.mjs` (absolute: `/tmp/claude-1000/-home-projektant-pata-Projekty-portfolio-ds/5369c6d7-27c7-4883-b96a-ae7500464c96/scratchpad/shoot.mjs`). Signature: `node shoot.mjs <subdir> <pages> <widths>`, e.g. `node shoot.mjs after home,about home 1024,1280,1366`. Output: `scratchpad/audit/<subdir>/<page>_<width>.png`.

**Absolute paths used below:**
- Repo root: `/data/backups/pred_reklamaci/Projects/Mine/portfolio-2`
- `APP` = `resources/css/app.css`
- `INDEX` = `resources/css/pages/index.css`
- `LAYOUT` = `resources/views/components/portfolio-layout.blade.php`

---

### Task 1: Verification harness + baseline capture

Establishes the before/after visual comparison basis. No app changes.

**Files:**
- Use (already written): `scratchpad/shoot.mjs`

- [ ] **Step 1: Confirm the app is serving**

Run: `curl -s -o /dev/null -w "%{http_code}\n" http://localhost:8008/`
Expected: `200`. If not 200, start it: `cd /data/backups/pred_reklamaci/Projects/Mine/portfolio-2 && composer dev` (background) and re-check.

- [ ] **Step 2: Capture the baseline across all pages and all 6 widths**

Run: `node /tmp/claude-1000/-home-projektant-pata-Projekty-portfolio-ds/5369c6d7-27c7-4883-b96a-ae7500464c96/scratchpad/shoot.mjs baseline home,about,exp,proj 390,768,1024,1280,1366,1700`
Expected: 24 `ok baseline/...` lines then `done`.

- [ ] **Step 3: Sanity-check two baseline images**

Read `scratchpad/audit/baseline/home_1280.png` and `scratchpad/audit/baseline/home_1700.png`.
Expected: 1280 shows squished 4-col stats / lone-column reviews (the problem); 1700 shows the clean target design. This confirms the harness captures the real states we will compare against.

No commit (scratchpad is outside the repo).

---

### Task 2: Give the notebook content room (`app.css`)

The root fix: shrink the phone-nav sidebar in the notebook band so the content column widens. This alone de-cramps stats/projects/reviews before any grid change.

**Files:**
- Modify: `resources/css/app.css` — add a new notebook media block near the end of the LAYOUT section (after the existing `@media (max-width: 576px)` layout block around line 517–521) and a companion rule in the PHONE NAV section.

**Interfaces:**
- Produces: a widened content grid column in 993–1440px via a reduced `--sidebar-w`; the phone remains fully visible via `scale`. Later tasks assume the notebook content column is ~900px at 1280px viewport.

- [ ] **Step 1: Add the notebook layout block to `app.css`**

Immediately AFTER the existing layout media block:

```css
@media (max-width: 576px) {
    .portfolio-wrapper {
        padding-inline: 1.25rem;
    }
}
```

add:

```css
/* ── Notebook (993–1440px): shrink the phone sidebar to free content width ── */
@media (min-width: 993px) and (max-width: 1440px) {
    :root {
        --sidebar-w: clamp(20rem, 22vw, 24rem);
    }
    /* Scale the 370px phone down so it fits the narrowed column.
       transform-origin center keeps it centred in the sticky sidebar. */
    #mobile-nav {
        scale: 0.88;
    }
}
```

- [ ] **Step 2: Re-shoot the notebook widths on home + experience**

Run: `node /tmp/claude-1000/-home-projektant-pata-Projekty-portfolio-ds/5369c6d7-27c7-4883-b96a-ae7500464c96/scratchpad/shoot.mjs t2 home,exp home,exp 1024,1280,1366`
Expected: 6 `ok t2/...` lines.

- [ ] **Step 3: Verify the phone fits and content widened**

Read `scratchpad/audit/t2/home_1280.png` and `scratchpad/audit/t2/exp_1024.png`.
Expected: the phone-nav is fully visible (not clipped or overflowing the page edge), and the content column is visibly wider than the `baseline/home_1280.png`. If the phone clips or overflows, adjust `scale` (try `0.84`) and/or the `--sidebar-w` floor (try `19rem`) and re-shoot until the phone sits fully inside its column with a small margin. Record the final values.

- [ ] **Step 4: Commit**

```bash
cd /data/backups/pred_reklamaci/Projects/Mine/portfolio-2
git add resources/css/app.css
git commit -m "feat(responsive): shrink phone sidebar on notebook tier to free content width

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>
Claude-Session: https://claude.ai/code/session_01C9Mt4Vq3HWEgyYhLPWBjdh"
```

---

### Task 3: Relax the home-page grids (`index.css`)

Rewrite the responsive section of `index.css` to the four-tier scheme: notebook gets its own band with 2×2 stats, 4-col tools, 2-col reviews, fluid project images, and a hero gap; tablet/mobile keep their existing treatment (with reviews' 1-col rule moved down from the old `≤1440` block).

**Files:**
- Modify: `resources/css/pages/index.css` — replace the entire `/* ── Responsive ── */` section (currently lines ~257–294, the three media blocks) with the block below.

**Interfaces:**
- Consumes: the widened notebook content column from Task 2.
- Produces: final responsive behaviour for `.stats-cards`, `.tools-row`, `.reviews-row`, `.projects-row`, `.hero-page` across all four tiers.

- [ ] **Step 1: Replace the responsive section of `index.css`**

Replace everything from `/* ── Responsive ── */` to the end of the file with:

```css
/* ── Responsive ── */

/* Notebook (993–1440px): relax the desktop grids for the narrower column */
@media (min-width: 993px) and (max-width: 1440px) {
    .stats-cards        { grid-template-columns: repeat(2, 1fr); } /* 2×2 */
    .stats-cards-card   { width: 100%; }

    .tools-row          { grid-template-columns: repeat(4, 1fr); justify-items: center; }

    /* 3 reviews → 2 per row, the third centred on its own row */
    .reviews-row        { flex-wrap: wrap; justify-content: center; }
    .reviews-row-card   { flex: 0 1 calc(50% - 0.75rem); max-width: 420px; }

    .projects-row       { height: 300px; }
    .projects-row > img { width: clamp(300px, 40%, 500px); }
    .projects-row-text  { min-width: 260px; }

    .hero-page-image img { width: 340px; }
}

/* Tablet + mobile: single-column, slide-in phone nav */
@media (max-width: 992px) {
    .hero-page {
        flex-direction: column;
        height: auto;
        margin-top: 10vh;
        margin-bottom: var(--sp-section);
    }
    .hero-page img { margin-top: 50px; }

    .stats-cards { grid-template-columns: repeat(2, 1fr); }

    .tools-row { grid-template-columns: repeat(4, 1fr); }

    .projects-row       { height: auto; }
    .projects-row > img { height: 220px; max-width: 300px; }

    .reviews-row        { flex-direction: column; align-items: center; }
    .reviews-row-card   { max-width: 450px; width: 100%; }
}

@media (max-width: 576px) {
    .stats-cards { grid-template-columns: 1fr; }

    .work-bot-line    { left: 55px; }
    .work-bot-content { padding: 3.125rem 1.25rem !important; }

    .projects-row       { flex-direction: column; height: auto; }
    .projects-row > img { order: 1; margin-bottom: 20px; height: auto; max-height: 300px; width: 100%; }
    .projects-row-text  { order: 2; }

    .tools-row      { grid-template-columns: repeat(2, 1fr); }
    .tools-row-card { width: auto; }
}
```

Note vs. the old code: the old `@media (max-width: 1440px)` block is gone. Its `stats-cards-card { width:100% }` moves into the notebook band; its `reviews-row { flex-direction: column }` moves into the `≤992` block; its `projects-row height/img` sizing is superseded by the notebook + `≤992` rules.

- [ ] **Step 2: Re-shoot home across notebook + mobile + PC**

Run: `node /tmp/claude-1000/-home-projektant-pata-Projekty-portfolio-ds/5369c6d7-27c7-4883-b96a-ae7500464c96/scratchpad/shoot.mjs t3 home home 390,768,1024,1280,1366,1700`
Expected: 6 `ok t3/...` lines.

- [ ] **Step 3: Verify each grid at notebook + no PC regression**

Read `scratchpad/audit/t3/home_1280.png` and `scratchpad/audit/t3/home_1700.png`.
Expected at 1280: stats are a clean 2×2 (no label wrap); tools are 4-col with the last row centred (no lone orphan hanging left); reviews are 2 per row + 1 centred below; project image/text both readable (text not collapsed). Expected at 1700: identical to `baseline/home_1700.png` above the footer (PC untouched). Also read `t3/home_768.png` and confirm tablet is unchanged from baseline. Tune values if any grid looks off, re-shoot.

- [ ] **Step 4: Commit**

```bash
cd /data/backups/pred_reklamaci/Projects/Mine/portfolio-2
git add resources/css/pages/index.css
git commit -m "feat(responsive): add notebook-tier grid rules for home page

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>
Claude-Session: https://claude.ai/code/session_01C9Mt4Vq3HWEgyYhLPWBjdh"
```

---

### Task 4: Footer full-bleed band (`portfolio-layout.blade.php` + `app.css`)

Move the footer out of the capped wrapper so it spans the full viewport width, and reduce it to a top-border-only band with its inner content still aligned to the page.

**Files:**
- Modify: `resources/views/components/portfolio-layout.blade.php` — relocate `<x-portfolio-footer />`.
- Modify: `resources/css/app.css` — `.portfolio-footer` + `.portfolio-footer-inner` rules (FOOTER area, ~lines 409–420) and footer padding in the `≤992` layout block.

**Interfaces:**
- Consumes: nothing from prior tasks.
- Produces: footer as a direct `<body>` child, full-width band, top border only, inner content capped at `var(--layout-max)`.

- [ ] **Step 1: Move the footer out of the wrapper in the Blade layout**

In `portfolio-layout.blade.php`, change:

```blade
        <div class="portfolio-col">
            <main class="portfolio-main">
                {{ $slot }}
            </main>

            <x-portfolio-footer />
        </div>

    </div>

</body>
```

to:

```blade
        <div class="portfolio-col">
            <main class="portfolio-main">
                {{ $slot }}
            </main>
        </div>

    </div>

    <x-portfolio-footer />

</body>
```

- [ ] **Step 2: Update `.portfolio-footer` + `.portfolio-footer-inner` in `app.css`**

Replace:

```css
.portfolio-footer {
    border: var(--border-w) solid var(--c-primary-lt);
    border-radius: var(--r-card) var(--r-card) 0 0;
    border-bottom: 0;
    background-color: var(--c-surface);
    padding: 3rem 4rem 1.5rem;
}

.portfolio-footer-inner {
    display: flex;
    gap: 2rem;
}
```

with:

```css
.portfolio-footer {
    border: 0;
    border-top: var(--border-w) solid var(--c-primary-lt);
    background-color: var(--c-surface);
    padding: 3rem 4rem 1.5rem;
}

.portfolio-footer-inner {
    display: flex;
    gap: 2rem;
    max-width: var(--layout-max);
    margin-inline: auto;
}
```

- [ ] **Step 3: Reduce footer padding on narrow screens**

In the existing `@media (max-width: 992px)` LAYOUT block (the one containing `.portfolio-footer-inner { flex-direction: column; }`), add a padding override so the full-width band isn't over-padded on small screens:

```css
@media (max-width: 992px) {
    .portfolio-wrapper {
        grid-template-columns: 1fr;
        width: 100%;
        padding-inline: 3rem;
    }

    .portfolio-sidebar {
        display: contents;
    }

    .portfolio-footer {
        padding: 2.5rem 1.5rem 1.5rem;
    }

    .portfolio-footer-inner {
        flex-direction: column;
    }
}
```

- [ ] **Step 4: Re-shoot the footer region across widths on two pages**

Run: `node /tmp/claude-1000/-home-projektant-pata-Projekty-portfolio-ds/5369c6d7-27c7-4883-b96a-ae7500464c96/scratchpad/shoot.mjs t4 home,proj home,proj 390,768,1280,1700`
Expected: 8 `ok t4/...` lines.

- [ ] **Step 5: Verify full-bleed + top-border only + no label overlap**

Read `scratchpad/audit/t4/home_1280.png` and `scratchpad/audit/t4/proj_1700.png`.
Expected: the footer surface spans the full viewport width edge-to-edge with only a top border (no rounded corners, no side borders); its inner columns (brand / Navigate / Follow me) stay aligned under the page content, not stretched to the screen edges; at 1280 the nav/social labels no longer overlap ("Instagram X X" reads cleanly). Confirm the footer sits flush at the page bottom with no gap beside the sidebar. Tune padding if needed, re-shoot.

- [ ] **Step 6: Commit**

```bash
cd /data/backups/pred_reklamaci/Projects/Mine/portfolio-2
git add resources/views/components/portfolio-layout.blade.php resources/css/app.css
git commit -m "feat(responsive): make footer a full-bleed top-border band

Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>
Claude-Session: https://claude.ai/code/session_01C9Mt4Vq3HWEgyYhLPWBjdh"
```

---

### Task 5: Full-sweep verification + regression check

Confirm every page at every tier, and that PC (≥1440) is unchanged above the footer.

**Files:** none (verification only).

- [ ] **Step 1: Shoot the full matrix (after state)**

Run: `node /tmp/claude-1000/-home-projektant-pata-Projekty-portfolio-ds/5369c6d7-27c7-4883-b96a-ae7500464c96/scratchpad/shoot.mjs after home,about,exp,proj 390,768,1024,1280,1366,1700`
Expected: 24 `ok after/...` lines then `done`.

- [ ] **Step 2: Compare notebook tier against baseline for all four pages**

Read `after/home_1280.png`, `after/about_1280.png`, `after/exp_1280.png`, `after/proj_1280.png` alongside their `baseline/` counterparts.
Expected: every page's notebook tier is de-cramped (2×2 stats where present, relaxed tools/reviews, aligned footer) and nothing is broken. `about-me` (shares `.stats-cards`) should show 2×2 stats and no phone clip.

- [ ] **Step 3: Confirm PC unchanged above the footer**

Read `after/home_1700.png` and `after/exp_1700.png` vs `baseline/` equivalents.
Expected: pixel-identical above the footer; only the footer differs (now full-bleed) — which is the intended change. If anything above the footer differs at 1700, a notebook rule leaked past 1440 — fix the media bound and re-shoot.

- [ ] **Step 4: Confirm mobile/tablet unchanged**

Read `after/home_390.png` and `after/home_768.png` vs `baseline/`.
Expected: unchanged except the footer band. If different, a rule leaked into `≤992`; fix and re-shoot.

- [ ] **Step 5: Final report**

Summarise for the user: which tiers/pages improved, the final tuned `scale` / `--sidebar-w` values from Task 2, and confirmation that PC and mobile are unchanged. No commit (verification only).

---

## Notes for the implementer

- **Live tuning is expected** in Tasks 2–4. The `scale: 0.88`, `--sidebar-w` clamp, `320–420px` maxes, and padding values are strong starting points; adjust against the actual render and record the final numbers. The screenshot step of each task is the gate.
- **Do not** touch `@theme` PC token values, colors, typography, phone-nav internals, or any admin/auth/settings view.
- If Vite isn't recompiling CSS, ensure `npm run dev` (or `composer dev`) is running; changes to `resources/css/*` are picked up by Vite HMR at `:5173`.
