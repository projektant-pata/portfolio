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

test('can create project with kind, client, status and role', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::manage.projects')
        ->set('header', ['en' => 'U Sladovny', 'cs' => ''])
        ->set('slug', 'u-sladovny')
        ->set('year', '2025')
        ->set('kind', 'client')
        ->set('client', 'PekneWeby')
        ->set('status', 'live')
        ->set('role', ['en' => 'Front-end and back-end', 'cs' => 'Front-end a back-end'])
        ->call('save')
        ->assertHasNoErrors();

    $project = Project::first();

    expect($project->kind)->toBe('client')
        ->and($project->client)->toBe('PekneWeby')
        ->and($project->status)->toBe('live')
        ->and($project->role)->toEqual(['en' => 'Front-end and back-end', 'cs' => 'Front-end a back-end']);
});

test('project kind must be one of the allowed values', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::manage.projects')
        ->set('header', ['en' => 'Bad kind', 'cs' => ''])
        ->set('slug', 'bad-kind')
        ->set('year', '2026')
        ->set('kind', 'freelance')
        ->call('save')
        ->assertHasErrors(['kind']);
});

test('project status may be left empty', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::manage.projects')
        ->set('header', ['en' => 'No status', 'cs' => ''])
        ->set('slug', 'no-status')
        ->set('year', '2026')
        ->set('status', '')
        ->call('save')
        ->assertHasNoErrors();

    expect(Project::first()->status)->toBeNull();
});

test('project defaults to the personal kind', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::manage.projects')
        ->set('header', ['en' => 'Default kind', 'cs' => ''])
        ->set('slug', 'default-kind')
        ->set('year', '2026')
        ->call('save')
        ->assertHasNoErrors();

    expect(Project::first()->kind)->toBe('personal');
});

test('links are saved with their kind', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::manage.projects')
        ->set('header', ['en' => 'With links', 'cs' => ''])
        ->set('slug', 'with-links')
        ->set('year', '2026')
        ->set('links', [
            ['url' => 'https://example.com', 'kind' => 'live', 'alt' => ['en' => '', 'cs' => ''], 'img_url' => ''],
            ['url' => 'https://github.com/a/b', 'kind' => 'repo', 'alt' => ['en' => '', 'cs' => ''], 'img_url' => ''],
        ])
        ->call('save')
        ->assertHasNoErrors();

    expect(Project::first()->links->pluck('kind')->all())->toBe(['live', 'repo']);
});

test('link kind must be one of the allowed values', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::manage.projects')
        ->set('header', ['en' => 'Bad link kind', 'cs' => ''])
        ->set('slug', 'bad-link-kind')
        ->set('year', '2026')
        ->set('links', [
            ['url' => 'https://example.com', 'kind' => 'video', 'alt' => ['en' => '', 'cs' => ''], 'img_url' => ''],
        ])
        ->call('save')
        ->assertHasErrors(['links.0.kind']);
});

test('editing a project loads its metadata into the form', function () {
    $user = User::factory()->create();
    $project = Project::factory()->client()->create(['status' => 'archived']);

    Livewire::actingAs($user)
        ->test('pages::manage.projects')
        ->call('openEdit', $project->id)
        ->assertSet('kind', 'client')
        ->assertSet('status', 'archived')
        ->assertSet('client', $project->client);
});

test('can create project with the school kind and wip status', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::manage.projects')
        ->set('header', ['en' => 'Thesis Project', 'cs' => ''])
        ->set('slug', 'thesis-project')
        ->set('year', '2026')
        ->set('kind', 'school')
        ->set('status', 'wip')
        ->call('save')
        ->assertHasNoErrors();

    $project = Project::first();

    expect($project->kind)->toBe('school')
        ->and($project->status)->toBe('wip');
});
