<?php

namespace App\Http\Controllers;

use App\Models\Experience;

class HomeController extends Controller
{
    public function __invoke()
    {
        $grouped = Experience::orderBy('sort_order')->get()->groupBy('type');
        $workExperiences = $grouped->get('work', collect());
        $lifeExperiences = $grouped->get('life', collect());

        return view('welcome', compact('workExperiences', 'lifeExperiences'));
    }
}
