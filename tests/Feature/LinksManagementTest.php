<?php

use App\Models\Link;
use App\Models\Project;
use App\Models\User;
use Livewire\Livewire;

test('manage links page renders for authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('manage.links'))
        ->assertOk();
});

test('can create link with i18n alt', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::manage.links')
        ->set('project_id', $project->id)
        ->set('url', 'https://example.com')
        ->set('alt', ['en' => 'Visit site', 'cs' => ''])
        ->set('img_url', '')
        ->call('save')
        ->assertHasNoErrors();

    $link = Link::first();
    expect($link->alt)->toBe(['en' => 'Visit site'])
        ->and($link->url)->toBe('https://example.com');
});

test('create link requires url', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::manage.links')
        ->set('project_id', $project->id)
        ->set('url', '')
        ->set('alt', ['en' => 'Visit', 'cs' => ''])
        ->call('save')
        ->assertHasErrors(['url']);
});

test('can delete link', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create();
    $link = Link::factory()->create(['project_id' => $project->id]);

    Livewire::actingAs($user)
        ->test('pages::manage.links')
        ->call('confirmDelete', $link->id)
        ->call('delete')
        ->assertHasNoErrors();

    expect(Link::count())->toBe(0);
});
