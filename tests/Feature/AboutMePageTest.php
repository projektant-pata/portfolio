<?php

test('about me page returns 200', function () {
    $response = $this->get(route('about-me'));
    $response->assertOk();
});

test('about me page contains about me section', function () {
    $response = $this->get(route('about-me'));
    $response->assertSee('about-me-content', false);
});

test('about me page contains stats section', function () {
    $response = $this->get(route('about-me'));
    $response->assertSee('about-me-stats-cards', false);
});
