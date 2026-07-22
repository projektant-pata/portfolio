# Portfolio — vylepšení (PC verze) + rotující tagline + admin

**Projekt:** `/data/backups/pred_reklamaci/Projects/Mine/portfolio-2`
**Datum:** 2026-07-21 · Laravel 13 · Livewire 4 (Volt) · Flux UI free · Tailwind v4 · PostgreSQL · Docker

---

## Context

Bilingvní (en/cs) portfolio. Design systém je čistý a centralizovaný (tokeny v `@theme`/`:root` v `resources/css/app.css`), ale vizuálně je stránka **animačně chudá** (jediný keyframe v celé appce), hero tagline `Full-stack developer` je **statický**, a **admin je z půlky nedodělaný** (dashboard je jen pozdrav, půlka obsahu žije v `lang/` souborech bez UI). Cíl: udělat desktop verzi profesionálnější a „živější", dokončit admin logiku, a splnit konkrétní přání — **měnící se nápis pod jménem** (Full-stack developer → Chess player → …), který krásně navazuje na už existující live chess.com ELO a GitHub integraci v `resources/js/app.js`.

Tento dokument je **specifikace návrhů**, seřazená podle priority. Otevřené otázky jsou vypsané v sekci [Rozhodnutí k potvrzení](#rozhodnutí-k-potvrzení-před-implementací) — nejsou blokující, mám u každé doporučení.

### Jak se v repu pracuje (gotchas — přečíst před editací)
- **Assety jsou pre-buildované**, žádný vite dev server neběží. Po JAKÉKOLI změně CSS/JS: `npm run build` na hostu (kontejner nemá Node). Jinak se změna neprojeví.
- PHP/artisan přes Docker: `docker exec portfolio-app-1 php artisan …`. DB je `portfolio-db-1` (Postgres 17), web `http://localhost:8008` (`portfolio-nginx-1`).
- Po PHP editacích: `docker exec portfolio-app-1 vendor/bin/pint --dirty --format agent`.
- Testy: `docker exec portfolio-app-1 php artisan test --compact` (SQLite in-memory — pozor na PG-only SQL).
- Všechny portfolio heading styly jsou **globální**, scoped na `.portfolio-page`. Dvě témata: dark = default (`.dark` na `<html>`), light = `html:not(.dark)`. `h2` má vlastní light override.
- **Nikdy `display:none` na předka fixed/absolute prvku** (viz historie mobilní nav — používej `display:contents`).
- Barvy/velikosti/přechody hákovat na existující tokeny (`--c-*`, `--fs-*`, `--t-*`, `--r-*`), ne hardcode.

---

## PRIORITA 1 — Rotující/„typewriter" tagline pod jménem (headline přání)

**Kde:** `resources/views/welcome.blade.php:8` — dnes `<h4 class="underh1">{!! __('home/hero.hero_subtitle') !!}</h4>`, kde `hero_subtitle = '<span>Full-stack</span> developer'` (`resources/lang/{en,cs}/home/hero.php`). Žádná typing JS zatím neexistuje → čistý greenfield, nic nekoliduje.

**Návrh:** slovo/fráze za jménem se cyklicky mění s typewriter efektem (píše → maže → další), s blikajícím caretem, gold accent zůstává. Role tahají tématicky na existující integrace (chess, github).

**Role (bilingvně, v lang souboru — ne hardcode v blade):** přidat pole do `resources/lang/{en,cs}/home/hero.php`:
```php
// en
'hero_roles' => ['Full-stack developer', 'Chess player', 'Spring Boot engineer', 'Laravel craftsman', 'Problem solver'],
// cs
'hero_roles' => ['Full-stack vývojář', 'Šachista', 'Spring Boot inženýr', 'Laravel řemeslník', 'Řešitel problémů'],
```
První slovo („Full-stack"/„Chess") je gold `<span>` — buď obarvit celou frázi jednotně, nebo (jednodušší a konzistentní) celý rotující text dát do gold-semibold jako dnešní span. **Doporučení:** celá fráze gold-semibold, statický prefix „I build things. " volitelný.

**Implementace (vanilla JS, styl repa — app.js je vanilla, ne Alpine):**
- V `welcome.blade.php` vykreslit statický fallback + rotátor:
  ```blade
  <h4 class="underh1">
      <span id="hero-rotator" data-roles='@json(__("home/hero.hero_roles"))'>{{ __('home/hero.hero_roles')[0] }}</span><span class="hero-caret" aria-hidden="true"></span>
  </h4>
  ```
- Do `resources/js/app.js` přidat `initHeroRotator()` (spustit v `DOMContentLoaded`): čte `data-roles`, type-in po ~55ms/znak, pauza 1.6s, type-out ~35ms/znak, další role. Cyklus infinite.
- **Respektovat `prefers-reduced-motion`**: když `matchMedia('(prefers-reduced-motion: reduce)').matches` → rotátor se nespustí, zůstane první statická role (guard už je v CSS zvykem repa).
- CSS caret v `index.css`: `.hero-caret` = 2px gold sloupec + `@keyframes caret-blink { 50% { opacity: 0 } }` (1s step). Reduced-motion vypne blik.
- A11y: `#hero-rotator` dostane `aria-live="polite"`; caret `aria-hidden`.

**Verify:** desktop 1440px — text se plynule píše/maže, gold, caret bliká; přepni jazyk (`/language/toggle`) → jede česká sada; nastav OS reduced-motion → statický text bez blikání.

---

## PRIORITA 2 — Profesionální animace (desktop)

Dnes: jediný keyframe v celé appce (rotující border `.exp-card--special`), nula scroll-reveal, nula entrance motion. `@media (prefers-reduced-motion: reduce)` guard už existuje (`app.css:277`) — každou novou animaci pod něj schovat.

Navrhované (od nejvíc „profi za nejmíň práce"):
1. **Scroll-reveal sekcí** — `.portfolio-section` fade-up (opacity 0→1, translateY 24px→0) při vstupu do viewportu. Vanilla `IntersectionObserver` v `app.js` (`initScrollReveal()`), přidává `.is-visible`; CSS přechod. Staggered delay pro karty (stats, tools, reviews) přes `--i` index. **Největší vizuální zisk.**
2. **Hero entrance** — suptitle → h1 → tagline → foto v kaskádě (staggered fade-up) hned po loadu.
3. **Stat count-up** — čísla v `stats-cards` a about-me stats „napočítají" z 0 při odhalení (IntersectionObserver + `requestAnimationFrame`). Ladí s už existující live ELO/repos logikou.
4. **Magnetic / tilt hover na tool logách a projekt kartách** — jemný `transform: translateY(-6px) scale(1.03)` + soft glow (gold `box-shadow`) na hover, `--t-fast`. Levné, čistě CSS.
5. **Gradient/animovaný accent na h1 accent span** — jemný shimmer po textu (už máš `@property` pattern z exp-border, dá se recyklovat).
6. **Work/Life přepínač** — dnes tvrdý `display:none` swap (`app.js:103`). Přidat cross-fade + výškovou animaci (nebo aspoň opacity). Zároveň řeší a11y nález #32 (převést `.work-top-btn` divy na `<button aria-pressed>`).
7. **Smooth scroll + „scroll progress" gold linka** nahoře (fixed, šířka = % scrollu). Jemný profi detail.

Vše nové motion **pod `prefers-reduced-motion`**. IntersectionObserver kód sdílet jednou utilitou v `app.js`.

---

## PRIORITA 3 — Fonty (návrhy)

Dnes: **Inter** (body, všude) + **Space Grotesk** (jen outlined display h1-span a h2). Display font je poddimenzovaný — použitý na 2 místech.

**Doporučené směry** (vybrat jeden — viz Rozhodnutí):
- **A. Vyladit stávající pár (min. risk):** nechat Inter body, ale Space Grotesk roztáhnout i na h3/h4 a hero tagline → víc charakteru zdarma. Doporučeno jako baseline.
- **B. Ostřejší display (doporučená změna):** body zůstává Inter/**Geist**, display přepnout na **Clash Display** nebo **General Sans** (Fontshare) / **Sora** (Google) — modernější, „portfolio" look. Mění se 1 token `--font-display` + `<link>` ve dvou souborech (`partials/head.blade.php`, `portfolio-layout.blade.php`).
- **C. Monospace accent:** pro „developer" vibe dát `.mini` labely, hodiny v telefonu, rok u projektů a tech-tagy do mono fontu (**JetBrains Mono** / **Geist Mono**). Malý, ale výrazný detail.

**Doporučení:** B pro display (Space Grotesk → General Sans nebo Clash Display) + C pro mono akcenty. Body Inter nechat. Fonty jsou 1 token = levná zkouška; kandidáty snadno prohodíme za běhu.

**Pozn.:** font se načítá **2× duplicitně** (admin `head.blade.php` i public `portfolio-layout.blade.php`) — při změně upravit obě místa (nález D3 v `docs/design-audit-findings.md`).

---

## PRIORITA 4 — Barvy (jemné úpravy)

Paleta (gold `#FACC15` na tmavé `#1C1B17`) je dobrá a konzistentní. Návrhy jsou aditivní, ne přebarvení:
- **Depth/hierarchie v dark:** `--c-bg` (#1C1B17) a `--c-surface` (#232219) jsou blízko — karty splývají. Buď surface o chlup zvednout, nebo přidat jemný `box-shadow`/inner border pro odlišení (nález A6, částečně řešeno).
- **Gold gradient accent:** místo ploché gold pro velké nadpisy/CTA použít jemný `linear-gradient(135deg, #FACC15, #FED7AA)` — luxusnější.
- **Subtilní gold glow** pod hero fotem / za h1 (radiální `--c-primary` @ 8% opacity) — „spotlight" efekt, hloubka.
- **Selection už je hezká** (`#FFB347`) — nechat.
- **Light téma** (amber `#B45309` na parchment) je promyšlené — beze změny, jen ověřit u nových animací kontrast.

---

## PRIORITA 5 — Mobilní nav (redesign / vylepšení)

Dnes: realistický **smartphone mockup** (370×820, wallpaper, hodiny, dock) — je to signature prvek, **zachovat koncept**. Vylepšení:
- **Dead odkazy** Messages/Music jsou `<a href="#">` (nález #33) → buď oživit (Messages → kontaktní mailto, Music → odkaz na Spotify/nic), nebo odstranit. Theme toggle „weather" a language „translator" jsou fajn easter-eggy — nechat, ale dát jim `role="button"`/`<button>`.
- **Otevírací gesto na desktopu:** telefon dnes stojí staticky v sidebaru. Přidat jemnou „breathing"/tilt idle animaci nebo parallax na scroll, ať vypadá živě.
- **Skutečná interaktivita ikon:** hover „app icon" → jemný bounce/scale (iOS feel).
- **Na skutečném mobilu** (≤992px): slide-in už funguje; přidat backdrop blur na overlay a haptický „spring" easing místo lineárního `--t-fast`.
- **Alternativa (viz Rozhodnutí):** kdyby měl být telefon jen dekorace, doplnit navíc **minimalistický top-bar / hamburger** pro rychlou navigaci — protože veřejný web dnes **nemá žádný klasický navbar** (nav je jen telefon + footer). To je použitelnostní díra na desktopu.

---

## PRIORITA 6 — Alternativní layout (desktop nápady)

Web má 2sloupcový grid (sticky telefon-sidebar 26rem + obsah). Nápady (nezávazné, k diskuzi):
- **Sticky sekční navigace / scroll-spy** vpravo nebo nahoře — tečky/labely sekcí (Hero·Stats·Work·Projects·Tools·Reviews) co se zvýrazní podle scrollu. Řeší chybějící navbar elegantně.
- **Hero jako split-screen** s větší fotkou a gold geometrickým pozadím (grid/mřížka nebo jemný noise), tagline rotátor jako hrdina.
- **Projekty jako bento grid** místo střídajících se řádků — modernější, hutnější na desktopu.
- **Tools sekce jako „marquee"** (nekonečně scrollující pás log) — populární profi efekt.
- **Reviews jako carousel** místo statického flex row.

**Doporučení:** začít neinvazivně — scroll-spy nav + bento projekty + tools marquee. Velký přerod layoutu (hero split) nechat jako druhou vlnu.

---

## PRIORITA 7 — „Nice" detaily (co potěší)

- Custom gold **caret/cursor** nebo aspoň gold `::selection` (už je).
- **Hover glow** na social ikonách ve footeru (dnes jen barva textu).
- **Noise/grain overlay** přes pozadí (velmi jemný) — filmový nádech.
- **Animovaný gradient border** na hero fotce (recyklovat `@property --exp-border-angle` z exp karet).
- **404 / prázdné stavy** s osobností (šachová figurka?).
- **Favicon + OG image** kontrola (SEO/sdílení).
- **Scroll-to-top** gold FAB tlačítko.

---

## PRIORITA 8 — Admin logika (dodělat)

Stav: **plný CRUD existuje** pro Projects, Experiences, Badges, Links, Articles (Volt `⚡*` komponenty v `resources/views/pages/manage/`, gated `['auth','verified']`). Ale jsou díry:

1. **Dashboard je statický pahýl** (`resources/views/dashboard.blade.php` = jen „Welcome back") → udělat **Volt komponentu** s: počty entit (projekty/experiences/badges/articles), poslední upravené položky, quick-create tlačítka, rychlé odkazy na manage stránky. Reuse Flux karty. **Nejrychlejší viditelný admin zisk.**
2. **Obsah homepage/about-me není editovatelný** — hero tagline+role, stats čísla, tools, reviews, celé about-me žijí v `resources/lang/*.php` bez UI. Návrh: model **`Setting` / `PageContent`** (key + json{en,cs} value) + manage stránka `⚡settings-content` na editaci těchto textů; NEBO editor lang souborů. **Doporučení:** DB `Setting` model pro hero role, stats, reviews, tools (přesunout z lang do DB), aby šlo měnit bez deploye. Toto je největší admin díra.
3. **Articles nemají veřejnou stránku** — admin je tvoří, ale nikdo je nevidí (žádný `/articles` route/blade). Buď **postavit veřejný `/articles` + detail** (blog), nebo Articles zrušit. **Doporučení:** postavit veřejný blog — obsah už je připravený.
4. **Žádná autorizační vrstva** — žádný role/Gate/Policy, každý verified user = plný admin. Registrace je vypnutá, takže „bezpečné absencí signupů", ale bez defense-in-depth. Návrh: přidat `is_admin` na `users` + `admin` middleware/Gate (low urgency, single-user).
5. **Upload jen u Projects/Experiences.** Article thumbnail, Link ikona, User profilovka jsou **jen URL string** bez uploadu. Doplnit `WithFileUploads` (pattern už je v `⚡projects.blade.php`).
6. **Onboarding prvního admina** — registrace off, žádný seeder na admina. Přidat `php artisan make:` command nebo seeder.

Reuse: sdílené manage komponenty už existují (`resources/views/components/manage/*`: page-header, search-input, locale-tabs, badge-picker, link-repeater, delete-modal, modal-footer). CRUD logika je ~80 % duplicitní ×5 → kandidát na trait `ManagesCrudModals` (viz `docs/design-audit-findings.md` sekce F).

---

## Rozhodnutí k potvrzení (před implementací)

Nejsou blokující — u každého mám doporučení; potvrzení jen doladí směr.

1. **Rotující tagline — sada rolí?** Doporučuji: `Full-stack developer, Chess player, Spring Boot engineer, Laravel craftsman, Problem solver`. Chceš jiné/víc (např. „Coffee-driven", konkrétní technologie)?
2. **Rotátor — styl?** Typewriter (píše/maže) vs. jednoduchý fade-swap. Doporučuji typewriter (efektnější, sedí k „developer" vibe).
3. **Display font — směr B?** Space Grotesk → General Sans / Clash Display / Sora. Nebo zůstat u Space Grotesk (směr A)? + mono akcenty (JetBrains Mono) ano/ne?
4. **Rozsah této vlny?** Doporučuji vlna 1 = P1 (tagline) + P2 (scroll-reveal, hero entrance, stat count-up, hover) + P8.1 (dashboard). Zbytek (layout přerod, obsah→DB, blog) vlna 2.
5. **Mobilní nav — přidat i klasický navbar/scroll-spy** na desktop (chybí navigace), nebo nechat čistě telefon+footer?
6. **Admin obsah → DB migrace** (hero/stats/tools/reviews z `lang/` do `Setting` modelu): chceš teď, nebo až v pozdější vlně? (Je to větší zásah + migrace + testy.)

---

## Doporučené pořadí (sekvence)

**Vlna 1 (rychlý viditelný efekt, nízké riziko, jen CSS/JS + 1 Volt):**
1. Rotující tagline (P1) — lang + `app.js` + `index.css`.
2. Scroll-reveal + hero entrance + hover lift (P2.1–2.4) — `app.js` + CSS.
3. Stat count-up (P2.3).
4. Dashboard Volt komponenta s počty + quick actions (P8.1).
5. Font swap experiment (P3, směr dle rozhodnutí) — 1 token + 2 `<link>`.
6. Barevné hloubkové detaily (P4) — glow, gradient accent.
→ `npm run build`, ověřit na `localhost:8008` desktop 1440 + light/dark + reduced-motion.

**Vlna 2 (větší zásahy):**
7. Layout: scroll-spy nav + bento projekty + tools marquee (P6).
8. Obsah homepage/about → `Setting` model + manage UI (P8.2).
9. Veřejný `/articles` blog (P8.3) + uploady (P8.5).
10. Mobilní nav polish + volitelný navbar (P5).
11. Admin role/Gate (P8.4).

---

## Verifikace (end-to-end)

- **Build:** `npm run build` na hostu po každé FE změně (bez toho se nic neprojeví).
- **Vizuál:** `http://localhost:8008` — desktop 1440px primárně; ověřit hero rotátor, scroll-reveal, count-up, hover. Přepnout `theme` cookie dark↔light. Přepnout jazyk `/language/toggle` → česká sada rolí. OS reduced-motion → animace off, statický fallback.
- **Admin:** login (verified user; registrace off — user ze seederu/tinkeru), `/dashboard` ukazuje počty + quick actions; manage CRUD stále funguje.
- **PHP:** `docker exec portfolio-app-1 vendor/bin/pint --dirty --format agent` + `docker exec portfolio-app-1 php artisan test --compact` (nové modely/komponenty pokrýt testem — enforcement v CLAUDE.md).
- **Regrese:** žádný horizontální scroll na žádné veřejné stránce (historický bug), mobilní nav se pořád otevírá (`#toggle-mobile-nav` viditelný ≤992px).

---

## Klíčové soubory

| Oblast | Soubor |
|---|---|
| Hero markup / tagline | `resources/views/welcome.blade.php:8` |
| Role texty (bilingv.) | `resources/lang/{en,cs}/home/hero.php` |
| Tokeny (barvy/font/type/přechody) | `resources/css/app.css` (`@theme` + `:root`) |
| Homepage CSS | `resources/css/pages/index.css` |
| JS (rotátor, animace, nav, theme) | `resources/js/app.js` |
| Mobilní nav | `resources/views/components/mobile-nav.blade.php` + `app.css:549-755` |
| Public shell | `resources/views/components/portfolio-layout.blade.php` |
| Font `<link>` (2× duplicitně) | `portfolio-layout.blade.php` + `resources/views/partials/head.blade.php` |
| Dashboard (pahýl) | `resources/views/dashboard.blade.php` + `routes/web.php` |
| Manage CRUD | `resources/views/pages/manage/⚡*.blade.php` |
| Sdílené manage komponenty | `resources/views/components/manage/*` |
| Existující audity (číst) | `docs/design-audit-findings.md`, `docs/typography.md`, `docs/frontend-headings-and-mobile-nav.md` |
</content>
</invoke>
