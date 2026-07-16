# About Me Page Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Create a standalone `/about-me` page with an About Me cards section and a Stats section, ported from the old portfolio.

**Architecture:** New `AboutMeController` handles the route, returning a new `about-me` view. Lang content lives in `resources/lang/{locale}/home/about-me.php`; stats reuse the existing `home/stats` lang keys. CSS is added to `app.css` using existing design tokens. JS API fetches (chess elo, GitHub repos) are added to `app.js` gated by element existence.

**Tech Stack:** Laravel 13, Blade, Tailwind CSS v4 (custom properties), vanilla JS fetch API, Pest v4

---

## File Map

| Action  | File                                              | Responsibility                          |
|---------|---------------------------------------------------|-----------------------------------------|
| Create  | `app/Http/Controllers/AboutMeController.php`      | Invokable controller, returns about-me view |
| Modify  | `routes/web.php`                                  | Point `/about-me` route to new controller |
| Create  | `resources/lang/en/home/about-me.php`             | EN about-me card content                |
| Create  | `resources/lang/cs/home/about-me.php`             | CS about-me card content                |
| Create  | `resources/views/about-me.blade.php`              | About Me + Stats sections               |
| Modify  | `resources/css/app.css`                           | Styles for about-me and stats-extended sections |
| Modify  | `resources/js/app.js`                             | Chess elo + GitHub repos API fetches    |
| Create  | `tests/Feature/AboutMePageTest.php`               | Route returns 200, sections visible     |

---

### Task 1: Write failing test for the about-me route

**Files:**
- Create: `tests/Feature/AboutMePageTest.php`

- [ ] **Step 1: Create the test file**

```bash
docker exec portfolio-2-app-1 php artisan make:test --pest AboutMePageTest
```

- [ ] **Step 2: Replace generated content with actual tests**

`tests/Feature/AboutMePageTest.php`:
```php
<?php

test('about me page returns 200', function () {
    $response = $this->get(route('about-me'));
    $response->assertOk();
});

test('about me page contains about me section', function () {
    $response = $this->get(route('about-me'));
    $response->assertSee('about-me-content', false);
});

test('about me page contains stats section', function () {
    $response = $this->get(route('about-me'));
    $response->assertSee('about-me-stats-cards', false);
});
```

- [ ] **Step 3: Run the tests to confirm they fail**

```bash
docker exec portfolio-2-app-1 php artisan test --compact --filter=AboutMePageTest
```

Expected: 3 FAIL — route exists but controller returns the wrong view.

---

### Task 2: Create AboutMeController

**Files:**
- Create: `app/Http/Controllers/AboutMeController.php`

- [ ] **Step 1: Generate the controller**

```bash
docker exec portfolio-2-app-1 php artisan make:controller AboutMeController --invokable --no-interaction
```

- [ ] **Step 2: Replace generated body**

`app/Http/Controllers/AboutMeController.php`:
```php
<?php

namespace App\Http\Controllers;

class AboutMeController extends Controller
{
    public function __invoke()
    {
        return view('about-me');
    }
}
```

- [ ] **Step 3: Update the route**

`routes/web.php` — change the `/about-me` line:
```php
use App\Http\Controllers\AboutMeController;

// replace:
Route::get('/about-me', HomeController::class)->name('about-me');
// with:
Route::get('/about-me', AboutMeController::class)->name('about-me');
```

The full updated `routes/web.php`:
```php
<?php

use App\Http\Controllers\AboutMeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LanguageController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/about-me', AboutMeController::class)->name('about-me');
Route::get('/experience', HomeController::class)->name('experience');
Route::get('/projects', HomeController::class)->name('projects');

Route::get('/language/toggle', [LanguageController::class, 'toggle'])->name('language.toggle');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::livewire('dashboard/experiences', 'pages::manage.experiences')->name('manage.experiences');
    Route::livewire('dashboard/badges', 'pages::manage.badges')->name('manage.badges');
    Route::livewire('dashboard/articles', 'pages::manage.articles')->name('manage.articles');
    Route::livewire('dashboard/projects', 'pages::manage.projects')->name('manage.projects');
    Route::livewire('dashboard/links', 'pages::manage.links')->name('manage.links');
});

require __DIR__.'/settings.php';
```

---

### Task 3: Create lang files

**Files:**
- Create: `resources/lang/en/home/about-me.php`
- Create: `resources/lang/cs/home/about-me.php`

- [ ] **Step 1: Create EN lang file**

`resources/lang/en/home/about-me.php`:
```php
<?php

return [
    'title' => 'About me',

    'card1_title' => 'About me',
    'card1_text'  => 'Hi there! I\'m Richard Hývl, a starting software developer and freelancer with a passion. Currently, I\'m a student at SPŠE Pardubice and leading figure in a Web developing group called <span>Prezz.</span>',

    'card2_title' => 'What do I like?',
    'card2_text'  => 'From a young age I was a passionate chess player. I was winning in 2nd grade against highschoolers at local chess tournaments. With a great break I\'m back, with a renewed passion for the game. Chess has taught me <span>critical thinking</span>, <span>strategy</span>, and the important <span>patience</span> — skills that I\'ve found incredibly valuable in my journey as a software developer.<br><br>I also really really love <span>catfishes</span> and <span>Rock music</span> :)',

    'card3_title' => 'What drives me?',
    'card3_text'  => 'I\'m driven by curiosity and the desire to help other people. I thrive on learning, growing my personality and one day becoming a successful person.',

    'card4_title' => 'How did we get here?',
    'card4_text'  => 'My journey started after I went back to elementary school from gymnasium due to health issues. Luckily the elementary school had extended teaching in the field of IT.<br><br>There, I fell in love with the technology, the unlimited options to create and innovate new things — it was like a dream. There, I developed my first website. Sadly, it was deleted by the hosting and I had no backup.<br><br>At high school, my passion thrived even more, leading me to achieve multiple victories, like winning a hackathon and becoming a freelancer.',

    'card5_title' => 'Volunteering?',
    'card5_text'  => 'I have volunteered at a few community events. It helped me develop presenting skills.<br><br>What was I part of:<ul><li><p><span>PEER program:</span> A program that helps teenagers understand the dangers of drugs and bullying. I was educated and then presented to my peers.</p></li><li><p><span>CZECH DAY AGAINST CANCER:</span> An organisation that collects funds to help people with cancer by selling flower badges on streets.</p></li></ul>',
];
```

- [ ] **Step 2: Create CS lang file**

`resources/lang/cs/home/about-me.php`:
```php
<?php

return [
    'title' => 'O mně',

    'card1_title' => 'O mně',
    'card1_text'  => 'Ahoj! Jsem Richard Hývl, začínající softwarový vývojář a freelancer s vášní. V současné době jsem studentem na SPŠE Pardubice a vůdčí osobností skupiny webových vývojářů <span>Prezz.</span>',

    'card2_title' => 'Co mám rád?',
    'card2_text'  => 'Od mládí jsem byl vášnivým šachistou. Ve 2. třídě jsem vyhrával proti středoškolákům na místním šachovém turnaji. S velkou přestávkou jsem zpět, s obnovenou vášní pro hru. Šachy mě naučily <span>kritickému myšlení</span>, <span>strategii</span> a důležité <span>trpělivosti</span> — dovednostem, které považuji za neuvěřitelně cenné na své cestě softwarového vývojáře.<br><br>Mám také opravdu moc rád <span>sumečky</span> a <span>rockovou hudbu</span> :)',

    'card3_title' => 'Co mě pohání?',
    'card3_text'  => 'Pohání mě zvědavost a touha pomáhat druhým lidem. Rád se učím, rozvíjím svou osobnost a jednou budu úspěšný člověk.',

    'card4_title' => 'Jak jsme se sem dostali?',
    'card4_text'  => 'Moje cesta začala poté, co jsem se kvůli zdravotním problémům vrátil z gymnázia na základní školu. Naštěstí měla základní škola rozšířenou výuku v oboru IT.<br><br>Tam jsem se zamiloval do technologií, do neomezených možností vytvářet a inovovat nové věci — bylo to jako sen. Tam jsem vytvořil své první webové stránky. Bohužel je hosting smazal a já neměl žádnou zálohu.<br><br>Na střední škole se mé nadšení rozmohlo ještě víc, což mě dovedlo k několika vítězstvím, jako například vyhrát hackathon a stát se freelancerem.',

    'card5_title' => 'Dobrovolnictví?',
    'card5_text'  => 'Dobrovolně jsem se účastnil několika komunitních akcí. Pomohlo mi to rozvíjet prezentační dovednosti.<br><br>Čeho jsem byl součástí:<ul><li><p><span>Program PEER:</span> Program, který pomáhá dospívajícím pochopit nebezpečí drog a šikany. Byl jsem vzdělán a poté jsem prezentoval svým vrstevníkům.</p></li><li><p><span>ČESKÝ DEN PROTI RAKOVINĚ:</span> Organizace vytvořená za účelem sbírání finančních prostředků na pomoc lidem s rakovinou prostřednictvím prodeje květinových odznaků na ulicích.</p></li></ul>',
];
```

---

### Task 4: Create the about-me view

**Files:**
- Create: `resources/views/about-me.blade.php`

- [ ] **Step 1: Create the view**

`resources/views/about-me.blade.php`:
```blade
<x-portfolio-layout :title="__('layout/header.about_title')" :description="__('layout/header.about_desc')">

    {{-- About Me --}}
    <section id="about-me" class="portfolio-section">
        <h2>{!! __('home/about-me.title') !!}</h2>
        <div id="about-me-content">
            <div class="about-me-card">
                <h3>{!! __('home/about-me.card1_title') !!}</h3>
                <p>{!! __('home/about-me.card1_text') !!}</p>
            </div>
            <div class="about-me-card">
                <h3>{!! __('home/about-me.card2_title') !!}</h3>
                <p>{!! __('home/about-me.card2_text') !!}</p>
            </div>
            <div class="about-me-card">
                <h3>{!! __('home/about-me.card3_title') !!}</h3>
                <p>{!! __('home/about-me.card3_text') !!}</p>
            </div>
            <div class="about-me-card">
                <h3>{!! __('home/about-me.card4_title') !!}</h3>
                <p>{!! __('home/about-me.card4_text') !!}</p>
            </div>
            <div class="about-me-card about-me-card--wide">
                <h3>{!! __('home/about-me.card5_title') !!}</h3>
                <p>{!! __('home/about-me.card5_text') !!}</p>
            </div>
        </div>
    </section>

    {{-- Stats --}}
    <section id="about-me-stats" class="portfolio-section">
        <h2>{{ __('home/stats.title') }}</h2>
        <article id="about-me-stats-cards">
            <div class="stats-cards-card">
                <h3><span>{{ __('home/stats.card1_title') }}</span></h3>
                <p>{{ __('home/stats.card1_text') }}</p>
            </div>
            <div class="stats-cards-card">
                <h3><span>{{ __('home/stats.card2_title') }}</span></h3>
                <p>{{ __('home/stats.card2_text') }}</p>
            </div>
            <div class="stats-cards-card">
                <h3><span>{{ (int) \Carbon\Carbon::parse('2022-09-01')->diffInYears(now()) }}+</span></h3>
                <p>{{ __('home/stats.card3_text') }}</p>
            </div>
            <div class="stats-cards-card">
                <h3><span>{{ __('home/stats.card4_title') }}</span></h3>
                <p>{{ __('home/stats.card4_text') }}</p>
            </div>
            <div class="stats-cards-card">
                <h3><span>{{ __('home/stats.card5_title') }}</span></h3>
                <p>{{ __('home/stats.card5_text') }}</p>
            </div>
            <div class="stats-cards-card">
                <h3><span id="elo">{{ __('home/stats.card6_title') }}</span></h3>
                <p>{{ __('home/stats.card6_text') }}</p>
            </div>
            <div class="stats-cards-card">
                <h3><span>{{ __('home/stats.card7_title') }}</span></h3>
                <p>{{ __('home/stats.card7_text') }}</p>
            </div>
            <div class="stats-cards-card">
                <h3><span>{{ __('home/stats.card8_title') }}</span></h3>
                <p>{{ __('home/stats.card8_text') }}</p>
            </div>
            <div class="stats-cards-card">
                <h3><span>{{ __('home/stats.card9_title') }}</span></h3>
                <p>{{ __('home/stats.card9_text') }}</p>
            </div>
            <div class="stats-cards-card">
                <h3><span id="github-repos">{{ __('home/stats.card10_title') }}</span></h3>
                <p>{{ __('home/stats.card10_text') }}</p>
            </div>
            <div class="stats-cards-card">
                <h3><span>{{ __('home/stats.card11_title') }}</span></h3>
                <p>{{ __('home/stats.card11_text') }}</p>
            </div>
        </article>
    </section>

</x-portfolio-layout>
```

- [ ] **Step 2: Check that `layout/header` lang keys exist for about-me**

```bash
grep -r "about_title\|about_desc" /home/projektant_pata/Projects/Mine/portfolio-2/resources/lang/
```

If keys are missing, add them to `resources/lang/en/layout/header.php` and `resources/lang/cs/layout/header.php`:
```php
'about_title' => 'About Me | Richard Hývl',
'about_desc'  => 'Learn more about Richard Hývl — software developer, chess player, and freelancer.',
```
CS:
```php
'about_title' => 'O mně | Richard Hývl',
'about_desc'  => 'Zjistěte více o Richardu Hývlovi — softwarovém vývojáři, šachistovi a freelancerovi.',
```

---

### Task 5: Add CSS for the about-me page

**Files:**
- Modify: `resources/css/app.css`

Append the following block at the end of `app.css` (after section 7):

- [ ] **Step 1: Append styles**

```css
/* ================================================================
   8. ABOUT ME PAGE
   ================================================================ */

/* ── About Me cards grid ── */
#about-me-content {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 2rem;
}

.about-me-card {
    border: 1px solid var(--c-primary-lt);
    background-color: var(--c-surface);
    border-radius: var(--r-card);
    padding: 2.5rem;
}

.about-me-card h3 {
    color: var(--c-primary);
    margin-bottom: 1.25rem;
    font-size: var(--fs-h4);
}

.about-me-card p {
    font-weight: var(--fw-light);
    line-height: 1.6;
}

.about-me-card ul {
    margin-top: 1rem;
    padding-left: 1.25rem;
}

.about-me-card ul li {
    list-style: circle;
    margin-bottom: 0.75rem;
}

/* Card 5 spans both columns */
.about-me-card--wide {
    grid-column: 1 / -1;
}

/* ── About Me stats grid (11 cards) ── */
#about-me-stats-cards {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.875rem;
}

/* ── Responsive: about me page ── */
@media (max-width: 1440px) {
    #about-me-stats-cards { grid-template-columns: repeat(3, 1fr); }
}

@media (max-width: 992px) {
    #about-me-content      { grid-template-columns: 1fr; }
    .about-me-card--wide   { grid-column: auto; }
    #about-me-stats-cards  { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 576px) {
    #about-me-stats-cards  { grid-template-columns: 1fr; }
}
```

---

### Task 6: Add API fetches to app.js

**Files:**
- Modify: `resources/js/app.js`

- [ ] **Step 1: Append the API fetch functions before the DOMContentLoaded block**

Add the following two functions immediately before `document.addEventListener('DOMContentLoaded', ...)`:

```js
// ── About Me — live stats ────────────────────────────────────

function fetchChessElo() {
    const elo = document.getElementById('elo');
    if (!elo) {
        return;
    }

    fetch('https://api.chess.com/pub/player/ObviousCommander/stats')
        .then(response => {
            if (!response.ok) {
                throw new Error('Chess API error: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            elo.textContent = data?.chess_rapid?.best?.rating ?? elo.textContent;
        })
        .catch(error => console.error('Chess elo fetch failed:', error));
}

function fetchGithubRepos() {
    const repos = document.getElementById('github-repos');
    if (!repos) {
        return;
    }

    fetch('https://api.github.com/users/projektant-pata')
        .then(response => {
            if (!response.ok) {
                throw new Error('GitHub API error: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            repos.textContent = data?.public_repos ?? repos.textContent;
        })
        .catch(error => console.error('GitHub repos fetch failed:', error));
}
```

- [ ] **Step 2: Call both functions inside DOMContentLoaded**

In the existing `document.addEventListener('DOMContentLoaded', () => { ... })` block, add at the end (before the closing `}`):

```js
    fetchChessElo();
    fetchGithubRepos();
```

---

### Task 7: Run full test suite and fix formatting

**Files:** (none new)

- [ ] **Step 1: Run the about-me tests**

```bash
docker exec portfolio-2-app-1 php artisan test --compact --filter=AboutMePageTest
```

Expected: 3 PASS

- [ ] **Step 2: Run pint to fix formatting**

```bash
docker exec portfolio-2-app-1 vendor/bin/pint --dirty --format agent
```

- [ ] **Step 3: Run full test suite**

```bash
docker exec portfolio-2-app-1 php artisan test --compact
```

Expected: all tests pass.

- [ ] **Step 4: Commit**

```bash
git add \
  app/Http/Controllers/AboutMeController.php \
  routes/web.php \
  resources/lang/en/home/about-me.php \
  resources/lang/cs/home/about-me.php \
  resources/views/about-me.blade.php \
  resources/css/app.css \
  resources/js/app.js \
  tests/Feature/AboutMePageTest.php
git commit -m "feat: add standalone about-me page with About Me cards and Stats sections"
```
