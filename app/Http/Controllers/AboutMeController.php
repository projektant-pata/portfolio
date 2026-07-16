<?php

namespace App\Http\Controllers;

class AboutMeController extends Controller
{
    public function __invoke()
    {
        return view('about-me');
    }
}
