<?php

use Database\Seeders\SettingSeeder;
use Illuminate\Support\Facades\Mail;

/**
 * The contact page's client-side behaviours. Everything else on the page
 * is static, and the server side is covered in tests/Feature/ContactPageTest.
 */
beforeEach(function () {
    $this->seed(SettingSeeder::class);
});

test('the contact page runs without javascript or console errors', function () {
    visit('/contact')->assertNoJavaScriptErrors()->assertNoConsoleLogs();
});

test('typing in the message field updates the counter', function () {
    $page = visit('/contact')->resize(1440, 900);

    $page->fill('#ct-message', 'We need a small internal tool.');
    $page->wait(1);

    $filled = $page->script("(() => document.getElementById('ct-message').value)()");

    /* The counter is Alpine-local: a live wire:model would mean a network
       round trip per keystroke. */
    expect($page->script("(() => document.querySelector('.ct-count .ct-help:last-child').textContent.trim())()"))
        ->toBe(mb_strlen($filled).' / 2000');
});

test('a copy button swaps its own label instead of raising a toast', function () {
    $page = visit('/contact')->resize(1440, 900);

    $page->click('.ct-way:first-child button.pill-button');
    $page->wait(1);

    expect($page->script("(() => document.querySelector('.ct-way:first-child button.pill-button').textContent.trim())()"))
        ->toBe(__('pages/contact.copied'));
});

test('the privacy note opens in place rather than on its own route', function () {
    $page = visit('/contact')->resize(1440, 900);

    expect($page->script("(() => document.getElementById('ct-privacy').open)()"))->toBeFalse();

    $page->click('.ct-check button');
    $page->wait(1);

    expect($page->script("(() => document.getElementById('ct-privacy').open)()"))->toBeTrue();
});

test('an invalid submission keeps the form, shows the handoff copy and jumps focus to the first error', function () {
    $page = visit('/contact')->resize(1440, 900);

    $page->click('.ct-form-foot button[type=submit]');
    $page->wait(2);

    $page->assertSee(__('pages/contact.err_name'))
        ->assertSee(__('pages/contact.err_consent'));

    expect($page->script('(() => document.activeElement.id)()'))->toBe('ct-name');
});

test('a valid submission swaps the button to Sending and then replaces the form with the success panel', function () {
    Mail::fake();

    $page = visit('/contact')->resize(1440, 900);

    $page->fill('#ct-message', 'We need a small internal tool and nobody on the team has time to build it.');
    $page->fill('#ct-name', 'Jana Nováková');
    $page->fill('#ct-email', 'jana@firma.cz');
    $page->check('#ct-consent');

    /* The form rejects anything filled in under three seconds as a bot. */
    $page->wait(4);

    $page->click('.ct-form-foot button[type=submit]');
    $page->wait(3);

    $page->assertSee(__('pages/contact.success_title'))
        ->assertDontSee(__('pages/contact.message_help'));
});
