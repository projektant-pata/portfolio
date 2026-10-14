<?php

use App\Mail\ContactMessage;
use App\Models\Setting;
use Database\Seeders\SettingSeeder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(SettingSeeder::class);
});

test('contact page returns 200', function () {
    $this->get(route('contact'))->assertOk();
});

test('the contact page renders all five sections in order', function () {
    $html = $this->get(route('contact'))->getContent();

    $sections = [
        'id="contact-channels"',
        'id="contact-form"',
        'id="contact-steps"',
        'id="contact-faq"',
    ];

    $positions = array_map(fn (string $needle) => strpos($html, $needle), $sections);

    expect($positions)->not->toContain(false)
        ->and($positions)->toBe(array_values(collect($positions)->sort()->all()));
});

test('the channels section opens the page without a fade-up', function () {
    /* The hero leaves the first section's top edge inside the first viewport,
       so it must already be painted rather than waiting for the observer. */
    $this->get(route('contact'))
        ->assertSee('id="contact-channels" class="portfolio-section portfolio-section--no-reveal"', false);
});

test('an empty phone number drops the phone row instead of shipping a placeholder', function () {
    config()->set('portfolio.contact.phone', '');

    $this->get(route('contact'))->assertDontSee(__('pages/contact.way_phone_note'));
});

test('a configured phone number renders its row', function () {
    config()->set('portfolio.contact.phone', '+420 111 222 333');

    $this->get(route('contact'))
        ->assertSee('+420 111 222 333')
        ->assertSee(__('pages/contact.way_phone_note'));
});

test('an empty availability setting hides the closing dot', function () {
    Setting::where('key', 'about_available')->update(['value' => ['en' => '', 'cs' => '']]);

    $this->get(route('contact'))->assertDontSee('cta-band-available', false);
});

test('the closing band offers a CV button only when a CV file is configured', function () {
    config()->set('portfolio.cv_path', '');
    $this->get(route('contact'))->assertDontSee(__('layout/cta.cv'));

    config()->set('portfolio.cv_path', 'cv.pdf');
    $this->get(route('contact'))->assertSee(__('layout/cta.cv'));
});

test('the page is fully translated into Czech', function () {
    $this->withSession(['locale' => 'cs']);

    $html = $this->get(route('contact'))->getContent();

    expect($html)->toContain(__('pages/contact.rail_title', [], 'cs'))
        ->and($html)->toContain(__('pages/contact.faq_head_eyebrow', [], 'cs'))
        ->and($html)->not->toContain(__('pages/contact.rail_title', [], 'en'));
});

test('the form refuses an empty submission with the handoff copy', function () {
    Mail::fake();

    Livewire::test('contact-form')
        ->call('submit')
        ->assertHasErrors(['name', 'email', 'message', 'consent'])
        ->assertSee(__('pages/contact.err_name'))
        ->assertSee(__('pages/contact.err_email'))
        ->assertSee(__('pages/contact.err_message'))
        ->assertSee(__('pages/contact.err_consent'));

    Mail::assertNothingSent();
});

test('the form refuses a message without consent', function () {
    Mail::fake();

    Livewire::test('contact-form')
        ->set('startedAt', time() - 30)
        ->set('name', 'Jana Nováková')
        ->set('email', 'jana@firma.cz')
        ->set('message', 'We need a small internal tool and nobody has time for it.')
        ->set('consent', false)
        ->call('submit')
        ->assertHasErrors(['consent']);

    Mail::assertNothingSent();
});

test('a valid message reaches the inbox and replaces the form with the success panel', function () {
    Mail::fake();

    Livewire::test('contact-form')
        ->set('startedAt', time() - 30)
        ->set('name', 'Jana Nováková')
        ->set('email', 'jana@firma.cz')
        ->set('topic', 'job')
        ->set('timeline', 'start in September')
        ->set('message', 'We have a junior backend opening starting in September. Interested?')
        ->set('consent', true)
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('sent', true)
        ->assertSee(__('pages/contact.success_title'))
        ->assertDontSee(__('pages/contact.send'));

    Mail::assertSent(ContactMessage::class, function (ContactMessage $mail) {
        return $mail->hasTo(config('portfolio.contact.inbox'))
            && $mail->hasReplyTo('jana@firma.cz')
            && $mail->fields['message'] === 'We have a junior backend opening starting in September. Interested?';
    });
});

test('the subject line names the topic in English whatever the visitor wrote in', function () {
    Mail::fake();
    app()->setLocale('cs');

    Livewire::test('contact-form')
        ->set('startedAt', time() - 30)
        ->set('name', 'Jana Nováková')
        ->set('email', 'jana@firma.cz')
        ->set('topic', 'hackathon')
        ->set('message', 'Našemu týmu chybí jeden backendista a je to za tři týdny.')
        ->set('consent', true)
        ->call('submit')
        ->assertSet('sent', true);

    Mail::assertSent(ContactMessage::class, function (ContactMessage $mail) {
        return $mail->envelope()->subject === '[Hackathon or team-up] Jana Nováková';
    });
});

test('a filled honeypot is answered with the success panel and no mail', function () {
    Mail::fake();

    Livewire::test('contact-form')
        ->set('startedAt', time() - 30)
        ->set('name', 'Jana Nováková')
        ->set('email', 'jana@firma.cz')
        ->set('message', 'We need a small internal tool and nobody has time for it.')
        ->set('consent', true)
        ->set('website', 'https://spam.example')
        ->call('submit')
        ->assertSet('sent', true);

    /* The bot is told nothing — being rejected out loud is how it learns. */
    Mail::assertNothingSent();
});

test('a form filled in under three seconds is treated the same way', function () {
    Mail::fake();

    Livewire::test('contact-form')
        ->set('name', 'Jana Nováková')
        ->set('email', 'jana@firma.cz')
        ->set('message', 'We need a small internal tool and nobody has time for it.')
        ->set('consent', true)
        ->call('submit')
        ->assertSet('sent', true);

    Mail::assertNothingSent();
});

test('a failing mailer shows the failure panel and keeps the typed text', function () {
    Mail::shouldReceive('to')->andThrow(new RuntimeException('SMTP is down'));

    Livewire::test('contact-form')
        ->set('startedAt', time() - 30)
        ->set('name', 'Jana Nováková')
        ->set('email', 'jana@firma.cz')
        ->set('message', 'We need a small internal tool and nobody has time for it.')
        ->set('consent', true)
        ->call('submit')
        ->assertSet('failed', true)
        ->assertSet('message', 'We need a small internal tool and nobody has time for it.')
        ->assertSee(__('pages/contact.failure_title'))
        ->assertSee(__('pages/contact.copy_message'));
});

test('a sixth attempt within the hour is rate limited', function () {
    Mail::fake();

    RateLimiter::clear('contact-form:127.0.0.1');

    foreach (range(1, 5) as $attempt) {
        Livewire::test('contact-form')
            ->set('startedAt', time() - 30)
            ->set('name', 'Jana Nováková')
            ->set('email', 'jana@firma.cz')
            ->set('message', 'We need a small internal tool and nobody has time for it.')
            ->set('consent', true)
            ->call('submit')
            ->assertSet('sent', true);
    }

    Livewire::test('contact-form')
        ->set('startedAt', time() - 30)
        ->set('name', 'Jana Nováková')
        ->set('email', 'jana@firma.cz')
        ->set('message', 'We need a small internal tool and nobody has time for it.')
        ->set('consent', true)
        ->call('submit')
        ->assertSet('failed', true);

    Mail::assertSentCount(5);
});

test('the footer and the phone nav both link to the contact page', function () {
    $html = $this->get(route('home'))->getContent();

    expect(substr_count($html, 'href="'.route('contact').'"'))->toBeGreaterThanOrEqual(2);
});
