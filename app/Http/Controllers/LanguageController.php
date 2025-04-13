<?php

// app/Http/Controllers/LanguageController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redirect;

class LanguageController extends Controller
{
    public function toggle()
    {
        // Zjištění aktuálního jazyka
        $currentLocale = App::getLocale();

        // Přepnutí na druhý jazyk
        $newLocale = ($currentLocale == 'cs') ? 'en' : 'cs';

        // Uložení nového jazyka do session
        Session::put('locale', $newLocale);
        App::setLocale($newLocale);

        return Redirect::back();
    }
}
