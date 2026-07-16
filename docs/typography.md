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
Fluid `clamp()` is not used — sizes are fixed tokens.

| Token       | Value      | px equiv* | Role                          |
|-------------|------------|-----------|-------------------------------|
| `--fs-mini` | `0.625rem` | ~10 px    | `.mini` labels (clock, app names, years) |
| `--fs-sm`   | `0.875rem` | ~14 px    | Small body text               |
| `--fs-base` | `1rem`     | ~16 px    | Body paragraphs, hero suptitle |
| `--fs-h4`   | `1.6rem`   | ~26 px    | Subheadings, hero subtitle (`#underh1`), tools card label |
| `--fs-h3`   | `2.56rem`  | ~41 px    | Section titles                |
| `--fs-h1`   | `4.1rem`   | ~66 px    | Hero title                    |
| `--fs-h2`   | `6.56rem`  | ~105 px   | Giant watermark / section label (outlined) |

*px equivalent assumes 16 px browser root.

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

`h1 span` renders outlined text: `color: transparent`, `-webkit-text-stroke: 2px var(--c-primary)`.

---

### `h2` — Watermark / section label

```css
font-size:      var(--fs-h2);   /* 6.56rem */
font-weight:    300;
font-family:    sans-serif;
color:          transparent;
-webkit-text-stroke: 1px var(--c-primary-lt);
text-align:     center;
margin-bottom:  -50px;          /* overlaps the content below */
```

In `.light-theme`, `h2` is filled with `--c-primary-lt` instead of outlined.

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
