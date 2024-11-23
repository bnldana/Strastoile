<?php

use App\Http\Controllers\FilmController;

Route::get('/', [FilmController::class, 'index'])->name('home');
Route::get('/film/{filmId}', [FilmController::class, 'show'])->name('films.show');
