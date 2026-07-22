<?php

use App\Models\User;

test('admin users can reach the dashboard', function () {
    $user = User::factory()->create();

    expect($user->isAdmin())->toBeTrue();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk();
});

test('verified non-admin users are forbidden from the admin area', function () {
    $user = User::factory()->nonAdmin()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('manage.projects'))
        ->assertForbidden();
});

test('guests are redirected to login, not shown a 403', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('is_admin cannot be granted through mass assignment', function () {
    $user = User::factory()->nonAdmin()->create();

    $user->fill(['is_admin' => true]);

    expect($user->is_admin)->toBeFalse();
});

test('the make-admin command promotes a user', function () {
    $user = User::factory()->nonAdmin()->create(['email' => 'promote@example.com']);

    $this->artisan('user:make-admin', ['email' => 'promote@example.com'])
        ->assertSuccessful();

    expect($user->fresh()->isAdmin())->toBeTrue();
});

test('the make-admin command fails for an unknown email', function () {
    $this->artisan('user:make-admin', ['email' => 'nobody@example.com'])
        ->assertFailed();
});
