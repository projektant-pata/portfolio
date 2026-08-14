<?php

namespace App\Http\Controllers;

use App\Models\Experience;
use Illuminate\View\View;

class ExperienceController extends Controller
{
    public function __invoke(): View
    {
        $experiences = Experience::with('badges')
            ->orderByRaw("CAST(SUBSTRING(year->>'en' FROM '\\d{4}') AS INTEGER) DESC NULLS LAST")
            ->orderBy('sort_order')
            ->get();

        $badges = $experiences
            ->flatMap(fn ($e) => $e->badges)
            ->unique('id')
            ->values();

        return view('experience', compact('experiences', 'badges'));
    }
}
