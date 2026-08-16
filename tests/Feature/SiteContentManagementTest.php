<?php

use App\Models\AboutCard;
use App\Models\Review;
use App\Models\Setting;
use App\Models\Stat;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\SettingSeeder;
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
    expect($stat->value)->toEqual(['en' => '5+', 'cs' => '5+'])
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

    expect($stat->displayValue('en'))->toBe((string) (int) CarbonImmutable::parse('2006-10-05')->diffInYears(now()));
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

test('site content editor persists settings and every rotating role list', function () {
    Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.site-content')
        ->set('texts.hero_suptitle', ['en' => 'Hello', 'cs' => 'Ahoj'])
        ->set('texts.hero_title', ['en' => 'I am X', 'cs' => 'Jsem X'])
        ->set('texts.stats_title', ['en' => 'Stats', 'cs' => 'Statistiky'])
        ->set('texts.tools_title', ['en' => 'Tools', 'cs' => 'Nástroje'])
        ->set('texts.reviews_title', ['en' => 'Reviews', 'cs' => 'Reference'])
        ->set('texts.about_title', ['en' => 'About', 'cs' => 'O mně'])
        ->set('texts.about_hero_suptitle', ['en' => 'whoami', 'cs' => 'whoami'])
        ->set('texts.about_hero_title', ['en' => 'About me', 'cs' => 'O mně'])
        ->set('texts.experience_hero_suptitle', ['en' => 'Where', 'cs' => 'Kudy'])
        ->set('texts.experience_hero_title', ['en' => 'My journey', 'cs' => 'Moje cesta'])
        ->set('texts.projects_hero_suptitle', ['en' => 'Built', 'cs' => 'Postaveno'])
        ->set('texts.projects_hero_title', ['en' => 'Shipped', 'cs' => 'Vydáno'])
        ->set('texts.blog_hero_suptitle', ['en' => 'Thinking', 'cs' => 'Myslím'])
        ->set('texts.blog_hero_title', ['en' => 'Build logs', 'cs' => 'Zápisky'])
        ->set('roleLists.hero_roles', ['en' => "Developer\nChess player", 'cs' => "Vývojář\nŠachista"])
        ->set('roleLists.about_hero_roles', ['en' => "Student\nFreelancer", 'cs' => "Student\nFreelancer"])
        ->set('roleLists.experience_hero_roles', ['en' => "Certificates\nWork", 'cs' => "Certifikáty\nPráce"])
        ->set('roleLists.projects_hero_roles', ['en' => "Laravel\nSpring Boot", 'cs' => "Laravel\nSpring Boot"])
        ->set('roleLists.blog_hero_roles', ['en' => "Writes on break\nMarkdown maximalist", 'cs' => "Píše, když\nMarkdown fanatik"])
        ->call('save')
        ->assertHasNoErrors();

    expect(Setting::text('hero_suptitle', 'cs'))->toBe('Ahoj')
        ->and(Setting::text('projects_hero_title', 'cs'))->toBe('Vydáno')
        ->and(Setting::text('blog_hero_title', 'cs'))->toBe('Zápisky')
        ->and(Setting::list('hero_roles', 'en'))->toBe(['Developer', 'Chess player'])
        ->and(Setting::list('about_hero_roles', 'cs'))->toBe(['Student', 'Freelancer'])
        ->and(Setting::list('experience_hero_roles', 'en'))->toBe(['Certificates', 'Work'])
        ->and(Setting::list('projects_hero_roles', 'cs'))->toBe(['Laravel', 'Spring Boot'])
        ->and(Setting::list('blog_hero_roles', 'en'))->toBe(['Writes on break', 'Markdown maximalist']);
});

test('the czech hero copy falls back to english when left blank', function () {
    $this->seed(SettingSeeder::class);

    Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.site-content')
        ->set('texts.about_hero_title', ['en' => 'About me', 'cs' => ''])
        ->set('roleLists.about_hero_roles', ['en' => "Student\nFreelancer", 'cs' => ''])
        ->call('save')
        ->assertHasNoErrors();

    expect(Setting::text('about_hero_title', 'cs'))->toBe('About me')
        ->and(Setting::list('about_hero_roles', 'cs'))->toBe(['Student', 'Freelancer']);
});

test('every english hero field is required', function () {
    Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.site-content')
        ->set('texts.about_hero_title', ['en' => '', 'cs' => ''])
        ->set('roleLists.projects_hero_roles', ['en' => '', 'cs' => ''])
        ->call('save')
        ->assertHasErrors(['texts.about_hero_title.en', 'roleLists.projects_hero_roles.en']);
});

test('non-admins cannot reach content manage pages', function () {
    $this->actingAs(User::factory()->nonAdmin()->create())
        ->get(route('manage.site-content'))
        ->assertForbidden();
});
