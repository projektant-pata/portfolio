<?php

use App\Http\Controllers\AboutMeController;
use App\Http\Controllers\ExperienceController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ProjectsController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/about-me', AboutMeController::class)->name('about-me');
Route::get('/experience', ExperienceController::class)->name('experience');
Route::get('/projects', ProjectsController::class)->name('projects');

Route::post('/language/toggle', [LanguageController::class, 'toggle'])->name('language.toggle');

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::livewire('dashboard', 'pages::dashboard')->name('dashboard');

    Route::livewire('dashboard/experiences', 'pages::manage.experiences')->name('manage.experiences');
    Route::livewire('dashboard/badges', 'pages::manage.badges')->name('manage.badges');
    Route::livewire('dashboard/articles', 'pages::manage.articles')->name('manage.articles');
    Route::livewire('dashboard/projects', 'pages::manage.projects')->name('manage.projects');
    Route::livewire('dashboard/links', 'pages::manage.links')->name('manage.links');
    Route::livewire('dashboard/stats', 'pages::manage.stats')->name('manage.stats');
    Route::livewire('dashboard/reviews', 'pages::manage.reviews')->name('manage.reviews');
    Route::livewire('dashboard/about-cards', 'pages::manage.about-cards')->name('manage.about-cards');
    Route::livewire('dashboard/site-content', 'pages::manage.site-content')->name('manage.site-content');
});

require __DIR__.'/settings.php';
