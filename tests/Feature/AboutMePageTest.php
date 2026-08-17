<?php

use App\Models\Badge;
use App\Models\Experience;
use App\Models\Interest;
use App\Models\PathNode;
use App\Models\Review;
use App\Models\Setting;
use Database\Seeders\InterestSeeder;
use Database\Seeders\PathNodeSeeder;
use Database\Seeders\SettingSeeder;
use Database\Seeders\StatSeeder;

/** Seeds everything the seven sections read. */
function seedAboutPage(): void
{
    test()->seed(SettingSeeder::class);
    test()->seed(StatSeeder::class);
    test()->seed(PathNodeSeeder::class);
    test()->seed(InterestSeeder::class);
    Experience::factory()->onAbout()->create(['title' => ['en' => 'PEER program', 'cs' => 'Program PEER']]);
    Review::factory()->create(['name' => 'Jan Novák']);
}

test('about me page returns 200', function () {
    $response = $this->get(route('about-me'));
    $response->assertOk();
});

test('the about me page renders all seven sections in order', function () {
    seedAboutPage();

    $html = $this->get(route('about-me'))->getContent();

    $sections = [
        'id="about-me"',
        'id="about-me-path"',
        'id="about-me-stats"',
        'id="about-me-interests"',
        'id="about-me-showups"',
        'id="about-me-references"',
        'id="about-me-contact"',
    ];

    $positions = array_map(fn (string $needle) => strpos($html, $needle), $sections);

    expect($positions)->not->toContain(false)
        ->and($positions)->toBe(array_values(collect($positions)->sort()->all()));
});

test('the about me intro head carries no ghost under the hero', function () {
    seedAboutPage();

    $html = $this->get(route('about-me'))->getContent();

    expect($html)->toContain('sechead--noghost')
        ->and($html)->toContain('portfolio-section--no-reveal');
});

test('the about me page carries exactly one ghost wordmark', function () {
    seedAboutPage();

    $html = $this->get(route('about-me'))->getContent();

    expect(preg_match_all('/<div class="sechead-ghost"/', $html))->toBe(1);
});

test('show ups are filtered by the about flag', function () {
    seedAboutPage();
    Experience::factory()->create(['title' => ['en' => 'Hidden from about', 'cs' => 'Skryté']]);

    $this->get(route('about-me'))
        ->assertSee('PEER program')
        ->assertDontSee('Hidden from about');
});

test('a show up without a badge renders without a kind label and without erroring', function () {
    seedAboutPage();

    $this->get(route('about-me'))
        ->assertOk()
        ->assertDontSee('about-me-show-kind', false);
});

test('a show up with a badge takes the badge colour and translated name', function () {
    seedAboutPage();
    $badge = Badge::factory()->create(['slug' => 'volunteering', 'name' => ['en' => 'Volunteering', 'cs' => 'Dobrovolnictví'], 'color' => '#34D399']);
    Experience::factory()->onAbout()->create(['title' => ['en' => 'Czech Day', 'cs' => 'Český den']])->badges()->attach($badge);

    $this->get(route('about-me'))
        ->assertSee('--show-color: #34D399', false)
        ->assertSee('Volunteering');
});

test('every section renders nothing on an empty database', function () {
    $html = $this->get(route('about-me'))->assertOk()->getContent();

    expect($html)->not->toContain('about-me-intro')
        ->and($html)->not->toContain('about-me-path')
        ->and($html)->not->toContain('about-me-bento')
        ->and($html)->not->toContain('about-me-beyond')
        ->and($html)->not->toContain('about-me-showups')
        ->and($html)->not->toContain('about-me-cta');
});

test('the czech locale renders the czech copy of the new sections', function () {
    seedAboutPage();

    $this->withSession(['locale' => 'cs'])
        ->get(route('about-me'))
        ->assertSee('Zpátky na základní školu')
        ->assertSee('Šachy od druhé třídy');
});

test('the availability dot appears in the intro and the contact band together', function () {
    seedAboutPage();

    $html = $this->get(route('about-me'))->getContent();

    expect(substr_count($html, 'class="availability-dot"'))->toBe(2);

    Setting::updateOrCreate(['key' => 'about_available'], ['value' => ['en' => '', 'cs' => '']]);

    $html = $this->get(route('about-me'))->getContent();

    expect(substr_count($html, 'class="availability-dot"'))->toBe(0);
});

test('the cv button is absent until a cv path is configured', function () {
    seedAboutPage();

    $this->get(route('about-me'))->assertDontSee('pill-button--ghost" download', false);

    config(['portfolio.cv_path' => 'cv/richard-hyvl.pdf']);

    $this->get(route('about-me'))->assertSee('cv/richard-hyvl.pdf', false);
});

test('an interest highlight is rendered as markup, not escaped, and cannot inject', function () {
    seedAboutPage();
    Interest::query()->delete();
    Interest::factory()->create([
        'title' => ['en' => '<b>Chess</b> since 2nd grade'],
        'highlight' => ['en' => '2nd grade'],
    ]);

    $this->get(route('about-me'))
        ->assertSee('<span>2nd grade</span>', false)
        ->assertDontSee('<b>Chess</b>', false);
});

test('the stats bento marks hero and soft tiles', function () {
    seedAboutPage();

    $html = $this->get(route('about-me'))->getContent();

    expect(substr_count($html, 'about-me-tile--hero'))->toBe(2)
        ->and(substr_count($html, 'about-me-tile--soft'))->toBe(2);
});

test('the path node marked now takes the now modifier', function () {
    seedAboutPage();

    $html = $this->get(route('about-me'))->getContent();

    expect(substr_count($html, 'about-me-node--now'))->toBe(1)
        ->and(PathNode::where('is_now', true)->count())->toBe(1);
});
