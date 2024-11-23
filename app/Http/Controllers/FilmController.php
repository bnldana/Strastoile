<?php

namespace App\Http\Controllers;

use App\Models\Film;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FilmController extends Controller
{
    protected $friseController;
    protected $cinemaListController;

    public function __construct(
        FriseController $friseController,
        CinemaListController $cinemaListController
    ) {
        $this->friseController = $friseController;
        $this->cinemaListController = $cinemaListController;
    }

    public function index(Request $request)
    {
        Carbon::setLocale('fr');
        $today = Carbon::today();
        $nextTuesday = Carbon::now()->next(Carbon::TUESDAY);

        // Récupération des dates pour la frise
        $dates = $this->friseController->getFrise();
        $selectedDate = $request->query('date', $today->format('Y-m-d'));

        // Récupération des cinémas et leurs séances
        $cinemaData = $this->cinemaListController->getCinemasWithSelection($request);
        $cinemas = $cinemaData['cinemas'];
        $selectedCinemas = $cinemaData['selectedCinemas'];

        // Récupération des séances
        $showtimes = $this->getShowtimes($request, $today, $nextTuesday, $selectedCinemas);

        // Récupération des films avec leurs séances
        $films = $this->getFilmsWithShowtimes($today, $selectedCinemas);

        return view('home', [
            'films' => $films,
            'showtimes' => $showtimes,
            'dates' => $dates,
            'selectedDate' => $selectedDate,
            'cinemas' => $cinemas,
            'selectedCinemas' => $selectedCinemas,
        ]);
    }

    /**
     * Récupère les séances en fonction des filtres
     */
    protected function getShowtimes(Request $request, Carbon $today, Carbon $nextTuesday, $selectedCinemas)
    {
        if ($request->has('date') && $request->date) {
            return $this->cinemaListController->getShowtimesForSelectedCinemas(
                $request,
                Carbon::parse($request->date)->format('Y-m-d'),
                $selectedCinemas->toArray()
            );
        }

        // Si pas de date sélectionnée, retourne les séances jusqu'à mardi prochain
        return $this->cinemaListController->getShowtimesForSelectedCinemas(
            $request,
            null,
            $selectedCinemas->toArray()
        )->filter(function ($showtime) use ($today, $nextTuesday) {
            $showtimeDate = Carbon::parse($showtime->day);
            return $showtimeDate->between($today, $nextTuesday);
        });
    }

    /**
     * Récupère les films avec leurs séances filtrées
     */
    protected function getFilmsWithShowtimes(Carbon $today, $selectedCinemas)
    {
        return Film::with(['showtimes' => function ($query) use ($today, $selectedCinemas) {
            $query->where('day', '>=', $today)
                ->whereIn('cinema_id', $selectedCinemas);
        }])
            ->get()
            ->map(function ($film) {
                $film->showtimes = $film->showtimes->sortBy('day')->sortBy('time');
                return $film;
            })
            ->sortBy(function ($film) {
                return $film->showtimes->first() ? $film->showtimes->first()->day : null;
            })
            ->filter(function ($film) {
                return $film->showtimes->isNotEmpty();
            });
    }
}
