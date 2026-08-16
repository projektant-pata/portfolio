<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Support\ArticleMarkdown;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\Response;

class ArticleController extends Controller
{
    public function __invoke(Article $article): View
    {
        abort_unless($article->isPublished(), Response::HTTP_NOT_FOUND);

        $locale = app()->getLocale();

        $article->load('badges');

        $others = Article::query()
            ->published()
            ->with('badges')
            ->whereKeyNot($article->getKey())
            ->orderByDesc('date')
            ->get();

        $slugs = $article->badges->pluck('slug');

        // Badge-mates first (newest first), then whatever is newest overall.
        $readNext = $others
            ->sortByDesc(fn (Article $other) => $other->badges->pluck('slug')->intersect($slugs)->isNotEmpty() ? 1 : 0)
            ->values()
            ->take(2);

        return view('article', [
            'article' => $article,
            'locale' => $locale,
            'body' => ArticleMarkdown::renderArticle($article, $locale),
            'readNext' => $readNext,
        ]);
    }
}
