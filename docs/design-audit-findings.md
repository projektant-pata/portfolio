# Design/UI audit (krok 2 z docs/supr-cupr-upgrade-plan.md)

Stav: 2026-07-16. Nic neopraveno — jen nálezy + návrh struktury komponent. Pokrytí: všechny Blade views (`resources/views/`), page CSS (`resources/css/`), `resources/js/app.js`, `vite.config.js`.

## A. Design tokens / theming — ✅ OPRAVENO 2026-07-16

Výsledný stav: jediný zdroj tokenů v `@theme` (`app.css`), `--c-*` jsou pouhé aliasy, light theme přepisuje tytéž `--color-*` v `html:not(.dark)`. Jeden theme mechanismus pro celou app: cookie `theme` (`dark|light|system`, default `dark`) → třída `.dark` na `<html>`, aplikovaná před prvním paintem v `partials/theme.blade.php` (include v public i admin/auth layoutech). `@fluxAppearance`/localStorage a `.light-theme` jsou pryč; appearance settings stránka píše tutéž cookie přes `window.setThemePreference()`. Font: Inter (Google Fonts) všude, jedna proměnná `--font-sans`. Pokrytí: `tests/Feature/ThemeTest.php`.

Původní nálezy:

1. **Dva paralelní token systémy** — `resources/css/app.css`
   `@theme` (ř. 11–39) definuje `--color-primary` atd. pro Tailwind, `:root` (ř. 101–162) definuje tytéž hodnoty znovu jako `--c-primary` atd. Hodnoty se musí udržovat 2× a už driftují: `.light-theme` (ř. 169) přepisuje jen `--c-*`, takže Tailwind tokeny (`--color-primary` = #FACC15) zůstávají v light módu dark-theme žluté.
   Návrh: jediný zdroj v `@theme`, `--c-*` nechat jen jako aliasy (`--c-primary: var(--color-primary)`), light-theme overrides přes tytéž proměnné.

2. **Dva nekompatibilní dark-mode mechanismy**
   Admin: `.dark` natvrdo na `<html>` (`layouts/app/sidebar.blade.php:2`) + `@custom-variant dark` — admin je vždy dark, cookie `theme` se ignoruje. Public: třída `.light-theme` na `<html>` přes JS + cookie (`partials/head.blade.php` FOUC skript je ale jen v admin head; public layout ho nemá → public light-theme se aplikuje až po načtení `app.js` = flash tmavého). Žádné `prefers-color-scheme`.
   Návrh: jeden mechanismus (`.dark`/`.light-theme` sjednotit), FOUC skript do `portfolio-layout.blade.php`.

3. **Fonty: 2 poskytovatelé, 3 proměnné, admin fallbackuje** — `portfolio-layout.blade.php:17-19` načítá Inter z fonts.googleapis.com; `partials/head.blade.php:12-13` (admin) načítá Instrument Sans z fonts.bunny.net. Manage stránky ale vynucují `font-family: var(--font-body)` = Inter, který v admin head vůbec načtený není → admin reálně jede na systémovém fallbacku. Tokeny `--font-sans` vs `--font-portfolio` vs `--font-body` (poslední dva jsou totéž).
   Návrh: jeden font, jeden provider, jedna proměnná.

4. **`--c-surface-raised` neexistuje** — používá se v 5 manage stránkách (`style="background: var(--c-surface-raised, rgba(0,0,0,0.08))"`), ale nikde není definovaná → vždy jede fallback. Definovat token, nebo fallback-hack smazat.
   Opraveno jako `--c-surface-sunken` — jediné použití je track jazykových tabů, kde aktivní tab má `--c-surface`, tedy vrstva pod povrchem, ne nad ním.

5. **Formát barev badge nejednotný** (= nález #15 z kroku 1) — manage UI (`⚡badges.blade.php:214-223`) nabízí Flux názvy (red/blue/…), veřejné views (`experience.blade.php:21,81`, `projects.blade.php:32`) čekají hex v `--badge-color`, manage tabulka (`⚡badges.blade.php:141`) čeká Flux název, seeder ukládá hex. Jedna hodnota nemůže fungovat všude.
   Návrh: ukládat hex (public je hlavní spotřebitel); v manage tabulce místo `<flux:badge color=...>` vykreslit swatch se `style="--badge-color"`; v manage formu color-picker/select s hex hodnotami.

6. **`--c-bg` == `--c-surface`** (#1C1B17) v dark módu — karty se od pozadí liší jen borderem; light theme má hierarchii (parchment/white). Pokud záměr, zdokumentovat; jinak surface v dark mírně zesvětlit.
   Opraveno: dark `--color-portfolio-surface` = #232219.

## B. Architektura CSS — ✅ OPRAVENO 2026-07-17

Stav: page CSS se načítá jednotně přes `:styles` prop (welcome/about-me/projects/experience), `@import`y z konce `app.css` jsou pryč → `projects.css` už neuniká na homepage, takže runtime kolize `.projects-row` (B7) neexistuje (zůstává jen duplicitní název třídy ve dvou page-scoped souborech — kosmetika, řeší se s D24). `dashboard.css` zůstává napevno v admin `head.blade.php` (admin nepoužívá `portfolio-layout`, `:styles` tam neplatí — OK). B8 hardcoded `rgba(96,84,67,0.15)` nahrazeno `color-mix(in srgb, var(--c-primary-fade) 15%, transparent)` v nové třídě `.manage-link-box` (`dashboard.css`).

Původní nálezy:

7. **Tři různé způsoby načítání page CSS**:
   a) `index.css`, `experience.css` — přes `:styles` prop layoutu → `@vite` (+ zápis ve `vite.config.js`);
   b) `about-me.css`, `projects.css` — `@import` na **konci** `app.css` (ř. 690–691; `@import` po jiných pravidlech je nevalidní standardní CSS, bundler to spolkne) → načítají se globálně na každé stránce;
   c) `dashboard.css` — natvrdo v `partials/head.blade.php:33` pro celý admin.
   Důsledek (b): `.projects-row` je definovaná v `index.css` I `projects.css` s odlišnými hodnotami (margin-bottom 3.125rem vs 3rem, jen projects verze má `--reverse` a placeholder) a na homepage se aplikují obě — vítěz závisí na pořadí `<link>` tagů. Křehké.
   Návrh: všechno page CSS jednotně přes `:styles` prop (a do `vite.config.js`), `@import`y z `app.css` pryč, kolizní `.projects-row` přejmenovat per-page (viz D24).

8. **Duplicitní hardcoded rgba** — `rgba(96,84,67,0.15)` v 5 manage stránkách = ručně rozepsaná `--c-primary-fade` (#605443). Při změně tokenu se nezmění. → `color-mix(in srgb, var(--c-primary-fade) 15%, transparent)`.

## C. Inline styly v manage stránkách — ✅ OPRAVENO 2026-07-17

Stav: všechny opakované statické inline styly v 5 `manage/⚡*.blade.php` + `dashboard.blade.php` převedeny na sdílené třídy v `dashboard.css` (`.manage-page`, `.manage-title`, `.manage-subtitle`, `.manage-empty`, `.manage-note`, `.manage-section-label`, `.manage-drag-handle`, `.manage-link-box`, `.locale-tabs`, `.locale-tab`, `.locale-tab--active`). Locale-tab aktivní stav se přepíná přes `:class="… 'locale-tab--active' …"` místo Alpine `:style`. Jediný zbylý inline `style=""` je dynamický badge swatch (`background: {{ $badge->color }}`) — správně zůstává inline. Komponentní extrakce (E/F) je stále TODO; třídy jsou mezikrok, který duplicitu markupu neruší, jen ji odstyluje.

Původní nálezy:

9. **Masivní `style=""` místo utilit** — všech 5 `pages/manage/⚡*.blade.php` + `dashboard.blade.php`: root div (`style="font-family...; color..."`), h1 (`font-size:2rem;font-weight:600`), podtitulek, empty-state `<p>`, drag-handle cell (`cursor:grab...`), link-box. Tentýž inline blok zkopírovaný 5–6×. Tailwind je přitom k dispozici.
   Návrh: převést na utility (`text-3xl font-semibold`, `text-[var(--c-muted)]`…) nebo pár tříd v `dashboard.css` vedle existujících `.btn-gold*`. Většina zmizí extrakcí komponent (sekce F).

## D. Konzistence pojmenování — 🟡 ČÁSTEČNĚ 2026-07-17

Stav D10 (id→třída pro styling): hotovo pro **home** (`index.css` + `welcome.blade.php`) a **about-me** (`about-me.css` + `about-me.blade.php`) — `#hero-page`, `#underh1`, `#stats-cards`, `#work`, `#work-top`, `#work-bot`, `#work-bot-line`, `#about-me-content`, `#about-me-stats-cards` převedeny na třídy (1:1 přejmenování). Ověřeno staticky: žádné z těchto id není JS hook ani anchor target a nekoliduje s třídou → bez dopadu na specificitu. JS-hook id (`work-top-btn-*`, `work-bot-content-*`) ponechána.

**ODLOŽENO (nejde ověřit bez běžící appky — Docker v tomto prostředí nefunguje, host PHP nemá pdo_sqlite):**
- D10 pro `experience.css`/`experience.blade.php` — `#exp-grid`, `#exp-col-left/right`, `#exp-search*` jsou **JS hooky** (`getElementById` v inline `<script>`) + `.open` compound selektory; přepis vyžaduje i úpravu JS → nutná browser verifikace.
- D10 stavové přepínače `style="display:none"` → `.hidden` (`welcome.blade.php:71`, `experience.blade.php:42`) — `app.js` nastavuje `.style.display` napřímo, konverze vyžaduje i změnu toggle logiky → browser verifikace.
- D11 (přejmenování sloupců `header`/`title`, `img_url`/`image_path`/`thumbnail_url`) a D12 (sjednocení `alt` na překládaný tvar) — **DB migrace** dotýkající se modelů, seederů, testů, veřejných views → nutný běh test suite.

Původní nálezy:

10. **CSS selektory bez systému** — mix idček pro styling (`#hero-page`, `#work-bot-content-life`, `#exp-grid`) a tříd; prefixy per-page nejednotné (`.exp-*`, `.projects-row`, `.work-*`, `.stats-cards-card`, `.about-me-card`). `#work-bot-content-life/work` + `style="display:none"` inline přepínané z JS.
    Návrh: třídy místo idček pro styling, jednotný block-prefix per page (`.home-*`, `.exp-*`, `.proj-*`), stav přes `.hidden`/`.active` třídy místo inline `display`.

11. **Názvosloví polí driftuje napříč doménami** — `header` (Project, Article) vs `title` (Experience); `img_url` (Project, Link) vs `image_path` (Experience) vs `thumbnail_url` (Article). UI labely to kopírují → v manage stránkách stejná věc pojmenovaná 3 způsoby. Datová věc, ale projevuje se v UI; sjednotit minimálně labely, ideálně (migrací) i sloupce.

12. **Alt text linků: 2 různé tvary** — Experience ukládá linky jako JSON pole s **plain-string** `alt`, Project má `Link` model s **překládaným** `alt` `{'en','cs'}`. Blade to musí řešit runtime typechecky (`experience.blade.php:96` — `is_array($link['alt'] ?? null) ? ...`). Sjednotit na překládaný tvar.

## E. Duplicitní markup (kandidáti na komponenty)

13. **Přepínač jazykových tabů** — identický ~20řádkový Alpine blok (`x-data="{ locale: 'en' }"` + 2 buttony s inline styly) v **5** manage stránkách. Největší duplicita v repu.
14. **Hlavička stránky** (h1 + podtitulek + „Add X" tlačítko) — stejný blok ×5.
15. **Delete-confirm modál** — identický ×5 (liší se jen slovo v nadpisu).
16. **Drag-handle SVG** (grip dots) — inline SVG zkopírované ×3 (experiences, projects, articles). Lucide má `grip-vertical`: `php artisan flux:icon grip-vertical` → `<flux:icon.grip-vertical>`.
17. **Search input** — stejný řádek ×5.
18. **Badge-picker repeater** (select + add/remove) — markup ×3 + metody `addBadge`/`removeBadge` zkopírované ×3.
19. **Link repeater** — ×2 (experiences, projects), skoro identický (liší se tvarem alt, viz D12).
20. **Empty-state řádek tabulky** — ×5.
21. **Footer modálu** (Cancel + submit) — ×5.
22. **Stats karty** — první 4 karty ve `welcome.blade.php:18-35` a `about-me.blade.php:33-49` jsou copy-paste totožné (stejné lang klíče). → komponenta + loop.
23. **Sociální/nav odkazy definované 2×** — tytéž URL (mailto, instagram, x, linkedin, github, chess) natvrdo v `portfolio-footer.blade.php` i `mobile-nav.blade.php`. Změna = 2 místa. → `config/portfolio.php` (pole odkazů) + komponenta.
24. **Projects na homepage vs projects stránka** — `welcome.blade.php:92-123` má 2 projekty natvrdo přes lang klíče (`home/projects.spsehub_*`), zatímco `/projects` renderuje z DB stejným vizuálem. Dvojí zdroj pravdy + kolize `.projects-row` (viz B7). → homepage brát top-N z DB (`Project::orderBy('sort_order')->take(2)`), jedna komponenta `project-row`.
25. **Work/life obsah na homepage 2×** — `welcome.blade.php:53-87`: dva takřka identické `@foreach` bloky (life/work), liší se jen kolekcí. → jeden loop / komponenta řádku.

## F. Návrh struktury komponent

```
resources/views/components/
├── manage/
│   ├── page-header.blade.php    {{-- @props(['title','subtitle']) + slot pro akci --}}
│   ├── locale-tabs.blade.php    {{-- Alpine locale switch; sloty: en, cs (řeší E13) --}}
│   ├── delete-modal.blade.php   {{-- @props(['entity']) → "Delete {entity}?" (E15) --}}
│   ├── search-input.blade.php   {{-- wire:model.live.debounce="search" (E17) --}}
│   ├── empty-row.blade.php      {{-- @props(['colspan','message']) (E20) --}}
│   ├── badge-picker.blade.php   {{-- repeater nad selectedBadgeIds (E18) --}}
│   ├── link-repeater.blade.php  {{-- po sjednocení alt tvaru (E19, D12) --}}
│   └── modal-footer.blade.php   {{-- Cancel + submit (E21) --}}
├── portfolio/
│   ├── stats-card.blade.php     {{-- (E22); karty jako pole v lang/controlleru → loop --}}
│   ├── project-row.blade.php    {{-- sdílí welcome + projects (E24) --}}
│   ├── experience-row.blade.php {{-- řádek work/life timeline (E25) --}}
│   └── social-links.blade.php   {{-- data z config/portfolio.php (E23) --}}
resources/views/flux/icon/
│   └── grip-vertical.blade.php  {{-- php artisan flux:icon grip-vertical (E16) --}}
```

PHP strana (mimo scope designu, ale souvisí): CRUD logika manage stránek (search/reorder/openCreate/openEdit/confirmDelete/delete/resetForm) je ~80 % identická ×5 → kandidát na trait `app/Concerns/ManagesCrudModals.php`; `getTranslation()` helper zkopírovaný v 5 modelech → trait `HasTranslations` (už zachyceno v memory z kroku 1).

Design tokens: po sjednocení (A1) doplnit chybějící (`--c-surface-raised`), badge barvy na hex (A5).

## G. Responzivita

26. **Modální formuláře: `grid grid-cols-2` bez mobilní varianty** — všechny manage formy (`grid grid-cols-2 gap-4`); na malém displeji zůstávají 2 sloupce nacpané. → `grid-cols-1 md:grid-cols-2`.
27. **Fixní rozměry** — `.projects-row` fixní výška 360/300 px (dlouhý popis přeteče bez ošetření), `.projects-row > img` fixní 600 px; `#mobile-nav` fixní 370×820 px — na desktopu s viewportem < ~840 px výšky přeteče sticky sidebar (100vh, overflow visible); na ≤576 px řešeno `scale: 0.8` hackem.
28. **Dva breakpoint systémy** — public CSS ručně 1440/992/576 px, manage stránky Tailwind `md`/`lg` (768/1024). Přechod public→admin nemá společnou mřížku. Aspoň zdokumentovat, ideálně public breakpointy převést na Tailwind hodnoty.

## H. Přístupnost (a11y)

29. **`<h2>projektant-pata</h2>` PŘED `<footer>`** — `portfolio-footer.blade.php:1`. Záměr: dekorativní nadpis stejně jako na ostatních sekcích, umístění mimo `<footer>` je vědomé (konzistence napříč stránkou). Ne bug — ale viz #30 pro SEO/a11y dopad.
30. **Rozbitá hierarchie nadpisů kvůli dekorativním `h2`** — obří `h2` watermarky na každé sekci (záměr, ne chyba) skutečně nesou tag `<h2>`, takže search engine i screen reader outline je zaplaven opakovaným dekorativním textem místo skutečné struktury obsahu; `h3` ve stats kartách obsahuje jen číslo, `h1 → h4` skok v hero. **SEO dopad**: Google používá heading outline k pochopení struktury stránky — dekorativní watermarky v `h2` ředí relevanci skutečných nadpisů sekcí a matou strukturovaná data. **Návrh**: dekorativní watermark texty přepsat na `<p aria-hidden="true">` / `<div>` (vizuálně beze změny), a každé sekci dát *skutečný* sémantický nadpis (může být `sr-only`/vizuálně skrytý pokud dekorativní text sekci dostatečně popisuje vizuálně) tak, aby `h1 → h2 → h3` hierarchie odpovídala skutečnému obsahu. Čísla statistik nejsou nadpisy.
31. **Icon-only tlačítka bez popisku** — pencil/trash/x-mark `<flux:button icon=... />` ve všech manage tabulkách a repeaterech; screen reader přečte nic. → doplň `aria-label`/`tooltip`.
32. **Work/life přepínač jsou `<div>`y** — `welcome.blade.php:43-48` (`.work-top-btn`) nejsou fokusovatelné, bez klávesnice/role. Experience stránka to samé řeší správně `<button>`. → `<button type="button">` + `aria-pressed`.
33. **Mrtvé `href="#"` odkazy v mobile-nav** — messages/music/weather (theme toggle!) jsou `<a href="#">`; theme toggle má být `<button>`; dekorativní ikony nemají být fokusovatelné. Footer `nav5` také `href="#"`.
34. **`target="_blank"` bez `rel="noopener"`** — `welcome.blade.php` (projekt linky), celý `portfolio-footer.blade.php`, celý `mobile-nav.blade.php` (rozšíření nálezu #16 z kroku 1; `experience.blade.php` a `projects.blade.php` to mají správně).
35. **Chyby validace ve skrytém jazykovém tabu** — manage formy: submit s chybou jen v CS poli, zatímco je zobrazen EN tab → uživatel nevidí žádnou chybu a submit „nefunguje". → indikátor chyby na tab tlačítku (Alpine: červená tečka když `$wire.getErrors()` obsahuje daný prefix), nebo auto-přepnout na tab s chybou.
36. **Globální transition + z-index na všem** — `app.css:227-242`: `.portfolio-page *` má `transition: var(--t-base)` (0.5s all!) a `position:relative; z-index:1`. Výkon (transition na každém elementu při přepnutí theme), chybí `prefers-reduced-motion`, z-index kontext na všem ztěžuje vrstvení. → transition jen na `background-color/color/border-color` u vybraných elementů + `@media (prefers-reduced-motion: reduce)`.
37. **Kontrast řízený libovolnou badge barvou** — `.exp-filter--badge.active` = text `--c-bg` na pozadí `--badge-color` (uživatelem zadaný hex); tmavý badge → nečitelné. Body text `--fw-light: 200` na 1rem je na hraně čitelnosti obecně.
38. **Duplicitní `wire:model` na jednom inputu** (= nález #14 z kroku 1) — ×3: `⚡badges.blade.php:190`, `⚡articles.blade.php:258`, `⚡projects.blade.php:300`. Nechat jen `.live.debounce`.

## Poznámky (ne-nálezy)

- Flux free edition nemá `tabs` komponentu — locale-tabs (F) správně zůstane vlastní Blade+Alpine komponenta.
- `resources/views/flux/icon/*` a `flux/navlist/group.blade.php` = korektní způsob publikace vlastních Flux ikon; grip-vertical přidat stejně.
- Settings/auth stránky (`pages/settings/`, `pages/auth/`, `layouts/auth/*`) jsou téměř čistý starter kit — konzistentní, bez zásahů netřeba.
- `.light-theme .exp-card--special` shimmer override v `app.css` je promyšlený — jen ukázka, že tokeny fungují, když se používají.
