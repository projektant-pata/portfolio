# Design Upgrade Ideas

Design review of the four public pages, done in-browser (Chromium via Playwright against
http://localhost:8008) at 1440×900 (desktop) and 390×844 (mobile), in both dark and light
themes. Sorted by impact. File pointers included where the cause is known.

---

## 1. Page openers ("starting sections") for About me / Experience / Projects

This is the biggest structural gap. The home page has a proper hero (eyebrow `HELLO WORLD!`,
big h1, typing rotator, photo), but the three subpages drop the visitor straight into content
cards. The only "title" they get is the giant outlined watermark h2, which is deliberately
half-swallowed under the first card (`margin-bottom: -0.45em` in `app.css`) — so the pages
effectively start mid-thought, with no place for the eye to land and no context.

Proposal: a shared `<x-portfolio.page-hero>` component used by all three pages. Keep it short
(not a full-viewport hero — maybe 30–40vh) so content stays one small scroll away:

- **Eyebrow** in the same style as the home page's `HELLO WORLD!` (mono, small caps, icon).
  Suggested: `WHO AM I` / `WHERE I'VE BEEN` / `WHAT I'VE BUILT`.
- **Solid h1 title** (not the outlined watermark — keep the watermark h2 as the section
  backdrop below, but give the page a readable title at the top).
- **One-sentence subtitle** — a human intro line, translatable, editable via the existing
  Settings admin (same pattern as `stats_title`).
- **Meta row** of small facts, per page:
  - *About me*: age · location · school · "chess player" chip — plus a real **portrait photo**.
    Right now the only photo of you on the About page is the hero photo on Home; the About
    page is anonymous, which is backwards.
  - *Experience*: entry count + year span ("11 entries · 2021 – 2026") + Work/Life counts.
    The filter bar could live inside/right under this hero, making it feel intentional
    instead of a floating toolbar.
  - *Projects*: project count + tech chips (Laravel, Symfony, …) + a "featured" pointer or
    CTA to GitHub.
- Optional: a small scroll cue (chevron / "scroll" mono label) shared with the home hero.

This also fixes the abrupt rhythm: eyebrow → title → subtitle → meta → watermark section
below it, consistent across all pages.

## 2. Legibility bugs with the outlined watermark headings

The watermark-behind-card treatment is a great signature, but it currently eats letters:

- **"My Stats" reads as "Mu Stats."** The negative overlap (`.portfolio-page h2 {
  margin-bottom: -0.45em }` in `resources/css/app.css:330`) hides exactly the y-descender.
  I verified the DB says "My Stats" — the design makes it misread. Reduce the overlap
  (≈ `-0.25em`), or apply the overlap only on headings without descenders, or switch the
  watermark to an all-caps style (`MY STATS`) where no descenders exist. All-caps is the
  safest fix and looks more like a classic watermark.

  > **Closed 2026-08-17** — every public section heading now renders through
  > `<x-portfolio.section-head>`; the ghost wordmark is `aria-hidden` and is no longer
  > the accessible heading itself (the real `h2` is), so a clipped descender on the ghost
  > is no longer a misreading of the section's name. The global `.portfolio-page h2`
  > watermark rule this bullet's line reference points at is gone entirely — retired, not
  > moved, so the `app.css:330` citation above is now dead; there is no successor line to
  > repoint it to. This closes the "My Stats" bullet specifically. It does **not** close
  > the next two bullets: the footer wordmark (`.portfolio-footer-watermark`) keeps the
  > same negative-margin/descender-clip treatment **by design** — it now carries its own
  > copy of the old rule rather than inheriting it — and the mobile edge-clipping bullet
  > below was not touched by this rollout either.
- **Footer name `projektant-pata`** has its `j`/`p` descenders clipped by the footer card —
  same cause, same fix.
- **Mobile: watermark headings clip at the viewport edges** ("Projects", "Reviews", footer
  name are cut off left/right at 390px). Clamp `--fs-h2` harder on small screens or allow
  the watermark to shrink-to-fit (`text-wrap: balance` won't help a single word — needs a
  smaller clamp floor).

## 3. Light theme needs a pass of its own

Dark is clearly the primary theme and it shows:

- The watermark h2s render as **near-solid yellow** in light mode — the 12% fill +
  1px stroke that reads as a subtle outline on dark becomes a heavy filled headline on
  cream, and yellow-on-cream contrast is poor. Give light mode its own watermark recipe
  (e.g. stroke-only at low alpha of a darker tone, or a warm gray fill).
- Hero accent words ("projektant-pata", the typed "developer") drop to a washed
  tan/olive that loses the gold identity. Consider a darker gold (`#8a6d00`-ish) for
  light-mode accents instead of alpha-mixing the same gold.
- Footer + watermark yellows are the loudest elements on the page in light mode, inverting
  the visual hierarchy.

## 4. Experience page tweaks

- **Timeline ordering is broken**: the column reads 2024, 2024, 2023, 2022, 2021, 2021 …
  and then **2026 (chess) dead last**. Newest-first should put 2026 on top. Worth checking
  how `year` (jsonb — key order not stable, see CLAUDE.md) is sorted.
- **Apparent duplicate**: "Hackathon AstroPi" (2024, LIFE) and "AstroPi Hackathon 2024"
  (2024, WORK) both exist. If intentional (event vs. work role), the titles should
  differentiate more; as-is it looks like seeded duplicate data.
- **Typos in seed data** (`database/seeders/ExperienceSeeder.php:154-155`):
  "competetion" → "competition" (twice, EN only). There's also a `'competetion'` badge
  color key in `2026_04_12_165907_seed_badge_colors.php` — check whether the badge name
  key needs to stay misspelled to match data. Also "Automatization of application in
  Selenium" → "Automation…" (ByEvolution entry).
- The **center timeline spine** renders as disconnected dot-plus-short-segment fragments;
  a continuous line (or intentionally dotted line) between the columns would read better.
- The two-column card layout leaves a big empty block bottom-left (after the chess card).
  Consider CSS masonry-style balancing (`grid-template-rows: masonry` isn't shippable, but
  ordering cards by height parity helps).

## 5. Projects page tweaks

- **Huge vertical gaps** between project rows (~1.5 screen heights of dead space between
  Portfolio → U Sladovny → SPŠE Hub at 1440px). Halving the row gap keeps the year-marker
  drama without the "is the page over?" moment.
- The **giant year numerals** (2026/2025/2022) get partially covered by the project card —
  same descender/overlap issue family as §2, plus at some widths the card overlaps digits.
  Cap the overlap so at least the full digit height stays visible.
- **Screenshot framing is inconsistent**: Portfolio's screenshot has odd cropping (phone
  nav visible, lots of dark space), U Sladovny is a tight hero shot, SPŠE Hub is a
  half-white/half-photo split that reads chaotic. A uniform treatment (browser-chrome
  mockup frame, fixed aspect ratio, consistent corner radius) would tie the section
  together.
- Only 3 projects but "5+ Projects Completed" in stats — either add the missing ones or
  the stat undersells/oversells. Easy win: seed the remaining projects.
- Project rows could carry **tech badges** (the badge system already exists for
  Experience) — right now the cards say nothing about stack at a glance.

## 6. Home page tweaks

- **Hero right side**: the photo is a plain rectangle floating in empty space, and there is
  a lot of dead area between the text block and the photo at 1440px. Ideas: give the photo
  the site's card treatment (border, radius, slight rotation or gold frame), add a caption
  strip (name / role / location), or anchor it to the bottom of the hero.
- **Work & Life tabs**: both tabs look nearly identical (two outlined pills); the active
  state needs more contrast — filled gold active pill vs. ghost inactive.
- **Stats card grid** centers rows into a 3-2-3-2-1 pyramid (About page) / 2-2 (Home).
  The pyramid looks accidental rather than designed. A fixed-column grid (3 or 5 across,
  last row left-aligned) or an intentional stagger would look more deliberate.
- **Reviews carousel**: 8 pagination dots for 6-ish reviews at 3-up — dots outnumber pages
  visually and are tiny targets. Page-based dots (2–3) or prev/next arrows would be
  cleaner. Review body text is also the smallest text on the site — bump a step.
- **Tools grid**: mixed icon languages (full-color brand icons vs. mono glyphs vs. pixel-art
  Claude) at different visual weights. Normalizing size + adding a hover state (tooltip
  with proficiency or years) would make it feel curated. Centered orphan row ("Arch
  Linux, Claude Code") — same fix as stats grid.

## 7. Mobile tweaks (390px)

- Hero h1 wraps `projektant-pata,` awkwardly and takes 4 lines with the trailing comma
  dangling. Lower the h1 clamp floor on mobile, or drop the comma on small screens.
- **Stats section is a single-column tower** — 10 full-width cards on About ≈ 4 screens of
  scrolling for one-line facts. Two-up grid on mobile halves it and these cards are small
  enough to pair.
- Watermark heading clipping — covered in §2.
- The phone-mockup nav is desktop-only (hamburger on mobile) — fine, but the hamburger sits
  alone with no brand mark; adding the site name top-left would anchor the mobile header.

## 8. Small consistency notes

- The phone-mockup sidebar is a fantastic signature element — consider making its
  wallpaper/time react to something (theme already swaps wallpaper; time could be real
  client time) as a delight detail. (Check first whether the clock is already live.)
- Section vertical rhythm varies (Stats→Work&Life gap ≠ Projects→Tools gap on Home);
  a shared `--section-gap` token would tighten the scroll feel.
- Home "Projects" section and Projects page use different card layouts for the same
  content — acceptable, but the home variant's screenshot crops are rougher.
- "ChatGPT — The best AI" review with the `Reference` badge is a fun easter egg; keep it,
  but maybe visually mark joke reviews (wink emoji?) so recruiters don't misread the
  section's credibility.

---

*Screenshot artifact note: full-page screenshots show a horizontal "seam" at ~900px and the
film-grain overlay only over the first viewport — that's `position: fixed` + Playwright
full-page capture, not a real rendering bug. Verified the live page is fine.*
