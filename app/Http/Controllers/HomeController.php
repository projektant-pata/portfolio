<?php

namespace App\Http\Controllers;

use App\Models\Experience;
use App\Models\Review;
use App\Models\Stat;

class HomeController extends Controller
{
    public function __invoke()
    {
        $grouped = Experience::with('badges')->orderBy('sort_order')->get()->groupBy('type');
        $workExperiences = $grouped->get('work', collect());
        $lifeExperiences = $grouped->get('life', collect());

        // Homepage shows the first four stats; the full set lives on /about-me.
        $stats = Stat::orderBy('sort_order')->take(4)->get();
        $reviews = Review::orderBy('sort_order')->get();

        return view('welcome', compact('workExperiences', 'lifeExperiences', 'stats', 'reviews'));
    }
}
