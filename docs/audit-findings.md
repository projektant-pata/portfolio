# Audit nálezy (krok 1 z docs/supr-cupr-upgrade-plan.md)

Stav: 2026-07-16. **Nálezy #1–#7, #9–#20 opraveny** (viz jednotlivé body). Zbývá: #8 (email verification). Ověřeno testy (`docker exec portfolio-app-1 php artisan test`): 87 passed, 2 skipped.

## Kritické / vysoké

1. ✅ OPRAVENO **Plaintext přihlašovací údaje v repu** — `database/seeders/DatabaseSeeder.php:18-23`
   Reálný e-mail + heslo `Heslo123!` + cesta k profilovce, natvrdo v seederu; repo je veřejné na GitHubu (odkazované z ProjectsSeeder). Pokud se heslo používá i jinde/na produkci → převzetí účtu.
   Fix: číst z env (`SEED_ADMIN_EMAIL/PASSWORD`), heslo rotovat. Zvážit i git historii (BFG), heslo považovat za kompromitované.
   → Seeder teď čte `SEED_ADMIN_{NAME,EMAIL,PASSWORD,PICTURE}` z env, fallback `Str::password(16)`. **Stále nutné ručně:** vyčistit git historii + rotovat kompromitované heslo `Heslo123!`.

2. ✅ OPRAVENO **User chybí trait `TwoFactorAuthenticatable`** — `app/Models/User.php`
   Fortify má 2FA zapnuté (`config/fortify.php:150`, confirm+confirmPassword), users tabulka má sloupce, ale model trait nemá →
   a) stránka Settings→Security padá 500 (`hasEnabledTwoFactorAuthentication()` undefined),
   b) 2FA challenge se při loginu nikdy nespustí (Fortify kontroluje `class_uses_recursive`) — user s nastaveným 2FA se přihlásí bez druhého faktoru.
   Potvrzeno 6 faily (SecurityTest, TwoFactorChallengeTest, AuthenticationTest).
   Fix: `use Laravel\Fortify\TwoFactorAuthenticatable;` v modelu.

3. ✅ OPRAVENO **Update profilu vždy padá TypeError** — `app/Concerns/ProfileValidationRules.php` (`profileRules(?int $userId)`)
   User má UUID PK (string), `⚡profile.blade.php` volá `profileRules($user->id)` → non-numeric string do `?int` → TypeError → uložení profilu 500.
   Potvrzeno 2 faily ProfileUpdateTest.
   Fix: `?string $userId = null` (a v `emailRules` totéž).

4. ✅ OPRAVENO **Testy běží v env `local`, ne `testing`** — `docker-compose.yml:9` (`APP_ENV=local`) + `phpunit.xml:21`
   Container env přebije phpunit `<env name="APP_ENV" value="testing"/>` (chybí `force="true"`) → CSRF middleware aktivní → 419 na všech POST → 24 falešných failů v dokumentovaném dev prostředí.
   Fix: `<env name="APP_ENV" value="testing" force="true"/>` (nebo APP_ENV z compose pryč).
   → Samotné `force="true"` nestačilo: Docker injektuje `APP_ENV=local` do `$_SERVER`, které Laravel čte dřív než putenv. Proto **obojí**: `force="true"` v phpunit.xml + odstranění `APP_ENV=local` z docker-compose.yml (runtime env teď z `.env`). Testy běží bez override: 69 passed.

## Střední

5. ✅ OPRAVENO **`Rule::unique()->ignore($this->editingId)` s klientem ovladatelnou hodnotou** — `⚡badges.blade.php`, `⚡articles.blade.php`, `⚡projects.blade.php` (save())
   `editingId` je public Livewire property → klient ji může nastavit na cokoliv; Laravel docs výslovně varují (SQL injection vektor přes ignore).
   Fix: před použitím validovat `uuid`, nebo načíst model přes `findOrFail` a předat `ignore($model)`.
   → `save()` teď na začátku načte model přes `findOrFail($this->editingId)` (tampering → 404) a předá instanci do `->ignore($model)`; `ignore(null)` při create nic neignoruje. Testy: duplicate slug fail + edit keeps own slug.

6. ✅ OPRAVENO **`reorder()` rozbíjí globální pořadí při aktivním filtru** — `⚡experiences.blade.php`, `⚡articles.blade.php`, `⚡projects.blade.php`
   Přečísluje `sort_order` 0..n jen pro vyfiltrovanou podmnožinu → přepíše pořadí vůči skrytým záznamům. Navíc `firstWhere($id)` může vrátit null (id mimo filtr) → splice vloží null → crash v `each`.
   Fix: reorder povolit jen bez filtru, nebo přepočítávat pozice v rámci celé tabulky; null-check na item.
   → `reorder()` teď early-return, pokud je aktivní `search` (a `typeFilter` u experiences), načítá celou nefiltrovanou tabulku a null-checkuje `$item`. Testy: reorder no-op při filtru + ignore unknown id.

7. ✅ OPRAVENO **`Badge::experiences()` má špatnou pivot tabulku** — `app/Models/Badge.php`
   Bez explicitního názvu Laravel odvodí `badge_experience`, ale tabulka je `experience_badge` → relace při použití spadne. Teď nikde nevolaná (mrtvý kód).
   Fix: `belongsToMany(Experience::class, 'experience_badge')` nebo smazat.
   → Přidán explicitní název `'experience_badge'` (shodně s `Experience::badges()`). Test: attach + read-back přes relaci.

8. **Email verification fakticky nefunguje** — `app/Models/User.php` + `routes/web.php:17` + `config/fortify.php:149`
   User neimplementuje `MustVerifyEmail` → `verified` middleware propustí každého, verifikační e-mail se nikdy neposílá. Feature zapnutá jen naoko.
   Fix: buď implementovat interface, nebo vypnout feature + odstranit `verified` z rout (registrace je stejně vypnutá).

9. ✅ OPRAVENO **Repo nejde rozjet z čistého klonu** — chybí `.env.example` (composer `setup` skript ho kopíruje) a `bootstrap/cache/` (docker build padal, dokud jsem adresář nevytvořil).
   Fix: commitnout `.env.example` a `bootstrap/cache/.gitignore` (+ `storage/framework/*` konvence).
   → Přidán `.env.example` (pgsql/db/8008, session+queue+cache=database, `SEED_ADMIN_*` placeholdery) a standardní `*\n!.gitignore` do `bootstrap/cache` + `storage/framework/{cache/data,sessions,views,testing}` + `storage/logs`.

10. ✅ OPRAVENO **Testy na sqlite, produkce PostgreSQL** — `phpunit.xml:26` vs. `ILIKE` a `->>'en'` v manage stránkách/controllerech
    sqlite `ILIKE` nezná → search cesty jsou testy nepokryté/nepokrytelné; riziko bugů jen v produkci.
    Fix: testovací PG databáze (service v compose), nebo aspoň `whereJsonContains`/`where(DB::raw('lower(...)'))` portable varianty.
    → Všech 5 `ILIKE` výskytů (badges/articles/projects/experiences/links) nahrazeno portable `lower(FIELD->>'en') LIKE lower(?)`. `->>'en'` i `lower()+LIKE` fungují na PG i sqlite ≥3.38 (container má 3.46). Search je teď testy pokrytý (case-insensitive test u badges). `%`/`_` escaping zůstává jako #18.

11. ✅ OPRAVENO **Upload obrázků: staré soubory se nemažou** — `⚡experiences.blade.php` a `⚡projects.blade.php` save()
    Při nahrání nového obrázku se starý na disku (`storage/app/public/...`) nechá → sirotci. `image_path`/`img_url` je navíc nevalidovaná public property.
    Fix: `Storage::disk('public')->delete()` starého souboru; property validovat nebo zprivátnit tok.
    → Nový upload teď smaže starý soubor (`Storage::disk('public')->delete()`, strip `storage/` prefixu). `image_path`/`img_url` se nikdy nebere z klienta — bez uploadu se přebere z uloženého modelu (`$model?->...`). Testy: nový upload maže starý soubor (obě stránky).

## Nízké

12. ✅ OPRAVENO **Stavová změna přes GET** — `routes/web.php:15` `/language/toggle`
    GET mění session (prefetch/crawler může přepínat jazyk), bez CSRF. Fix: POST form/Livewire akce, nebo aspoň `rel="nofollow"` + kontrola.
    → Route je teď `Route::post`; jediný trigger (`mobile-nav.blade.php`) je `<form method="POST">` + `@csrf`. Test: POST přepíná locale, GET vrací 405.
13. ✅ OPRAVENO **`SetLocale` nevaliduje hodnotu ze session** proti whitelistu `['cs','en']` — `app/Http/Middleware/SetLocale.php`. Defenzivní drobnost.
    → `handle()` nastaví locale jen když session hodnota je v `SUPPORTED_LOCALES = ['cs','en']` (`in_array` strict). Test: neplatné locale ze session je ignorováno.
14. ✅ OPRAVENO **Duplicitní `wire:model` na jednom inputu** — `⚡badges.blade.php` (`wire:model="name.en"` + `wire:model.live.debounce="name.en"`), totéž `⚡articles.blade.php` (header.en). Autofill slugu nespolehlivý. Nechat jen `.live.debounce`.
    → Ponecháno jen `wire:model.live.debounce` na obou inputech (name.en / header.en).
15. ✅ OPRAVENO **Nekonzistentní formát barev badge** — `2026_04_12_165907_seed_badge_colors.php` ukládá hex, manage UI nabízí Flux názvy (red/blue/…); veřejné views používají `--badge-color` (hex OK, název ne), manage tabulka `<flux:badge color=...>` (název OK, hex ne). Sjednotit na jedno.
    → Sjednoceno na **hex** (formát, který už seeder a veřejné views používají). Manage select nabízí hex hodnoty (labely Gold/Amber/…, shodné s 9 seedovanými hexy → editace seednutého badge předvyplní), tabulka zobrazuje swatch + hex místo `<flux:badge>`. Validace zpřísněna na `regex:/^#[0-9A-Fa-f]{6}$/`. Test: nehex barva (`red`) selže validací.
16. ✅ OPRAVENO **`target="_blank"` bez `rel="noopener"`** — `resources/views/welcome.blade.php` (experience odkazy; experience.blade.php to má správně).
    → Přidáno `rel="noopener noreferrer"` ke všem `target="_blank"` ve welcome (2× experience life/work, 3× projekty web/github). Test: home vypíše `target="_blank" rel="noopener noreferrer"` u experience odkazu.
17. ✅ OPRAVENO **Mrtvé soubory/kód** — `laravel.html` (prázdný), `experience.md` (osobní poznámky) v rootu; nepoužitý import `Request` v `LanguageController`; zakomentovaný kód v `DatabaseSeeder`.
    → Smazány `laravel.html` + `experience.md`, odebrán `use Illuminate\Http\Request;` z `LanguageController`, odebrán `// User::factory(10)->create();` z `DatabaseSeeder`.
18. ✅ OPRAVENO **Neescapované `%`/`_` ve vyhledávání** — LIKE bindingy v manage stránkách; jen kosmetika.
    → Všech 5 (badges/articles/projects/experiences/links) obaluje search přes `addcslashes($this->search, '%_\\')` → wildcardy hledány literálně. Test: search `%` u badges nevrací nic.
19. ✅ OPRAVENO **`ProjectsSeeder` není zaregistrovaný v `DatabaseSeeder`** — spouští se jen ručně; pokud záměr, zdokumentovat.
    → `DatabaseSeeder::run()` teď volá `$this->call(ProjectsSeeder::class)`. Test: seed vytvoří admina + projekty spse-hub/u-sladovny/portfolio.
20. ✅ OPRAVENO **CLAUDE.md drift** — názvy kontejnerů `portfolio-2-*` neexistují, skutečné jsou `portfolio-app-1`, `portfolio-db-1`, `portfolio-nginx-1`; composer žádá `php ^8.3`, CLAUDE.md tvrdí 8.5 (kontejner je 8.5 — OK, ale sjednotit).
    → Názvy kontejnerů v CLAUDE.md už opraveny dřív (portfolio-app-1/db-1/nginx-1). `composer.json` sjednocen na `"php": "^8.5"` shodně s runtime kontejnerem a CLAUDE.md.

## Poznámky (ne-nálezy)

- `{!! Str::markdown($content) !!}` v `experience.blade.php:74` je bezpečné — Laravel 13 GFM converter defaultně escapuje raw HTML a blokuje `javascript:` odkazy (ověřeno tinkerem). Přesto doporučuji explicitní `['html_input' => 'strip', 'allow_unsafe_links' => false]`.
- `{!! __('...') !!}` ve welcome/about-me čte jen vlastní lang soubory — OK.
- `whereRaw(..., [?])` a `orderByRaw` používají bindingy/statické stringy — SQL injection tudy není.
- Registrace je vypnutá (`config/fortify.php:147`) — dashboard tedy jen pro seednutého admina. Dobře.
