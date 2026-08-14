# Design — dock hero on every public page

Roll the Experience page's `dock-hero` opener out to Home, About me and Projects,
and retire the `page-hero` component it replaces.

## Goal

One hero component across the four public pages. Home is the only page whose
hero fills the viewport outright: nothing of the next section shows below the
fold. About, Projects and Experience keep the peek that invites the scroll.

## Current state

| Page | Hero | CSS entry |
|---|---|---|
| Home (`welcome.blade.php`) | `<x-portfolio.page-hero full>` | `pages/index.css` → `components/page-hero.css` |
| About (`about-me.blade.php`) | `<x-portfolio.page-hero>` | `pages/about-me.css` → `components/page-hero.css` |
| Projects (`projects.blade.php`) | `<x-portfolio.page-hero>` | `pages/projects.css` → `components/page-hero.css` |
| Experience (`experience.blade.php`) | `<x-portfolio.dock-hero>` | `pages/experience.css` → `components/dock-hero.css` |

Two facts about `dock-hero` as shipped, both verified against the rendered
page rather than the source:

- **The dock column never renders.** `experience.blade.php` passes
  `:dock-image="config('portfolio.hero_images.experience_dock')"`, which is `''`,
  and passes no `dock-label` at all, so the `@if` in the component fails and the
  grid runs at `1fr 470px`, not `250px 1fr 470px`. The column is finished code
  waiting on a transparent device export.
- **`DockHeroTest.php:35` is a false positive.** `assertSee('Navigate')` matches
  `resources/lang/en/layout/footer.php` `nav_title`, not a dock label. The
  Czech counterpart matches `Navigace` the same way. Both assertions pass while
  asserting nothing.

## Decisions

1. All three remaining pages adopt `dock-hero`.
2. The dock column stays off everywhere, Experience included. No new image
   assets are needed for this work.
3. `page-hero` is deleted — blade and CSS — once it has no callers.
4. Home loses the peek; About and Projects keep it.
5. Hero copy keeps coming from `Setting`. Only the new dock-hero-specific
   strings (wordmark, tags, photo caption) are added, and they go in the lang
   files.
6. The tests that cover `page-hero` are rewritten against `dock-hero`, not
   deleted.

## Component changes

`resources/views/components/portfolio/dock-hero.blade.php` gains two props:

- **`full`** (bool, default `false`) — adds a `dock-hero--full` class. Home only.
- **`photoPosition`** (string, default `'52% 22%'`) — written onto the `<figure>`
  as `style="--dock-hero-photo-pos: …"`. The current `object-position` is hard
  tuned to the Experience photograph; the other three hero images crop badly
  under it. The CSS reads
  `object-position: var(--dock-hero-photo-pos, 52% 22%)`, so Experience is
  unaffected and each page can be dialled in during visual review.

Nothing else about the component's markup changes.

## CSS changes

`resources/css/components/dock-hero.css`

- Add the full-height variant next to the base `.dock-hero` rule, **above** the
  `@media (max-width: 1200px)` block:

  ```css
  .dock-hero--full { margin-bottom: var(--dock-hero-top); }
  ```

  Base `.dock-hero` is `margin-top: 3.5rem` + `min-height: calc(100dvh - 7rem)`
  + `margin-bottom: calc(3.5rem - 5dvh)`. Dropping the `- 5dvh` makes the box
  exactly `100dvh`, so the next section's top edge lands on the fold. Placement
  matters: `.dock-hero--full` and the media query's `.dock-hero` have equal
  specificity, so the later rule wins and mobile keeps its `4.5rem` bottom
  margin. Below 1200px the columns stack and the hero grows past one screen
  anyway — "the next section is not visible" is a desktop guarantee only.

- Add the entrance animation (see below).

`resources/css/pages/{index,about-me,projects}.css`

- Swap `@import '../components/page-hero.css'` for `'../components/dock-hero.css'`.

`resources/css/app.css`

- Delete `--hero-peek` (L168) — only `page-hero.css` reads it.
- Drop the `:not(.hero-page)` clauses from the scroll-reveal selectors
  (L436, L442). `dock-hero` deliberately carries no `portfolio-section` class,
  so it was never matched by them.

Delete `resources/css/components/page-hero.css`.

## Entrance animation

`initHeroEntrance()` (`resources/js/app.js:311`) adds `hero-loaded` to
`.hero-page` on the next frame; `page-hero.css:93-102` stages the reveal
(suptitle 0.05s → h1 0.15s → rotator 0.25s → image 0.35s). After the migration
that selector matches nothing and Home silently loses the staged entrance it
has today.

The animation is ported to `dock-hero`: the JS selector becomes `.dock-hero`,
and `dock-hero.css` grows the equivalent rules over
`.dock-hero-eyebrow → .dock-hero-title → .dock-hero-roles → .dock-hero-tags →
.dock-hero-photo`. The ghost wordmark is excluded — it is out of flow behind
the copy and a transform on it reads as a glitch.

**This changes Experience**, which has never had the entrance. That is the
price of one hero component; the alternative is deleting `initHeroEntrance()`
and letting Home lose the polish instead.

## Content

Hero eyebrow, title and roles keep coming from `Setting` — no new keys, no
seeder change. The dock-hero-specific strings are added per page:

| Page | Lang file | Keys |
|---|---|---|
| Home | `resources/lang/{en,cs}/home/hero.php` (exists) | `hero_wordmark`, `hero_tags`, `hero_photo_caption` |
| About | `resources/lang/{en,cs}/pages/about-me.php` (exists) | `hero_wordmark`, `hero_tags` |
| Projects | `resources/lang/{en,cs}/pages/projects.php` (new) | `hero_wordmark`, `hero_tags` |

Draft copy is written during implementation and reviewed by the owner before
the branch lands. Tags and caption are optional in the component — a page that
ends up without them renders fine.

Photos come from `config('portfolio.hero_images')`, which already has one entry
per page. No config change.

`hero_dock_label` in `pages/experience.php` stays where it is: it costs nothing
and is the string the dock column will use once the device shot exists.

## Scroll reveal under the hero

About and Projects gain `portfolio-section--no-reveal` on their first section
(`#about-me`, `#projects`), matching what Experience already does. The peek puts
that section's top edge inside the first viewport, and without the opt-out it
paints at `opacity: 0` until the observer fires.

Home does **not** get the opt-out: with no peek, its first section (`#stats`)
starts below the fold and reveals normally.

## Tests

`tests/Feature/PageHeroTest.php` (7 tests) and `tests/Browser/PageHeroTest.php`
(8 tests) are rewritten against `dock-hero` and renamed to match. Points where
the expectation genuinely inverts:

- *"a subpage hero fills the first screen and still lets the next section peek
  in"* — still true for About and Projects, false for Home. Home needs its own
  assertion: the next section's top edge sits at or below the fold.
- *"the section after a subpage hero fades in on scroll like every other
  section"* — now false for About and Projects (they opt out) and true for Home.

`tests/Feature/DockHeroTest.php`: delete the two `Navigate` / `Navigace`
assertions in *"the dock hero renders the wordmark, dock label and tags from the
lang files"* and rename it — they assert nothing, and the dock column they claim
to cover does not render. The two dock-column tests below them stay; they drive
the component through `config()`, not through a rendered dock.

New coverage:

- Each of the four public pages renders `class="dock-hero"` and no `hero-page`.
- Each page still renders exactly one `<h1>` and one `id="hero-rotator"`
  (the rotator id must stay unique per page).
- Home carries `dock-hero--full`; the other three do not.
- Wordmark and tags render per page in both locales.

## Files touched

```
resources/views/components/portfolio/dock-hero.blade.php   props
resources/views/components/portfolio/page-hero.blade.php   delete
resources/views/{welcome,about-me,projects}.blade.php      hero swap, --no-reveal
resources/css/components/dock-hero.css                     --full, entrance
resources/css/components/page-hero.css                     delete
resources/css/pages/{index,about-me,projects}.css          import swap
resources/css/app.css                                      --hero-peek, selectors
resources/js/app.js                                        initHeroEntrance selector
resources/lang/{en,cs}/home/hero.php                       hero keys
resources/lang/{en,cs}/pages/projects.php                  new
resources/lang/{en,cs}/pages/about-me.php                  hero keys
tests/Feature/PageHeroTest.php                             rewrite
tests/Browser/PageHeroTest.php                             rewrite
tests/Feature/DockHeroTest.php                             fix false positive
```

## Risks

- **Photo crops.** The three hero webps have never been seen inside a 470px
  column with the dock-hero gradient over them. `photoPosition` is the lever;
  each page needs a look at 1440px and at 390px before the branch lands.
- **Ghost wordmark collisions.** The wordmark is `clamp()`ed and sits behind the
  copy. Longer wordmarks than `Experience` (e.g. `projektant-pata`) may run past
  the copy column. Checked at the same two widths.
- **Home is the busiest page.** Six sections follow the hero; the reveal
  observer and the scroll-progress bar both read section geometry. Worth one
  scroll pass end to end after the swap.
