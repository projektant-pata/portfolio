<?php

use App\Models\PathNode;
use App\Models\User;
use Livewire\Livewire;

test('an admin can create a path node', function () {
    Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.path-nodes')
        ->set('year', ['en' => '2019', 'cs' => '2019'])
        ->set('nodeTitle', ['en' => 'Back to school', 'cs' => 'Zpátky do školy'])
        ->set('body', ['en' => 'Health issues.', 'cs' => 'Zdravotní problémy.'])
        ->call('save')
        ->assertHasNoErrors();

    expect(PathNode::first()->title)->toEqual(['en' => 'Back to school', 'cs' => 'Zpátky do školy']);
});

test('a path node requires an english year, title and body', function () {
    Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.path-nodes')
        ->set('year', ['en' => '', 'cs' => ''])
        ->set('nodeTitle', ['en' => '', 'cs' => ''])
        ->set('body', ['en' => '', 'cs' => ''])
        ->call('save')
        ->assertHasErrors(['year.en', 'nodeTitle.en', 'body.en']);
});

test('an admin can edit, flag as now, and delete a path node', function () {
    $node = PathNode::factory()->create();

    Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.path-nodes')
        ->call('openEdit', $node->id)
        ->set('nodeTitle', ['en' => 'Renamed', 'cs' => 'Přejmenováno'])
        ->set('isNow', true)
        ->call('save')
        ->assertHasNoErrors();

    expect($node->fresh()->getTranslation('title', 'en'))->toBe('Renamed')
        ->and($node->fresh()->is_now)->toBeTrue();

    Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.path-nodes')
        ->call('confirmDelete', $node->id)
        ->call('delete');

    expect(PathNode::count())->toBe(0);
});

test('reordering path nodes rewrites sort order', function () {
    $first = PathNode::factory()->create(['sort_order' => 0]);
    $second = PathNode::factory()->create(['sort_order' => 1]);

    Livewire::actingAs(User::factory()->create())
        ->test('pages::manage.path-nodes')
        ->call('reorder', $second->id, 0);

    expect($second->fresh()->sort_order)->toBe(0)
        ->and($first->fresh()->sort_order)->toBe(1);
});
