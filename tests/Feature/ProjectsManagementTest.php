<?php

use App\Models\Badge;
use App\Models\Project;
use App\Models\User;
use Livewire\Livewire;

test('manage projects page renders for authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('manage.projects'))
        ->assertOk();
});

test('can create project with english fields', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::manage.projects')
        ->set('header', ['en' => 'My Portfolio', 'cs' => ''])
        ->set('description', ['en' => 'A personal website.', 'cs' => ''])
        ->set('slug', 'my-portfolio')
        ->set('year', '2026')
        ->call('save')
        ->assertHasNoErrors();

    $project = Project::first();
    expect($project->header)->toBe(['en' => 'My Portfolio'])
        ->and($project->slug)->toBe('my-portfolio')
        ->and($project->year)->toBe(2026);
});

test('create project requires english header', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::manage.projects')
        ->set('header', ['en' => '', 'cs' => ''])
        ->set('slug', 'test')
        ->set('year', '2026')
        ->call('save')
        ->assertHasErrors(['header.en']);
});

test('can edit project and update translations', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create([
        'header' => ['en' => 'My Project'],
        'description' => ['en' => 'Details here.'],
    ]);

    Livewire::actingAs($user)
        ->test('pages::manage.projects')
        ->call('openEdit', $project->id)
        ->assertSet('header', ['en' => 'My Project', 'cs' => ''])
        ->set('header', ['en' => 'My Project', 'cs' => 'Můj projekt'])
        ->call('save')
        ->assertHasNoErrors();

    expect($project->fresh()->header)->toBe(['en' => 'My Project', 'cs' => 'Můj projekt']);
});

test('can delete project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::manage.projects')
        ->call('confirmDelete', $project->id)
        ->call('delete')
        ->assertHasNoErrors();

    expect(Project::count())->toBe(0);
});
