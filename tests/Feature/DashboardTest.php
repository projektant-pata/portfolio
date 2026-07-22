<?php

use App\Models\Article;
use App\Models\Badge;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Livewire;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('email verification is disabled, so Fortify registers no verification routes', function () {
    expect(Features::enabled(Features::emailVerification()))->toBeFalse();

    expect(Route::has('verification.notice'))->toBeFalse()
        ->and(Route::has('verification.verify'))->toBeFalse()
        ->and(Route::has('verification.send'))->toBeFalse();
});

test('users with an unverified email can still reach the dashboard', function () {
    $this->actingAs(User::factory()->unverified()->create());

    $this->get(route('dashboard'))->assertOk();
});

test('dashboard shows entity counts and quick actions', function () {
    $user = User::factory()->create();
    Project::factory()->count(3)->create();
    Badge::factory()->count(2)->create();

    Livewire::actingAs($user)
        ->test('pages::dashboard')
        ->assertSee('Projects')
        ->assertSee('Badges')
        ->assertSee('Quick actions')
        ->assertSee('Add project')
        ->assertSee('3')
        ->assertSee('2');
});

test('dashboard lists recently updated content newest first', function () {
    $user = User::factory()->create();

    $older = Project::factory()->create(['header' => ['en' => 'Older Project']]);
    $older->forceFill(['updated_at' => now()->subDays(3)])->save();

    $newer = Article::factory()->create(['header' => ['en' => 'Newer Article']]);
    $newer->forceFill(['updated_at' => now()])->save();

    Livewire::actingAs($user)
        ->test('pages::dashboard')
        ->assertSeeInOrder(['Newer Article', 'Older Project']);
});

test('dashboard shows empty state when there is no content', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::dashboard')
        ->assertSee('Nothing here yet.');
});

test('manage pages open the create modal via the create query flag', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->withQueryParams(['create' => '1'])
        ->test('pages::manage.projects')
        ->assertDispatched('modal-show');
});
