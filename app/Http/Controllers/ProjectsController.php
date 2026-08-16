<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\Project;

class ProjectsController extends Controller
{
    public function __invoke()
    {
        $projects = Project::with(['badges', 'links'])
            ->orderBy('year', 'desc')
            ->orderBy('sort_order')
            ->orderByRaw("header->>'en'")
            ->get()
            ->groupBy('year');

        $stackBadges = Badge::whereHas('projects')
            ->orderByRaw("name->>'en'")
            ->get();

        return view('projects', compact('projects', 'stackBadges'));
    }
}
