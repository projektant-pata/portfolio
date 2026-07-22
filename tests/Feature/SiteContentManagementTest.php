<?php

use App\Models\AboutCard;
use App\Models\Review;
use App\Models\Setting;
use App\Models\Stat;
use App\Models\User;
use Livewire\Livewire;

test('stats manage page renders for admins', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('manage.stats'))
        ->assertOk();
});

test('can create a static stat', function () {
    Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.stats')
        ->set('value', ['en' => '5+', 'cs' => '5+'])
        ->set('text', ['en' => 'Projects Completed', 'cs' => 'Projektů dokončeno'])
        ->call('save')
        ->assertHasNoErrors();

    $stat = Stat::first();
    expect($stat->value)->toBe(['en' => '5+', 'cs' => '5+'])
        ->and($stat->text['en'])->toBe('Projects Completed');
});

test('a stat requires an english caption', function () {
    Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.stats')
        ->set('text', ['en' => '', 'cs' => ''])
        ->call('save')
        ->assertHasErrors(['text.en']);
});

test('a stat with an age source computes its value', function () {
    $stat = Stat::factory()->create(['source' => 'age', 'value' => null]);

    expect($stat->displayValue('en'))->toBe((string) (int) \Carbon\CarbonImmutable::parse('2006-10-05')->diffInYears(now()));
});

test('can create a review', function () {
    Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.reviews')
        ->set('name', 'Petr Machovec')
        ->set('position', ['en' => 'Co-founder', 'cs' => 'Spoluzakladatel'])
        ->set('text', ['en' => '"Great work"', 'cs' => '"Skvělá práce"'])
        ->call('save')
        ->assertHasNoErrors();

    expect(Review::first()->name)->toBe('Petr Machovec');
});

test('a review requires a name and english quote', function () {
    Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.reviews')
        ->set('name', '')
        ->set('text', ['en' => '', 'cs' => ''])
        ->call('save')
        ->assertHasErrors(['name', 'text.en']);
});

test('can create an about card', function () {
    Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.about-cards')
        ->set('cardTitle', ['en' => 'About me', 'cs' => 'O mně'])
        ->set('text', ['en' => 'Hi there!', 'cs' => 'Ahoj!'])
        ->call('save')
        ->assertHasNoErrors();

    expect(AboutCard::first()->title['en'])->toBe('About me');
});

test('site content editor persists settings and rotating roles', function () {
    Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.site-content')
        ->set('texts.hero_suptitle', ['en' => 'Hello', 'cs' => 'Ahoj'])
        ->set('texts.hero_title', ['en' => 'I am X', 'cs' => 'Jsem X'])
        ->set('texts.stats_title', ['en' => 'Stats', 'cs' => 'Statistiky'])
        ->set('texts.tools_title', ['en' => 'Tools', 'cs' => 'Nástroje'])
        ->set('texts.reviews_title', ['en' => 'Reviews', 'cs' => 'Reference'])
        ->set('texts.about_title', ['en' => 'About', 'cs' => 'O mně'])
        ->set('roles.en', "Developer\nChess player")
        ->set('roles.cs', "Vývojář\nŠachista")
        ->call('save')
        ->assertHasNoErrors();

    expect(Setting::text('hero_suptitle', 'cs'))->toBe('Ahoj')
        ->and(Setting::list('hero_roles', 'en'))->toBe(['Developer', 'Chess player'])
        ->and(Setting::list('hero_roles', 'cs'))->toBe(['Vývojář', 'Šachista']);
});

test('non-admins cannot reach content manage pages', function () {
    $this->actingAs(User::factory()->nonAdmin()->create())
        ->get(route('manage.site-content'))
        ->assertForbidden();
});
