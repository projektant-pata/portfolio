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

        return view('article', [
            'article' => $article,
            'locale' => $locale,
            'body' => ArticleMarkdown::renderArticle($article, $locale),
            'readNext' => collect(),
        ]);
    }
}
