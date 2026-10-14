<?php

use App\Models\Article;
use App\Models\Setting;

test('every public page closes on the band', function (string $url) {
    $html = $this->get($url)->getContent();

    expect(substr_count($html, 'class="cta-band"'))->toBe(1)
        ->and($html)->toContain('id="page-cta"');
})->with([
    fn () => route('home'),
    fn () => route('experience'),
    fn () => route('projects'),
    fn () => route('blog'),
    fn () => route('contact'),
]);

test('an article closes on the band too, with the article copy', function () {
    $article = Article::factory()->create(['published_at' => now()->subDay()]);

    $this->get(route('blog.show', $article->slug))
        ->assertSee('class="cta-band"', false)
        ->assertSee('If this one was useful,', false);
});

test('each page gets its own copy', function (string $url, string $expected) {
    $this->get($url)->assertSee($expected, false);
})->with([
    [fn () => route('home'), 'Seen enough? Let us <em>talk</em>.'],
    [fn () => route('projects'), 'Your project could be <em>next</em> in this list.'],
    [fn () => route('contact'), 'Prefer it <em>short</em>?'],
]);

test('about me renders the band once, from its own settings', function () {
    Setting::query()->updateOrCreate(
        ['key' => 'about_cta_title'],
        ['value' => ['en' => 'Settings <em>Title</em>', 'cs' => 'Settings <em>Title</em>']],
    );

    $html = $this->get(route('about-me'))->getContent();

    expect(substr_count($html, 'class="cta-band"'))->toBe(1)
        ->and($html)->toContain('Settings <em>Title</em>')
        ->and($html)->toContain('id="about-me-contact"')
        ->and($html)->not->toContain('id="page-cta"');
});

test('the band speaks Czech when the session does', function () {
    $this->withSession(['locale' => 'cs'])
        ->get(route('home'))
        ->assertSee('Pojďme si <em>napsat</em>.', false)
        ->assertSee('Napište mi');
});

test('the band carries the availability flag set on about me', function () {
    Setting::query()->updateOrCreate(
        ['key' => 'about_available'],
        ['value' => ['en' => 'Open for work', 'cs' => 'Volný na práci']],
    );

    $this->get(route('projects'))
        ->assertSee('cta-band-available', false)
        ->assertSee('Open for work');
});

test('the CV button stays hidden until a CV path is configured', function () {
    config()->set('portfolio.cv_path', '');

    $this->get(route('home'))->assertDontSee('Download CV');

    config()->set('portfolio.cv_path', 'cv.pdf');

    $this->get(route('home'))->assertSee('Download CV');
});
