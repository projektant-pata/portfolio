<?php

use App\Models\Tool;
use App\Models\User;
use Livewire\Livewire;

test('an admin can create a grid tool', function () {
    Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.tools')
        ->set('name', 'Spring Boot')
        ->set('color', '#6DB33F')
        ->set('state', 'using')
        ->set('group', ['en' => 'Backend', 'cs' => 'Backend'])
        ->set('groupNote', ['en' => 'where I actually live', 'cs' => 'kde vážně bydlím'])
        ->set('placement', 'grid')
        ->set('showOnHome', true)
        ->call('save')
        ->assertHasNoErrors();

    $tool = Tool::first();

    expect($tool->name)->toBe('Spring Boot')
        ->and($tool->state)->toBe('using')
        ->and($tool->group)->toEqual(['en' => 'Backend', 'cs' => 'Backend'])
        ->and($tool->group_note)->toEqual(['en' => 'where I actually live', 'cs' => 'kde vážně bydlím'])
        ->and($tool->show_on_home)->toBeTrue();
});

test('a tool requires a name and rejects a malformed colour', function () {
    Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.tools')
        ->set('name', '')
        ->set('color', 'green')
        ->call('save')
        ->assertHasErrors(['name', 'color']);
});

test('a chip tool drops the group, state, colour and home flag', function () {
    Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.tools')
        ->set('name', 'Kubernetes')
        ->set('group', ['en' => 'Backend', 'cs' => 'Backend'])
        ->set('groupNote', ['en' => 'where I actually live'])
        ->set('state', 'using')
        ->set('color', '#6DB33F')
        ->set('showOnHome', true)
        ->set('placement', 'learning')
        ->call('save')
        ->assertHasNoErrors();

    $tool = Tool::first();

    expect($tool->placement)->toBe('learning')
        ->and($tool->group)->toBeNull()
        ->and($tool->group_note)->toBeNull()
        ->and($tool->state)->toBeNull()
        ->and($tool->color)->toBeNull()
        ->and($tool->show_on_home)->toBeFalse();
});

test('an admin can edit and delete a tool', function () {
    $tool = Tool::factory()->create(['name' => 'Postgres']);

    Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.tools')
        ->call('openEdit', $tool->id)
        ->set('name', 'PostgreSQL')
        ->call('save')
        ->assertHasNoErrors();

    expect($tool->fresh()->name)->toBe('PostgreSQL');

    Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.tools')
        ->call('confirmDelete', $tool->id)
        ->call('delete');

    expect(Tool::count())->toBe(0);
});

test('reordering tools rewrites sort order', function () {
    $first = Tool::factory()->create(['sort_order' => 0]);
    $second = Tool::factory()->create(['sort_order' => 1]);

    Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.tools')
        ->call('reorder', $second->id, 0);

    expect($second->fresh()->sort_order)->toBe(0)
        ->and($first->fresh()->sort_order)->toBe(1);
});

test('the tool search matches case-insensitively on the name', function () {
    Tool::factory()->create(['name' => 'Docker']);
    Tool::factory()->create(['name' => 'Figma']);

    $names = Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.tools')
        ->set('search', 'dock')
        ->instance()
        ->tools()
        ->pluck('name');

    expect($names->all())->toBe(['Docker']);
});
