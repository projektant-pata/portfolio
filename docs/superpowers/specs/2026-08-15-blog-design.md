# Blog Index + Article Detail — Design

**Date:** 2026-08-15
**Design handoff:** `blog.zip` → `design_handoff_blog/` (`README.md`, `blog-index.html`, `blog-article.html`, `blog.css`, `assets/`)
**Status:** approved, ready for an implementation plan

## Goal

Ship the fifth public page — `/blog` — and the article detail it links to, using the handoff's ledger listing and Markdown reading experience, but opened by **our existing `<x-portfolio.dock-hero>`** rather than the handoff's `.blog-hero`.

The `Article` model, its bilingual JSON columns, its badge pivot and its admin CRUD already exist and have never been rendered publicly. This work is the public half.

## Deliberate deviations from the handoff

The owner instructed: *use our hero, take the handoff for everything else.* That decision cascades:

| Handoff says | We ship | Why |
| --- | --- | --- |
| `.blog-hero` — bordered 2-column composition, own CSS | `<x-portfolio.dock-hero>`, same call shape as Experience | Owner's instruction; one hero component site-wide instead of two |
| Hero lede paragraph (`.blog-hero-lede`) | dropped | `dock-hero` has no lede slot. The copy survives as the page's `<meta name="description">` |
| Latest-post rail (`.blog-hero-latest`) | dropped | It is a bottom row of *their* hero; `dock-hero` has no such row. The newest post is already the lead row of the ledger |
| Ghost wordmark, eyebrow, rotating roles, tag chips | kept — they are `dock-hero` props already | — |
| `.blog-progress` scroll hairline | reuse the global `#scroll-progress` in `portfolio-layout` | We already ship exactly this (`resources/js/app.js:320-333`); a second bar would double up |
| Reading time stored at save time | computed on render | `content` is bilingual, so one stored integer is already wrong. `ceil(words / 200)` per locale, cached with the rendered HTML |

Everything else in the handoff — listing anatomy, states, no-thumbnail ghost numeral, badge filter as a query param, article shell, the whole `.blog-prose` scale, breakpoints, both languages' copy — ships as specified.

## 1 · Routes

```php
Route::get('/blog', BlogController::class)->name('blog');
Route::get('/blog/{article:slug}', ArticleController::class)->name('blog.show');
```

Single-action invokable controllers, matching `Home`/`AboutMe`/`Experience`/`Projects`. `ArticleController` 404s on an unpublished article (implicit binding resolves the slug; the publish check lives in the controller so the 404 is explicit and testable).

## 2 · Model changes

**Migration** `add_published_at_to_articles_table`:
- `timestamp('published_at')->nullable()->after('date')`, indexed.
- Backfill: existing rows get `published_at = date` so nothing already written silently disappears.

**`App\Models\Article`:**
- `published_at` in `$fillable` and cast to `datetime`.
- `scopePublished()` → `whereNotNull('published_at')->where('published_at', '<=', now())`.
- `isPublished(): bool`.
- `readingTime(string $locale): int` → `max(1, (int) ceil($words / 200))`, where `$words` counts `preg_split('/\s+/u', …)` chunks of the markdown source — **not** `str_word_count`, which drops Czech diacritics and would undercount every `cs` post.
- `archiveIndex` — the row's position in the whole published archive, oldest = `01`. Computed once in the controller (`$published->reverse()->values()`), passed to the row component; not a model method, because it is a property of the list, not the article.

`date` stays the display date (the ledger rail, the article header). `published_at` is purely the gate. They are seeded to the same value but can diverge — that is intentional and matches how the owner writes: a post dated when it happened, published later.

**`ArticleFactory`:** `published()` / `draft()` / `scheduled()` states.

**Admin `⚡articles`:** publish datetime input in the form, a `Draft` marker in the table row, and the list keeps its current ordering. No change to how badges or translations are edited.

## 3 · Markdown pipeline

`App\Support\ArticleMarkdown` — a wrapper around a configured `League\CommonMark\MarkdownConverter` (league/commonmark 2.8.2 is already installed and already used by `Str::markdown` on the Experience cards). Not `Str::markdown`, because the handoff §4 requires four output changes:

1. `<table>` wrapped in `<div class="blog-table">` — table overflow container.
2. Fenced code wrapped in `<div class="blog-code">` with a `<div class="blog-code-bar">` carrying the info string as the language label.
3. A paragraph containing only an image becomes `<figure><img><figcaption>` with the alt text as the caption.
4. External links get `rel="noopener"` (`ExternalLinkExtension`).

Implemented as custom node renderers registered on the `Environment` (plus `TableExtension` and `ExternalLinkExtension`), not as regex over the output HTML. Each renderer is independently unit-testable: markdown string in, HTML string out.

**Caching:** `Cache::rememberForever("article:{$id}:{$locale}:{$updatedAt}")`. The `updated_at` timestamp in the key makes edits self-invalidating, so nothing has to remember to flush.

**Syntax highlighting:** none. The CSS ships `.c` / `.k` / `.s` and the block looks finished without any of them. Adding a highlighter later means mapping it to those three classes.

## 4 · Dates

`App\Support\ArticleDate` — a small class with three static formatters, because Czech needs shapes Carbon's locale data does not give for free:

| Where | en | cs |
| --- | --- | --- |
| Article header | `18 March 2026` | `18. března 2026` |
| Rail (day) | `18` | `18.` |
| Rail (month + year) | `MAR 2026` | `3. 2026` |
| `<time datetime>` | `2026-03-18` | `2026-03-18` |

Czech never abbreviates the month in the rail (`bře` is nobody's word), so the Czech rail goes numeric. Both locales keep the day as the big gold figure. Unit-tested across both locales and a leading-zero month.

## 5 · Views and components

- `resources/views/blog.blade.php` — dock-hero, section head with count, optional filter line, `.blog-list`, end line / empty state.
- `resources/views/article.blade.php` — back rail, header, cover, prose, footer, read-next.
- `resources/views/components/portfolio/blog-row.blade.php` — one row. Props: `article`, `locale`, `lead` (bool), `archiveIndex`. Handles the lead variant, the no-thumbnail ghost numeral and the badge strip. Used by the listing **and** by Read next, unchanged. No second card component exists.
- `resources/views/components/portfolio/blog-badge.blade.php` — link-capable badge, `--bc` from `Badge::$color`, href to `/blog?badge=<slug>`.

## 6 · CSS

One new file, `resources/css/pages/blog.css`, added to `vite.config.js` inputs and loaded by both views through the layout's `:styles` prop (the Experience pattern — the one of the three loading patterns in `docs/design-audit-findings.md` we are standardising on).

It carries the handoff's `.blog-*` and `.art-*` rules **minus every `.blog-hero-*` rule**, plus the four page-local derived values (`--blog-ink`, `--blog-measure`, `--blog-hair`, `--blog-link-hover` / `--blog-code-fg` per theme).

**Two token additions to the `@theme` block in `app.css`** — the handoff CSS assumes them and currently falls back:
- `--font-mono: 'Fira Code', ui-monospace, monospace` — the rail, meta, code and footer type. Requires adding `Fira Code` 400/500 to the Google Fonts link in `portfolio-layout.blade.php`.
- `--r-pill: 999px` — badge and tag radius, currently written as a literal in several files.

No other token is added, redefined or aliased. Every colour, radius and border width the handoff names already exists in `app.css` with identical values.

**Badge light-theme fix (handoff §6):** `color-mix(in srgb, var(--bc) 62%, #1C1B17)` for text and `75%` for the border under `html.light`. Applied to `.blog-badge` **and** to the existing `.exp-badge` in `experience.css` — several vocabulary hues are unreadable on white today (`javascript #F7DF1E` ≈1.3:1). This fixes a live accessibility bug on `/experience`, not just the new page.

## 7 · Listing and filtering

`.blog-list` is one bordered container of hairline-divided rows; the whole row is the `<a>`. Grid `150px 1fr 300px`, `min-height:170px`; the newest post is `.blog-row--lead` (`150px 1fr 420px`, `264px`, 32px title, `Newest` flag). Rows with no `thumbnail_url` keep the column and fill it with the outlined archive numeral.

Filtering is a query param, not a bar:
- Badges everywhere are links to `/blog?badge=<slug>`.
- With the param set, one `.blog-filter` line renders above the listing: `Filtered by [hardware] Show all`.
- The section-head count reflects the filtered number; `Show all` clears the param.
- Unknown slug → empty state with the `Show all` reset, not a 404.

No filter bar, no scope toggle, no search, no sort control, no pagination. The Experience bar earns its space because Experience has scope, free text and a live count; a blog with a handful of posts a year does not. Revisit past ~25 posts and promote the Experience bar wholesale rather than inventing a third filter idiom.

## 8 · Article page

Order: back rail → header (date · reading time, `h1`, description as lede, badge links) → cover → prose → footer → Read next.

- Cover is a `2.4:1` band from `thumbnail_url`; when empty the element is **omitted entirely**, no substitute.
- `.blog-prose` is `grid-template-columns: 1fr min(54ch, 100%) 1fr`; text sits in column 2, `figure` / `.blog-code` / `.blog-table` span `1/-1`.
- Read next: two other published posts, newest sharing a badge first, filled up by date, rendered with `<x-portfolio.blog-row>`.
- Meta: `<title>` = `{header} — Blog`, description = the article's `description` field verbatim.
- One `h1` per page. Listing titles are `h3` under the `h2` section head. Ghost wordmark and numerals are `aria-hidden`.

## 9 · Navigation

Mobile nav keeps four icons per row:

- Row 2 becomes **Blog · Email · Instagram · X**.
- Row 3 becomes **LinkedIn · GitHub · Chess · (empty slot)**.
- The SPŠE-WP entry is deleted; Blog reuses its `safari.webp` icon.
- Lang: `proj1` removed, `nav5` added (`Blog` / `Blog`).

The footer nav column gains a Blog link.

## 10 · Copy and settings

- `resources/lang/{en,cs}/pages/blog.php` — hero tags, wordmark, section head, count (three Czech plural forms, like `home/experience.count_*`), filter line, end line, both empty states, back link, reading-time suffix, article footer, read-next head.
- `resources/lang/{en,cs}/layout/header.php` — `blog_title` / `blog_desc`, `article_*` fallbacks.
- Settings, seeded in `SettingSeeder` and registered in `⚡site-content`: `blog_hero_suptitle`, `blog_hero_title`, `blog_hero_roles`. Same split as Experience — editable hero voice, static structural copy.

`SettingSeeder::run()` deletes any key not in its array, so the three new keys must land there or the next seed wipes them.

## 11 · Testing

- **Unit** — `ArticleMarkdown` (table wrap, code wrap + language bar, figure/figcaption, `rel="noopener"`, plain paragraph untouched), `ArticleDate` (both locales, single-digit month), `readingTime` rounding.
- **Feature** — index lists published only; drafts and future posts are absent and 404 on detail; badge filter narrows the list and the count; unknown badge shows the filtered empty state; zero posts shows the other empty state; lead row only on the newest; archive numerals count from the oldest; article renders prose and badges; cover omitted without `thumbnail_url`; read-next picks badge-mates first; both locales; hero settings render.
- **Browser** — light-theme badge contrast, row hover/focus ring, responsive stacking at 1200 and 560, no console errors on both pages.

## 12 · Out of scope

Seeded blog content (the handoff's six filler posts are explicitly "replace them, don't ship them"), a home-page blog teaser, load-more pagination, syntax highlighting, RSS, and the hero photo asset itself (config points at the existing portrait until a blog crop exists).
