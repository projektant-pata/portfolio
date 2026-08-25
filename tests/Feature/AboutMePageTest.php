<?php

use App\Models\Badge;
use App\Models\Experience;
use App\Models\Interest;
use App\Models\PathNode;
use App\Models\Setting;

test('about me page returns 200', function () {
    $response = $this->get(route('about-me'));
    $response->assertOk();
});

test('the about me page renders all seven sections in order', function () {
    seedAboutPage();

    $html = $this->get(route('about-me'))->getContent();

    $sections = [
        'id="about-me-stats"',
        'id="about-me-path"',
        'id="about-me-tools"',
        'id="about-me-interests"',
        'id="about-me-showups"',
        'id="about-me-references"',
        'id="about-me-contact"',
    ];

    $positions = array_map(fn (string $needle) => strpos($html, $needle), $sections);

    expect($positions)->not->toContain(false)
        ->and($positions)->toBe(array_values(collect($positions)->sort()->all()));
});

test('the stats section opens the page without a fade-up', function () {
    seedAboutPage();

    $html = $this->get(route('about-me'))->getContent();

    /* The hero leaves the first section's top edge inside the first viewport,
       so that section must already be painted — it never fades up. */
    expect($html)->toContain('id="about-me-stats" class="portfolio-section portfolio-section--no-reveal"')
        ->and($html)->toContain('sechead--noghost');
});

test('the retired intro section is gone', function () {
    seedAboutPage();

    $html = $this->get(route('about-me'))->getContent();

    expect($html)->not->toContain('about-me-intro')
        ->and($html)->not->toContain('about-me-fact');
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

    expect($html)->not->toContain('about-me-path')
        ->and($html)->not->toContain('tk-cols')
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

test('the availability dot appears in the contact band', function () {
    seedAboutPage();

    $html = $this->get(route('about-me'))->getContent();

    expect(substr_count($html, 'class="availability-dot"'))->toBe(1);

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

test('the about me toolbox groups tools into columns and lists the learning chips', function () {
    seedAboutPage();

    $html = $this->get(route('about-me'))->getContent();

    expect($html)->toContain('tk-cols')
        ->and(substr_count($html, '<div class="tk-col">'))->toBe(4)
        ->and($html)->toContain('Spring Boot')
        ->and($html)->toContain('--bc: #6DB33F')
        ->and($html)->toContain('Kubernetes');
});

test('the about me toolbox translates the frequency labels', function () {
    seedAboutPage();

    $this->withSession(['locale' => 'cs'])
        ->get(route('about-me'))
        ->assertSee('denně')
        ->assertSee('Právě se učím');
});
