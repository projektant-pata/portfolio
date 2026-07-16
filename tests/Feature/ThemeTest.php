<?php

use App\Models\User;

test('public pages resolve the theme before first paint', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('theme=([^;]*)', false)
        ->assertSee("classList.toggle('dark', isDark)", false);
});

test('admin pages resolve the theme before first paint', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee("classList.toggle('dark', isDark)", false);
});

test('appearance settings write the theme cookie instead of flux appearance', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('appearance.edit'))
        ->assertOk()
        ->assertSee('window.setThemePreference(preference)', false)
        ->assertDontSee('$flux.appearance', false);
});

test('no layout hardcodes the dark class on the html element', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('<html lang="en" class="dark">', false);

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('<html lang="en" class="dark">', false);
});
