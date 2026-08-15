# Section head rollout — finishing touches — design

**Date:** 2026-08-15
**Source:** follow-up to `docs/superpowers/specs/2026-08-15-section-head-design.md` (the original `sechead` component + home rollout).
**Scope:** four independent pieces that finish the section-head system across the whole site:

1. Home — add ghosts to the two sections that shipped without one (Work & Life, Tools).
2. `dock-hero` — reposition the hero's ghost wordmark to sit above the title like `sechead`'s does, instead of overlapping behind it.
3. Footer — the "projektant-pata" wordmark stops inheriting the outlined `.portfolio-page h2` watermark rule and gets its own solid-fill big-font treatment.
4. Roll `x-portfolio.section-head` out to `about-me`, `experience`, `projects` — the three pages the original spec explicitly deferred.

## 1. Home ghosts

`resources/views/welcome.blade.php` — two sections currently render `<x-portfolio.section-head>` without a `ghost` prop, per the original spec's "Work & Life and Tools stay ghost-less" rule (a fourth/fifth ghost would put ghosts on every section, back to back). That rule is explicitly overridden here — reversed by direct instruction.

- Work & Life (`:36-41`): add `:ghost="__('home/experience.title')"` → renders "Experience" / "Zkušenosti". Reuses the existing `title` key already in `home/experience.php`; no new lang entries.
- Tools (`:81-86`): add `:ghost="__('home/tools.title')"` → renders "Tools" / "Nástroje". Reuses the existing `title` key already in `home/tools.php`.

Result: all five home sections (Stats, Work & Life, Projects, Tools, Reviews) carry a ghost, consecutively. This is a deliberate content-rule deviation, not an oversight — noted here so a future reader doesn't "fix" it back.

## 2. `dock-hero` ghost reposition

`resources/views/components/portfolio/dock-hero.blade.php` (no markup change) + `resources/css/components/dock-hero.css` (`.dock-hero-ghost`, `.dock-hero-copy`, and their two breakpoint overrides at `1200px` and `560px`).

**Current behavior:** `.dock-hero-ghost` is `position:absolute; top:-0.5rem` inside `.dock-hero-copy` (`padding-top:3.4rem` desktop). The ghost's transparent-stroke box overlaps the eyebrow/title as background texture — it does not read as its own line.

**Target behavior**, matching `.sechead-ghost`'s relationship to `.sechead-row`: the ghost sits as a visually separate line above the eyebrow, with a real gap before the title block starts. Concretely:

- `.dock-hero-ghost`'s `top` moves further negative (clears the copy column's content instead of sitting just above it).
- `.dock-hero-copy`'s `padding-top` grows to match, so the eyebrow/title never sit under the ghost's line box.
- Same pairing repeated in the `@media (max-width:1200px)` (stacked layout, ghost already repositioned to `left:2rem; top:0.5rem`) and `@media (max-width:560px)` rules — both need their `top`/`padding-top` pair re-tuned the same way, not just desktop.
- Exact `rem` values are tuned empirically during implementation (build on host, eyeball at 1440/1100/760/360, both themes) — same process the original ghost-clip fix used. Not locked here.

**Included fix:** `.dock-hero-ghost` currently has `line-height:1` with no descender guard — the same bug class as the `Mu Stats` clip that `.sechead-ghost` already had to fix with `line-height:1.12` + `padding-bottom:.1em`. Since this rule is being touched anyway, the same two declarations are added here. Verify on a descender-bearing wordmark (e.g. "Projects").

This is one shared component — the change lands once and applies to all four consumers: home (`dock-hero--full`, no peek), about-me, experience (has a dock column), projects. All four get visually verified after the change, not just the one used to tune the values.

## 3. Footer — solid big font

`resources/css/app.css`, `.portfolio-footer-watermark` (currently just `z-index:0`, inheriting everything else from the generic `.portfolio-page h2` rule at `:348`).

New standalone declarations, no longer dependent on the global rule:

```css
.portfolio-footer-watermark {
    z-index: 0;
    text-align: center;
    margin-bottom: -0.45em;
    font-size: var(--fs-h2);
    font-family: var(--font-display);
    font-weight: var(--fw-bold);
    color: var(--c-fg);
    overflow-wrap: anywhere;
}
```

Dropped from the old rule: `color-mix(... var(--c-watermark) ...)` and `-webkit-text-stroke` — those two are what made it an outline. Everything else (`text-align`, the `-0.45em` overlap that pulls the footer band up over the letters, `font-size`, `overflow-wrap`) carries over unchanged; the overlap trick is a layout mechanic independent of outline-vs-solid.

Verify at `≤992px` (full-bleed band, `margin-left` un-set) and `≥993px` (card footer, `margin-left: calc(var(--sidebar-w) + var(--content-gap))`) — both already have their own `.portfolio-footer-watermark` overrides at `:669` and need to keep applying on top of the new base rule, not fight it.

## 4. Rollout to about-me / experience / projects

Each page adds `@import '../components/section-head.css';` to the top of its page CSS entry (`resources/css/pages/{about-me,experience,projects}.css`), alongside the existing `@import '../components/dock-hero.css';`.

### about-me (two sections, both currently reading admin-editable Settings)

`resources/views/about-me.blade.php`:

- **About Me** (`:20`, currently `<h2>{!! Setting::text('about_title', $locale) !!}</h2>`): becomes
  ```blade
  <x-portfolio.section-head
      :eyebrow="__('pages/about-me.head_eyebrow')"
      :title="\App\Models\Setting::text('about_title', $locale)"
  />
  ```
  `title` stays bound to the same admin-editable Setting, unescaped exactly as today — the "Section titles" field in `⚡site-content` keeps working unchanged. No `ghost` (the hero above already carries the "About me" wordmark — a second one would repeat it) and no `note`.

- **Stats** (`:33`, currently `<h2>{{ Setting::text('stats_title', $locale) }}</h2>`): becomes
  ```blade
  <x-portfolio.section-head
      :ghost="__('home/stats.head_ghost')"
      :eyebrow="__('pages/about-me.stats_head_eyebrow')"
      :title="\App\Models\Setting::text('stats_title', $locale)"
  />
  ```
  `title` stays bound to `stats_title` (same admin field, still live — home no longer reads it but about-me does, per the original spec's note). `ghost` reuses `home/stats.head_ghost` ("My stats" / "Statistiky") — same sub-brand as home's Stats section, no collision with the page's own "About me" hero ghost. No `note`.

New keys in `resources/lang/{en,cs}/pages/about-me.php`:
```php
'head_eyebrow' => 'The short version',       // cs: 'Ve zkratce'
'stats_head_eyebrow' => 'By the numbers',    // cs: 'V číslech'
```

### experience (one section, filter bar directly under the head)

`resources/views/experience.blade.php:22`, currently `<h2>{{ __('home/experience.title') }}</h2>`:
```blade
<x-portfolio.section-head
    :eyebrow="__('pages/experience.head_eyebrow')"
    :title="__('home/experience.title')"
/>
```
`title` reuses the existing plain "Experience" / "Zkušenosti" string (no gold `<em>` — it's a label, not a sentence, matching the original title's tone). No `ghost` (hero above already carries "Experience"; the filter bar sits right under the head, same "busy content, no ghost" precedent as home's Work & Life). No `note`.

New key in `resources/lang/{en,cs}/pages/experience.php`:
```php
'head_eyebrow' => 'Full record',   // cs: 'Celý přehled'
```

### projects (no page-level head exists today — new addition)

`resources/views/projects.blade.php` — currently jumps straight from the hero into `@forelse ($projects as $year => $yearProjects)`, no section head at all (only the per-year `<h2 class="projects-year-label">` inside the loop, which stays untouched — it's a repeating in-page label, not a page-level head, and keeps relying on the global `.portfolio-page h2` rule as it does today).

Add before the `@forelse`:
```blade
<x-portfolio.section-head
    :eyebrow="__('pages/projects.head_eyebrow')"
    :title="__('home/projects.title')"
/>
```
`title` reuses the existing plain "Projects" / "Projekty" string. No `ghost` (hero above already carries "Projects"). No `note`.

New key in `resources/lang/{en,cs}/pages/projects.php`:
```php
'head_eyebrow' => 'By year',   // cs: 'Podle roku'
```

## Global watermark rule — still alive, on purpose

After this rollout, `.portfolio-page h2` (`app.css:348`) is still depended on by exactly one thing: `.projects-year-label`. It is explicitly **not** deleted — that per-year label is out of scope (it's not a page-level section head, and the original spec's plan for deleting the global rule was tied to page-level heads migrating, which `.projects-year-label` isn't). The footer no longer depends on it after piece 3 above.

## Testing

- Feature: extend `tests/Feature/SectionHeadTest.php` (or a new file) with assertions for the two new home ghosts, and new assertions per page — about-me/experience/projects each `assertSee` their new eyebrow strings and `aria-hidden` ghost where applicable, in both locales.
- Feature: `about_title` / `stats_title` Setting-editing still renders through the new about-me sechead markup — extend or spot-check `SiteContentManagementTest`, do not weaken its assertions.
- Browser: extend `tests/Browser/SectionHeadTest.php`'s no-sideways-scroll check (360/760/1100/1440) to also visit about-me, experience, projects — the ghost reposition changes hero padding on every page, and a new head is added on projects.
- Visual pass (host `npm run build`, both themes, all four widths): hero ghost reads as a clean separate line on all four dock-hero pages; footer wordmark is solid-filled, not outlined, at both `≤992px` and `≥993px`; every new eyebrow/ghost renders in both locales; no clipped descenders anywhere a ghost is shown.

## Non-goals

- Deleting `.portfolio-page h2` — `.projects-year-label` still needs it.
- Touching `.projects-year-label` styling itself.
- Any change to `stats_title` / `about_title` / their admin-editable behavior in `⚡site-content` — the Settings keep working exactly as before, sechead just renders them now.
- New content/copy review for the review carousel or any card content — untouched.
