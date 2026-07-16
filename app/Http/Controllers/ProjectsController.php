<?php

namespace App\Http\Controllers;

use App\Models\Project;

class ProjectsController extends Controller
{
    public function __invoke()
    {
        $projects = Project::with(['badges', 'links'])
            ->orderBy('sort_order')
            ->orderBy('year', 'desc')
            ->orderByRaw("header->>'en'")
            ->get()
            ->groupBy('year');

        return view('projects', compact('projects'));
    }
}
