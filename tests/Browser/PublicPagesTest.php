<?php

use App\Models\Project;

test('public pages render without javascript or console errors', function () {
    $pages = visit(['/', '/about-me', '/projects', '/experience']);

    $pages->assertNoJavaScriptErrors()->assertNoConsoleLogs();
});

test('the home page teases projects straight from the database', function () {
    Project::factory()->create(['sort_order' => 0, 'header' => ['en' => 'Featured Project']]);

    visit('/')->assertSee('Featured Project');
});
