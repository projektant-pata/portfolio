<?php

use App\Models\Article;
use App\Models\Badge;
use Database\Seeders\SettingSeeder;

test('the blog listing renders without console errors and stacks responsively', function () {
    $this->seed(SettingSeeder::class);
    $badge = Badge::factory()->create(['slug' => 'hardware', 'name' => ['en' => 'hardware'], 'color' => '#F7DF1E']);
    Article::factory()->published()->count(3)->create()->each(fn ($article) => $article->badges()->attach($badge));

    visit(route('blog'))->resize(1440, 900)
        ->assertNoJavascriptErrors()
        ->assertSee('All posts')
        ->assertPresent('.blog-row--lead');

    visit(route('blog'))->resize(390, 844)
        ->assertPresent('.blog-row');
});

test('an article page renders its prose without console errors', function () {
    $this->seed(SettingSeeder::class);
    $article = Article::factory()->published()->create([
        'content' => ['en' => "## Head\n\nBody text.\n\n```php\n\$x = 1;\n```"],
    ]);

    visit(route('blog.show', $article->slug))->resize(1440, 900)
        ->assertNoJavascriptErrors()
        ->assertPresent('.blog-prose')
        ->assertPresent('.blog-code');
});
