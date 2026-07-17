<?php

namespace App\Http\Controllers;

use App\Models\Experience;
use App\Models\Project;

class HomeController extends Controller
{
    /**
     * Number of projects teased on the home page before the visitor is sent to /projects.
     */
    private const FEATURED_PROJECT_LIMIT = 2;

    public function __invoke()
    {
        $grouped = Experience::orderBy('sort_order')->get()->groupBy('type');
        $workExperiences = $grouped->get('work', collect());
        $lifeExperiences = $grouped->get('life', collect());

        $featuredProjects = Project::with(['badges', 'links'])
            ->orderBy('sort_order')
            ->orderBy('year', 'desc')
            ->orderByRaw("header->>'en'")
            ->take(self::FEATURED_PROJECT_LIMIT)
            ->get();

        return view('welcome', compact('workExperiences', 'lifeExperiences', 'featuredProjects'));
    }
}
