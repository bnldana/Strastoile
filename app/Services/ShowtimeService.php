<?php

namespace App\Services;

use App\Models\Showtime;
use App\Models\Cinema;
use App\Models\Film;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShowtimeService
{
    public function saveShowtimes(array $horaires, string $cinemaName)
    {
        DB::beginTransaction();

        try {
            $cinema = Cinema::firstOrCreate(['name' => $cinemaName]);
            $today = Carbon::today();

            // Supprimer les séances passées
            Showtime::where('cinema_id', $cinema->id)
                ->where('day', '<', $today)
                ->delete();

            foreach ($horaires as $horaire) {
                if (!$this->validateHoraireData($horaire)) {
                    continue;
                }

                try {
                    $film = $this->createOrUpdateFilm($horaire);

                    // Pour chaque jour de projection
                    foreach ($horaire['showtimes'] as $showtime) {
                        if (!$this->validateShowtimeData($showtime)) {
                            continue;
                        }

                        Showtime::updateOrCreate(
                            [
                                'cinema_id' => $cinema->id,
                                'film_id' => $film->id,
                                'day' => $showtime['date']
                            ],
                            [
                                'horaires' => json_encode($showtime['horaires'])
                            ]
                        );

                        Log::info("Séance créée/mise à jour:", [
                            'film' => $film->title,
                            'date' => $showtime['date'],
                            'horaires' => $showtime['horaires']
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error("Erreur lors du traitement du film {$horaire['film']}: " . $e->getMessage());
                    continue;
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function validateHoraireData($horaire): bool
    {
        if (!isset($horaire['film'])) {
            Log::error("Données de film manquantes", ['horaire' => $horaire]);
            return false;
        }
        return true;
    }

    private function validateShowtimeData($showtime): bool
    {
        if (!isset($showtime['date']) || !isset($showtime['horaires']) || !is_array($showtime['horaires'])) {
            Log::warning("Données de séance incomplètes ou invalides", ['showtime' => $showtime]);
            return false;
        }
        return true;
    }

    private function createOrUpdateFilm($horaire): Film
    {
        try {
            $filmData = [
                'director' => $horaire['director'] ?? 'Non spécifié',
                'duration' => $horaire['duration'] ?? 'Non spécifié',
                'genre' => $horaire['genre'] ?? null,
                'poster_url' => $horaire['poster_url'] ?? null,
            ];

            if (isset($horaire['release_date'])) {
                try {
                    $filmData['release_date'] = Carbon::createFromFormat('d/m/Y', $horaire['release_date'])->format('Y-m-d');
                } catch (\Exception $e) {
                    Log::warning("Format de date de sortie invalide", ['release_date' => $horaire['release_date']]);
                }
            }

            $film = Film::updateOrCreate(
                ['title' => $horaire['film']],
                $filmData
            );

            Log::info("Film créé/mis à jour:", [
                'id' => $film->id,
                'title' => $film->title
            ]);

            return $film;
        } catch (\Exception $e) {
            Log::error("Erreur lors de la création du film: " . $e->getMessage());
            throw $e;
        }
    }

    private function createOrUpdateShowtime(Cinema $cinema, Film $film, array $showtime): void
    {
        try {
            $validHoraires = array_filter($showtime['horaires'], function ($horaire) {
                return preg_match('/^\d{1,2}h\d{2}$/', $horaire);
            });

            if (empty($validHoraires)) {
                Log::warning("Aucun horaire valide trouvé", ['horaires' => $showtime['horaires']]);
                return;
            }

            $showtimeModel = Showtime::updateOrCreate(
                [
                    'cinema_id' => $cinema->id,
                    'film_id' => $film->id,
                    'day' => $showtime['date']
                ],
                [
                    'horaires' => json_encode(array_values($validHoraires))
                ]
            );

            Log::info("Séance créée/mise à jour:", [
                'id' => $showtimeModel->id,
                'film' => $film->title,
                'date' => $showtime['date'],
                'horaires' => $validHoraires
            ]);
        } catch (\Exception $e) {
            Log::error("Erreur lors de la création de la séance: " . $e->getMessage(), [
                'film' => $film->title,
                'date' => $showtime['date'] ?? 'unknown'
            ]);
            throw $e;
        }
    }
}
