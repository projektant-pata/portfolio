<?php

test('the image slot renders the picture when a path is set', function () {
    $html = (string) $this->blade(
        '<x-portfolio.image-slot path="images/about/chess.webp" caption="chess · 1200×900" alt="Chess board" height="230px" />'
    );

    expect($html)->toContain('images/about/chess.webp')
        ->and($html)->toContain('alt="Chess board"')
        ->and($html)->toContain('--image-slot-h: 230px')
        ->and($html)->not->toContain('class="image-slot"');
});

test('the image slot falls back to a captioned placeholder plate', function () {
    $html = (string) $this->blade('<x-portfolio.image-slot caption="portrait · 900×1200" height="420px" />');

    expect($html)->toContain('class="image-slot"')
        ->and($html)->toContain('portrait · 900×1200')
        ->and($html)->toContain('aria-hidden="true"')
        ->and($html)->not->toContain('<img');
});

test('the image slot escapes a caption', function () {
    $html = (string) $this->blade('<x-portfolio.image-slot caption="<script>x</script>" />');

    expect($html)->not->toContain('<script>');
});

test('the availability dot is decorative', function () {
    $html = (string) $this->blade('<x-portfolio.availability-dot />');

    expect($html)->toContain('availability-dot')
        ->and($html)->toContain('aria-hidden="true"');
});

test('the pill button renders both variants and opts into download only when asked', function () {
    $gold = (string) $this->blade('<x-portfolio.pill-button href="mailto:a@b.c">Write</x-portfolio.pill-button>');
    $ghost = (string) $this->blade('<x-portfolio.pill-button href="/cv.pdf" variant="ghost" :download="true">CV</x-portfolio.pill-button>');

    expect($gold)->toContain('pill-button--gold')
        ->and($gold)->not->toContain('download')
        ->and($ghost)->toContain('pill-button--ghost')
        ->and($ghost)->toContain('download')
        ->and($ghost)->toContain('CV');
});
