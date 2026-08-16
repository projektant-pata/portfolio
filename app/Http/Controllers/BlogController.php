<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Contracts\View\View;

class BlogController extends Controller
{
    public function __invoke(): View
    {
        $articles = Article::query()
            ->published()
            ->with('badges')
            ->orderByDesc('date')
            ->get();

        return view('blog', [
            'articles' => $articles,
            // Archive numerals count from the oldest post, so a new post never
            // renumbers the ones already published.
            'archiveIndexes' => $articles->reverse()->values()
                ->mapWithKeys(fn (Article $a, int $i) => [$a->id => str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)]),
            'total' => $articles->count(),
        ]);
    }
}
