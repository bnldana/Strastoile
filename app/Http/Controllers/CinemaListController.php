<?php

namespace App\Http\Controllers;

use App\Models\Cinema;
use App\Models\Showtime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CinemaListController extends Controller
{
    /**
     * Récupère la liste des cinémas avec leur état de sélection
     *
     * @param Request $request
     * @param array|null $defaultSelected IDs des cinémas sélectionnés par défaut
     * @return array
     */
    public function getCinemasWithSelection(Request $request, ?array $defaultSelected = null): array
    {
        $cinemas = Cinema::all();
        $selectedCinemas = $this->getSelectedCinemas($request, $defaultSelected);

        return [
            'cinemas' => $cinemas,
            'selectedCinemas' => $selectedCinemas
        ];
    }

    /**
     * Récupère les séances pour les cinémas sélectionnés
     *
     * @param Request $request
     * @param string|null $date
     * @param array|null $defaultSelected
     * @return Collection
     */
    public function getShowtimesForSelectedCinemas(Request $request, ?string $date = null, ?array $defaultSelected = null): Collection
    {
        $selectedCinemas = $this->getSelectedCinemas($request, $defaultSelected);
        $date = $date ?? Carbon::today()->format('Y-m-d');

        return Showtime::with(['film', 'cinema'])
            ->whereIn('cinema_id', $selectedCinemas)
            ->where('day', $date)
            ->get();
    }

    /**
     * Détermine les cinémas sélectionnés en fonction des paramètres de requête
     * et des valeurs par défaut
     *
     * @param Request $request
     * @param array|null $defaultSelected
     * @return Collection
     */
    protected function getSelectedCinemas(Request $request, ?array $defaultSelected = null): Collection
    {
        // Récupère les cinémas sélectionnés depuis la requête
        $selectedFromRequest = collect($request->get('cinemas', []));

        // Si aucun cinéma n'est sélectionné dans la requête, utilise les valeurs par défaut
        if ($selectedFromRequest->isEmpty()) {
            if ($defaultSelected !== null) {
                return collect($defaultSelected);
            }
            // Si aucune valeur par défaut n'est fournie, sélectionne tous les cinémas
            return Cinema::pluck('id');
        }

        return $selectedFromRequest;
    }

    /**
     * Récupère les films disponibles pour les cinémas sélectionnés
     *
     * @param Collection $showtimes
     * @return Collection
     */
    public function getFilmsForShowtimes(Collection $showtimes): Collection
    {
        return $showtimes->pluck('film')
            ->unique('id')
            ->sortBy('title')
            ->values();
    }

    /**
     * Vérifie si un cinéma est actuellement sélectionné
     *
     * @param int $cinemaId
     * @param Collection $selectedCinemas
     * @return bool
     */
    public function isCinemaSelected(int $cinemaId, Collection $selectedCinemas): bool
    {
        return $selectedCinemas->contains($cinemaId);
    }

    /**
     * Génère l'URL pour basculer la sélection d'un cinéma
     *
     * @param Request $request
     * @param int $cinemaId
     * @param Collection $selectedCinemas
     * @return string
     */
    public function getToggleUrl(Request $request, int $cinemaId, Collection $selectedCinemas): string
    {
        $params = $request->except('cinemas');

        if ($this->isCinemaSelected($cinemaId, $selectedCinemas)) {
            $newSelected = $selectedCinemas->reject(fn($id) => $id == $cinemaId);
        } else {
            $newSelected = $selectedCinemas->push($cinemaId);
        }

        if ($newSelected->isNotEmpty()) {
            $params['cinemas'] = $newSelected->toArray();
        }

        return url()->current() . '?' . http_build_query($params);
    }
}
