# Experience dock hero — design

Source: `projektant-pata Design System.zip` → `design_handoff_experience_hero/`
(README.md, `experience-hero.html`, `hero.css`, placeholder assets).

## Goal

Replace the shared `<x-portfolio.page-hero>` opener on the Experience page with the
handoff's bordered three-column composition: a labelled dock column, a copy column
carrying an outlined wordmark, and a full-bleed photo column. The hero's bottom edge
hands off to the existing `.exp-filterbar` below it.

The component is built generic (`<x-portfolio.dock-hero>`, `.dock-hero*` classes) so
About and Projects can adopt it later by passing different props. This spec migrates
Experience only.

## Deviations from the handoff

Three intentional departures, decided with the site owner:

1. **No metric strip.** The handoff's five-figure bottom row (`Years active`,
   `Entries`, `Competitions`, `Countries`, `Production deploy`) is dropped entirely —
   the owner does not want it. The container therefore has one grid row, not two, and
   `grid-template-rows: 1fr auto` is unnecessary.
2. **`min-height: 640px`, not `720px`.** The handoff's 720px included the ~70px strip.
   Keeping 720px without the strip opens dead air between the wordmark and the
   bottom-aligned copy.
3. **Lede replaced by the roles rotator.** The handoff's static lede paragraph is
   replaced by the site's existing rotating-role text (`#hero-rotator` + blinking
   caret), fed by the `experience_hero_roles` setting that already exists.

Two smaller changes:

- **Eyebrow keeps its emoji** (`🗓️ Where I've been`) and normal letter-spacing. The
  handoff's `letter-spacing: .18em` uppercase treatment and its `::before` 26×1px rule
  are both dropped — the emoji is the section marker instead of the rule.
- **Photo caption seeds empty.** The handoff caption describes a Tour de App talk; the
  photo column reuses the existing portrait, where that copy would be false.

## Architecture

### Component

`resources/views/components/portfolio/dock-hero.blade.php`

| Prop | Type | Notes |
| --- | --- | --- |
| `eyebrow` | string | rendered as-is, emoji included; omitted when empty |
| `title` | HTML string | accent is `<span>` (site convention), styled weight 500 + `--c-primary` |
| `roles` | array | rotator source; rotator rendered only when `count > 1` |
| `tags` | array | non-interactive summary chips; row omitted when empty |
| `wordmark` | string | outlined background word, `aria-hidden` |
| `dockLabel` | string | e.g. `Navigate` |
| `dockImage` | string | asset path; column renders label-only when empty |
| `photo` | string | asset path |
| `photoAlt` | string | |
| `caption` | HTML string | optional; when empty, no `<figcaption>` and no bottom gradient |

The rotator keeps the `hero-rotator` id that `resources/js/app.js` binds to. One hero
per page keeps it unique, and the existing `if (!el) return` guard means pages without
it are unaffected. No new JavaScript.

### CSS

- `resources/css/components/hero-rotator.css` — **new**, holds the rotator/caret rules
  extracted verbatim from `page-hero.css` (`.hero-caret`, `@keyframes caret-blink`,
  `#hero-rotator span`). Imported by both `page-hero.css` and `dock-hero.css`, so the
  two heroes share one definition.
- `resources/css/components/dock-hero.css` — **new**, all `.dock-hero*` rules.
- `resources/css/pages/experience.css` — imports `dock-hero.css`; keeps its
  `page-hero.css` import only if something else on the page still needs it (it does
  not, so the import is removed).

No new design tokens. The handoff palette is value-identical to the existing `@theme`
block in `resources/css/app.css` for both themes, so everything resolves from
`--c-primary`, `--c-primary-fade`, `--c-bg`, `--c-surface`, `--c-fg`, `--c-muted`,
`--border-w`, `--r-card`, `--t-fast`.

### Layout

The hero does **not** carry the `portfolio-section` class. That class sets
`opacity:0` on everything but `.hero-page` until the scroll observer adds
`.is-visible`, which is wrong for an above-the-fold opener, and its
`margin-bottom: var(--sp-section)` would fight the design's `2.25rem`.

Container `.dock-hero`: `display:grid; grid-template-columns:250px 1fr 470px;
min-height:640px; position:relative; overflow:hidden`, border
`var(--border-w) solid var(--c-primary-fade)`, radius `var(--r-card)`, background
`var(--c-bg)`, `margin-bottom:2.25rem` (the gap `.exp-filterbar` already uses).

- **Dock** (`250px`): column flex, `align-items:center`, `padding-top:2.6rem`,
  `overflow:hidden`, `border-right:1px solid color-mix(in srgb, var(--c-primary-fade)
  55%, transparent)`, top-down surface gradient. Label 10px/600/`.2em`/uppercase/
  `--c-muted`. Image 178px wide with `drop-shadow(0 24px 50px rgba(0,0,0,.55))`,
  deliberately cropped by the column's bottom edge.
- **Copy** (`1fr`): column flex, `justify-content:flex-end`, `gap:1.5rem`,
  `padding:4.6rem 3.4rem 3.6rem`. Wordmark is `position:absolute; left:0; top:-.5rem;
  z-index:0` **inside this column** (not a grid child), Space Grotesk 700, 132px,
  `letter-spacing:-.04em`, `color:transparent`, `-webkit-text-stroke:1px color-mix(in
  srgb, var(--c-primary-fade) 80%, transparent)`, `pointer-events:none`.
  Headline Space Grotesk 700, 70px, `line-height:.96`, `letter-spacing:-.02em`.
  Rotator line sits at the lede's slot and size (≈15.5px), muted, current role in
  `--c-fg` weight 600 with the gold caret. Below it the tag row: chips with
  `padding:.4rem .8rem`, radius `999px`, `1px solid color-mix(in srgb,
  var(--c-primary-fade) 75%, transparent)`, transparent background, 11.5px/500,
  `--c-muted`, `cursor:default`; hover moves border to `--c-primary` and text to
  `--c-fg` over `--t-fast`. They are deliberately smaller and dot-less so they do not
  read as the filter bar's `.exp-tag` buttons below.
- **Photo** (`470px`): `<figure>` `position:relative; overflow:hidden`; image
  `position:absolute; inset:0; width/height:100%; object-fit:cover; object-position:
  52% 22%`. `::after` overlay stacks two gradients — bottom-up dark for caption
  legibility (only when a caption exists) and a left-edge `--c-bg` wash to soften the
  seam against the copy column. Caption colours are literal (`#D6D3D1`, `#fff`), not
  tokens, because they sit on the photo in both themes.

### Responsive

- **≤1200px** — single column, order dock → copy → photo. Dock becomes a row
  (`flex-direction:row; align-items:flex-end; gap:1.6rem; padding:2rem 2rem 0`),
  right border becomes bottom border, phone 130px. Copy gains `padding-top:9.5rem` to
  clear the wordmark, which shrinks to `clamp(64px, 11vw, 120px)` at `top:1.4rem`.
  Photo gets `min-height:380px` and drops its horizontal gradient.
- **≤560px** — headline `clamp(38px, 11vw, 70px)`.

## Content

All hero prose stays admin-editable through the existing `Setting` model and the
`⚡site-content` manage page, under the existing `Experience hero` group.

| Key | Status | Notes |
| --- | --- | --- |
| `experience_hero_suptitle` | exists, unchanged | emoji kept |
| `experience_hero_title` | exists, unchanged | keeps its `<span>` accent |
| `experience_hero_roles` | exists, unchanged | now feeds the rotator inside the dock hero |
| `experience_hero_tags` | **new**, list | 5 summary chips, en/cs |
| `experience_hero_photo_caption` | **new** | optional, seeds empty in both locales |

The wordmark is a lang key, not a setting — it is the page's name, not prose:
`resources/lang/{en,cs}/pages/experience.php` → `hero_wordmark` (`Experience` /
`Zkušenosti`). The dock label uses the same file → `hero_dock_label` (`Navigate` /
`Navigace`).

Seeder note: `SettingSeeder` must remain idempotent for existing installs; the new key
is added the same way the current keys are written.

## Assets

- **Photo** — reuses `config('portfolio.hero_images.experience')` →
  `public/images/experience-hero.webp`. `object-position` is retuned against the real
  crop during implementation.
- **Dock image** — new config key `portfolio.hero_images.experience_dock` →
  `public/images/experience-dock.webp`. The owner supplies the export (transparent,
  ~356px wide, no baked-in shadow — the CSS drop-shadow supplies it). Until the file
  exists the key is set to `''` and the dock renders label-only: no broken image, no
  layout shift beyond the missing picture.

## Testing

- `tests/Feature/PageHeroTest.php` and `tests/Browser/PageHeroTest.php` drop
  `experience` from their datasets — that page no longer renders `.hero-page`, so the
  shared-hero assertions and the subpage peek-geometry assertion no longer apply to it.
- `tests/Feature/DockHeroTest.php` (new): Experience renders eyebrow, title with a live
  `<em>`, rotator with `data-roles`, the wordmark, and one chip per tag setting; cs
  locale renders cs copy;
  caption markup absent when the setting is empty and present when set; dock `<img>`
  absent when the config path is empty.
- `tests/Browser/DockHeroTest.php` (new): three columns side by side at 1440px, stacked
  in dock → copy → photo order at 1100px and 520px, hero's bottom edge directly above
  `.exp-filterbar`, light theme renders the 2px border.

Translatable settings are asserted with `toEqual`, never `toBe` — Postgres reorders
`jsonb` keys.

## Out of scope

- Migrating About / Projects / Home to `<x-portfolio.dock-hero>`.
- Any metric, counter, or figure UI.
- Making the hero's tag chips drive the filter bar. They stay display-only `<span>`s.
  If they should filter later, promote them to `<button>` and reuse the filter bar's
  `.exp-tag` styling plus `aria-pressed` rather than restyling these.
- Replacing the photo asset itself.
