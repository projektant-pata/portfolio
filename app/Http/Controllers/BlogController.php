<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Badge;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function __invoke(Request $request): View
    {
        $slug = $request->string('badge')->toString();
        $activeBadge = $slug === '' ? null : Badge::where('slug', $slug)->first();

        $published = Article::query()->published()->with('badges')->orderByDesc('date')->get();

        $articles = $slug === ''
            ? $published
            : $published->filter(fn (Article $a) => $a->badges->contains('slug', $slug))->values();

        return view('blog', [
            'articles' => $articles,
            'activeBadge' => $activeBadge,
            'activeSlug' => $slug,
            // Numerals and the "x of y" count are always over the whole
            // archive: filtering hides rows, it does not renumber them.
            'archiveIndexes' => $published->reverse()->values()
                ->mapWithKeys(fn (Article $a, int $i) => [$a->id => str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)]),
            'total' => $published->count(),
        ]);
    }
}
