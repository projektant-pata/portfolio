# Typography System

All typography tokens live in `app.css` under `:root` (section 1 — Design Tokens).
The font family is also declared in the `@theme` block for Tailwind compatibility.

---

## Font Families

| Token          | Stack                                    | Used for              |
|----------------|------------------------------------------|-----------------------|
| `--font-body`  | `'Inter'`, ui-sans-serif, system-ui      | All `.portfolio-page` elements |
| `--font-sans`  | `'Instrument Sans'`, ui-sans-serif       | Tailwind default (admin / Flux UI) |
| `--font-portfolio` | `'Inter'`, ui-sans-serif             | Tailwind alias (same as body) |

`.portfolio-page` and all its descendants inherit `--font-body` via the base reset.

---

## Font Sizes

All sizes are in `rem` so they respect the user's browser base font preference.
Headings (`h1`–`h4`) are **fluid** via `clamp(min, base + vw, max)` so they scale
down on narrow viewports instead of forcing horizontal overflow. The `max` value
of each clamp equals the original desktop size in the table below.

| Token       | Clamp (min → max)            | Desktop max | Role                          |
|-------------|------------------------------|-------------|-------------------------------|
| `--fs-mini` | `0.85rem` (fixed)            | ~14 px      | `.mini` labels (clock, app names, years) |
| `--fs-sm`   | `0.875rem` (fixed)           | ~14 px      | Small body text               |
| `--fs-base` | `1rem` (fixed)               | ~16 px      | Body paragraphs, hero suptitle |
| `--fs-h4`   | `1.3rem` → `1.6rem`          | ~26 px      | Subheadings, hero subtitle (`#underh1`), tools card label |
| `--fs-h3`   | `1.9rem` → `2.56rem`         | ~41 px      | Section titles                |
| `--fs-h1`   | `2.6rem` → `4.1rem`          | ~66 px      | Hero title                    |
| `--fs-h2`   | `2.6rem` → `6.56rem`         | ~105 px     | Giant watermark / section label |

*px equivalent assumes 16 px browser root.

> **Why fluid:** with fixed sizes, the long unbreakable word `projektant-pata`
> at 66 px (`--fs-h1`) forced the content column to a ~496 px min-width, which
> overflowed every viewport below ~520 px. The clamp fixes this at the source;
> `h1`/`h2` also carry `overflow-wrap: anywhere` as a safety net.

---

## Font Weights

| Token           | Value | Role                              |
|-----------------|-------|-----------------------------------|
| `--fw-light`    | `200` | Body paragraphs, hero subtitle    |
| `--fw-regular`  | `400` | Headings h3/h4, suptitle, review text, mobile nav text |
| `--fw-semibold` | `600` | Gold `<span>` inside `#underh1`   |
| `--fw-bold`     | `700` | Hero `h1`                         |

---

## Heading Styles

### `h1` — Hero title

```css
font-size:      var(--fs-h1);       /* 4.1rem */
font-weight:    var(--fw-bold);     /* 700 */
line-height:    0.95;
letter-spacing: -0.02em;
margin-bottom:  0;
```

`h1 span` (the accent word) is **outlined** in the display font (`--font-display`,
Space Grotesk): `-webkit-text-stroke: 2px var(--c-primary)` with a faint interior
fill `color: color-mix(in srgb, var(--c-primary) 16%, transparent)` — edges plus a
hint of color inside, not a hollow frame. See `docs/frontend-headings-and-mobile-nav.md`.

---

### `h2` — Watermark / section label

> **History:** this section originally documented a global `.portfolio-page h2`
> rule that gave every `h2` on the site the giant outlined-watermark treatment,
> plus a light-mode override (`html:not(.dark) .portfolio-page h2`) that filled
> it solid. The `feat/section-head-rollout` branch moved every public section
> heading onto `<x-portfolio.section-head>` (`resources/css/components/section-head.css`),
> which has its own, much smaller `h2` rule — see that file's `.sechead .sechead-row h2`.
> With no heading left relying on the global watermark look, both the rule and
> its light-mode override were deleted from `app.css`.

The watermark treatment itself survives in exactly one place: the
`.portfolio-footer-watermark` heading (`resources/views/components/portfolio-footer.blade.php`),
which now carries the declarations directly instead of inheriting them:

```css
font-size:      var(--fs-h2);   /* clamp → 6.56rem max */
font-weight:    500;
font-family:    var(--font-display);          /* Space Grotesk */
color:          color-mix(in srgb, var(--c-watermark) 12%, transparent);
-webkit-text-stroke: 1px var(--c-watermark);  /* outlined watermark */
text-align:     center;
margin-bottom:  -0.45em;        /* overlap scales with the fluid font size */
```

Dark mode is **outlined** (as above); a light-mode override
(`html:not(.dark) .portfolio-footer-watermark`, mirroring the old global reset)
fills it solid with `--c-watermark` because a thin stroke is too faint on the
light parchment background. Change the display look via the `--font-display`
token.

---

### `h3` — Section title

```css
font-size:   var(--fs-h3);       /* 2.56rem */
font-weight: var(--fw-regular);  /* 400 */
```

`h3 span` highlights with `color: var(--c-primary)`.

---

### `h4` — Subheading

```css
font-size:   var(--fs-h4);       /* 1.6rem */
font-weight: var(--fw-regular);  /* 400 */
```

`h4 span` highlights with `color: var(--c-primary)`.

---

## Body & Label Styles

### `p` — Body paragraph

```css
font-size:   var(--fs-base);    /* 1rem */
font-weight: var(--fw-light);   /* 200 */
```

`p span` highlights with `color: var(--c-primary)`.

---

### `.mini` — Micro label

```css
font-size:   var(--fs-mini);    /* 0.625rem */
font-weight: var(--fw-light);   /* 200 */
```

Used for app names below icons in the phone nav, work-row years, and clock text.
Both properties are marked `!important` to override inherited weights.

---

### Hero suptitle (`.hero-suptitle`)

```css
font-size:      var(--fs-base);     /* 1rem */
font-weight:    var(--fw-regular);  /* 400 */
color:          var(--c-muted);     /* #a3a3a3 / #6b7280 light */
text-transform: uppercase;
letter-spacing: 0.08em;
```

---

### Hero subtitle (`#underh1`)

```css
font-size:      var(--fs-h4);       /* 1.6rem */
font-weight:    var(--fw-light);    /* 200 */
color:          var(--c-muted);
letter-spacing: 0.04em;
line-height:    1.1;
```

Its `span` uses `--fw-semibold` (600) and `--c-primary` for the gold accent word.

---

## Muted Color (`--c-muted`)

Used exclusively for secondary / supporting text:

| Theme        | Value     |
|--------------|-----------|
| Dark (default) | `#a3a3a3` |
| Light (`.light-theme`) | `#6b7280` |

Applied to: hero suptitle, hero subtitle, and any text that should recede.

---

## Phone Nav Typography Override

All `p` inside `#mobile-nav` are hard-overridden:

```css
color:       #fff !important;
font-weight: var(--fw-regular) !important;  /* 400 */
font-size:   0.75rem !important;            /* 12px */
```

`.mini` inside `.mobile-nav-app` also forces `--fw-regular` (the global `.mini` default is light).
