<?php

use App\Models\Badge;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

test('creating a project with a duplicate slug fails', function () {
    $user = User::factory()->create();
    Project::factory()->create(['slug' => 'taken']);

    Livewire::actingAs($user)
        ->test('pages::manage.projects')
        ->set('header', ['en' => 'Another', 'cs' => ''])
        ->set('slug', 'taken')
        ->set('year', '2026')
        ->call('save')
        ->assertHasErrors(['slug']);
});

test('reorder is a no-op while a search filter is active', function () {
    $user = User::factory()->create();
    $a = Project::factory()->create(['slug' => 'a', 'sort_order' => 0]);
    $b = Project::factory()->create(['slug' => 'b', 'sort_order' => 1]);

    Livewire::actingAs($user)
        ->test('pages::manage.projects')
        ->set('search', $a->header['en'])
        ->call('reorder', $b->id, 0);

    expect($a->fresh()->sort_order)->toBe(0)
        ->and($b->fresh()->sort_order)->toBe(1);
});

test('reorder ignores an unknown id without crashing', function () {
    $user = User::factory()->create();
    Project::factory()->create(['sort_order' => 0]);

    Livewire::actingAs($user)
        ->test('pages::manage.projects')
        ->call('reorder', (string) \Illuminate\Support\Str::uuid(), 0)
        ->assertHasNoErrors();
});

test('uploading a new project image deletes the previous file', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $oldPath = UploadedFile::fake()->create('old.jpg', 10, 'image/jpeg')->store('projects', 'public');
    $project = Project::factory()->create(['img_url' => 'storage/'.$oldPath]);

    Livewire::actingAs($user)
        ->test('pages::manage.projects')
        ->call('openEdit', $project->id)
        ->set('imageFile', UploadedFile::fake()->create('new.jpg', 10, 'image/jpeg'))
        ->call('save')
        ->assertHasNoErrors();

    Storage::disk('public')->assertMissing($oldPath);
    expect($project->fresh()->img_url)->not->toBe('storage/'.$oldPath);
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
