<?php

namespace App\Http\Controllers;

use App\Models\Experience;
use App\Models\Interest;
use App\Models\PathNode;
use App\Models\Review;
use App\Models\Stat;
use App\Models\Tool;

class AboutMeController extends Controller
{
    public function __invoke()
    {
        $pathNodes = PathNode::orderBy('sort_order')->get();
        $interests = Interest::orderBy('sort_order')->get();
        $stats = Stat::orderBy('sort_order')->get();

        /* Eager loaded: each tile reads its first badge for the kind label and
           the accent colour, so four tiles would otherwise mean four queries. */
        $showUps = Experience::where('show_on_about', true)
            ->with('badges')
            ->orderBy('sort_order')
            ->get();

        $reviews = Review::orderBy('sort_order')->get();

        $tools = Tool::where('placement', 'grid')->orderBy('sort_order')->get();
        $toolChips = Tool::where('placement', 'learning')->orderBy('sort_order')->get();

        return view('about-me', compact('pathNodes', 'interests', 'stats', 'showUps', 'reviews', 'tools', 'toolChips'));
    }
}
