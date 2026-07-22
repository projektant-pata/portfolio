<?php

namespace App\Http\Controllers;

use App\Models\Experience;
use App\Models\Project;
use App\Models\Review;
use App\Models\Stat;

class HomeController extends Controller
{
    /**
     * Number of projects teased on the home page before the visitor is sent to /projects.
     */
    private const FEATURED_PROJECT_LIMIT = 2;

    public function __invoke()
    {
        $grouped = Experience::with('badges')->orderBy('sort_order')->get()->groupBy('type');
        $workExperiences = $grouped->get('work', collect());
        $lifeExperiences = $grouped->get('life', collect());

        $featuredProjects = Project::with(['badges', 'links'])
            ->orderBy('sort_order')
            ->orderBy('year', 'desc')
            ->orderByRaw("header->>'en'")
            ->take(self::FEATURED_PROJECT_LIMIT)
            ->get();

        // Homepage shows the first four stats; the full set lives on /about-me.
        $stats = Stat::orderBy('sort_order')->take(4)->get();
        $reviews = Review::orderBy('sort_order')->get();

        return view('welcome', compact('workExperiences', 'lifeExperiences', 'featuredProjects', 'stats', 'reviews'));
    }
}
