# /about-me — remaining handoff sections

Source: `new sections.zip` → `design_handoff_about/` (README + `about-standalone.html` + `about.css`).
The handoff describes eight sections. Six already shipped in `b4f2a0e` / `2783e7a`.

## State vs handoff

| # | Section | Handoff class | State |
|---|---------|---------------|-------|
| 1 | Intro | `.ab-intro` | **dropped on purpose** in `b4f2a0e` ("drop the intro frame"). Restore or not — open. |
| 2 | Toolbox | `.ab-tools` | shipped, but diverged: bands + three-frequency legend instead of the handoff's 2-column `using`/`experience` rows. Work in flight, uncommitted. |
| 3 | The short version | `.ab-path` | shipped (`.about-me-path`) |
| 4 | Stats | `.ab-bento` | shipped (`.about-me-bento`) |
| 5 | Off the screen | `.ab-off` | **gap.** Today it is `.about-me-beyond`, a compact 3-cell grid. Handoff wants four long alternating photo/text chapters with chapter number, optional pull line, a fact row, and a closing paragraph. |
| 6 | Things I show up for | `.ab-showups` | shipped (`.about-me-showups`) |
| 7 | References | `.tcar` | shipped (carousel reused from home) |
| 8 | Contact | `.ab-cta` | shipped (`.about-me-cta`) |

So "new sections" = §5 rebuilt, plus §1 if it comes back.

## Plan — §5 Off the screen

1. `php artisan make:migration add_chapter_fields_to_interests_table`
   - `label` jsonb — chapter label (`Chess`, `Homelab`, …), `{en,cs}`
   - `pull` jsonb — optional pull line, `{en,cs}`
   - `facts` jsonb — `{"en": [{"dt": …, "dd": …}], "cs": [...]}`
   - `body` now holds two paragraphs (split on a blank line)
   - Chapter number derives from `sort_order`; it is not a column.
2. `app/Models/Interest.php` — add the new casts, plus `paragraphs(string $locale): array` and `facts(string $locale): array`.
3. New Setting key `about_off_end` (the section's closing paragraph) → `SettingSeeder` + the `⚡site-content` manage page.
4. `resources/views/about-me.blade.php` §4 — replace the `.about-me-beyond` cells with `.about-me-chapter`; `$loop->even` adds the `--flip` modifier; media is `x-portfolio.image-slot` at `aspect-ratio: 4/3`.
5. `resources/css/pages/about-me.css` — drop `.about-me-beyond*`, add `.about-me-chapter*`: no card, no frame, 1px 50%-fade top hairline between chapters, `3.2rem` vertical padding, copy column `max-width: 54ch`. Responsive: ≤1100px single column with the photo always first (`--flip` reset to `order: 0`); ≤760px tightened.
6. `⚡interests.blade.php` — fields for `label`, `pull`, and a repeater for `facts`.
7. `InterestSeeder` — four chapters: chess, homelab, gym/sport, travel.
8. Tests — extend `AboutMePageTest` and `AboutContentTest` (chapter number, flip on 02/04, pull line on 01/03 only, fact rows, closing paragraph). Run:
   `docker exec portfolio-app-1 php artisan test --compact --filter=About`
9. `docker exec portfolio-app-1 vendor/bin/pint --dirty --format agent`

## Session estimate

Two sessions if Intro stays dropped, three if it returns.

| # | Scope | Blocked by |
|---|-------|------------|
| 0 (half) | finish + commit the toolbox work in flight, run tests | — |
| 1 | migration + model + `about_off_end` Setting + `⚡interests` fields + seeder | copy fact-check |
| 2 | blade chapters + `about-me.css` + responsive + tests + pint | photos (placeholders ship fine) |
| 3 (only if Intro returns) | `.ab-intro` frame + fact strip + portrait + admin wiring | portrait `900×1200` |

Sessions 1 and 2 can fuse if context holds — the backend is small, the CSS is the bulk. Splitting is safer.

## Open decisions

1. Does §1 Intro come back? The handoff calls the missing portrait the page's single biggest credibility gap.
2. Ship §5 on `image-slot` placeholders, or wait for the four `1200×900` photos?
3. Commit the toolbox work before starting, or carry it?

## Content caveats (from the handoff README)

- All chapter copy is written-to-plausible, **not verified**. Every number needs fact-checking before it ships: peak rating `1684`, homelab node count, countries visited, gym frequency.
- Assets still missing: portrait `900×1200`, plus chess / homelab rack / gym / travel at `1200×900` each.
- Handoff HTML and `_deps/` are design references only — do not port them; rebuild with the project's own tokens and components.
