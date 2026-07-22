<?php

namespace App\Http\Controllers;

use App\Models\AboutCard;
use App\Models\Stat;

class AboutMeController extends Controller
{
    public function __invoke()
    {
        $aboutCards = AboutCard::orderBy('sort_order')->get();
        $stats = Stat::orderBy('sort_order')->get();

        return view('about-me', compact('aboutCards', 'stats'));
    }
}
