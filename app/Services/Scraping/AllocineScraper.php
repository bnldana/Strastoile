<?php

namespace App\Services\Scraping;

use App\Services\ShowtimeService;
use Carbon\Carbon;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class AllocineScraper
{
    protected $showtimeService;
    protected $pythonPath;
    protected $scriptPath;
    protected $cinemaIds;

    public function getCinemaIds(): array
    {
        return $this->cinemaIds;
    }

    public function __construct(ShowtimeService $showtimeService)
    {
        $this->showtimeService = $showtimeService;
        $this->pythonPath = env('PYTHON_PATH', 'python3');
        $this->scriptPath = base_path('python/get_allocine_showtimes.py');
        $this->cinemaIds = [
            'Star' => 'P0027',
            'Saint-Exupéry' => 'P0025',
            'Vox' => 'P0600',
            'UGC' => 'P0963',
            'Cosmos' => 'P0026'
        ];
    }

    public function scrapeAll()
    {
        foreach ($this->cinemaIds as $cinemaName => $cinemaId) {
            try {
                $this->scrapeCinema($cinemaName, $cinemaId);
            } catch (\Exception $e) {
                \Log::error("Erreur lors du scraping de $cinemaName: " . $e->getMessage());
            }
        }
    }

    public function scrapeCinema(string $cinemaName, string $cinemaId)
    {
        $process = new Process([$this->pythonPath, $this->scriptPath, $cinemaId]);
        $process->setTimeout(300); // 5 minutes timeout

        try {
            $process->run(function ($type, $buffer) {
                if (Process::ERR === $type) {
                    \Log::info('Python script output: ' . $buffer);
                }
            });

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $output = $process->getOutput();
            $data = json_decode($output, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Erreur de décodage JSON: " . json_last_error_msg());
            }

            $formattedData = $this->formatShowtimes($data);
            $this->showtimeService->saveShowtimes($formattedData, $cinemaName);
        } catch (\Exception $e) {
            \Log::error("Erreur lors du scraping de $cinemaName ($cinemaId): " . $e->getMessage());
            throw $e;
        }
    }

    public function formatShowtimes(array $data): array
    {
        $formattedFilms = [];

        foreach ($data as $filmData) {
            if (!isset($filmData['film'])) {
                \Log::error("Données de film manquantes", ['data' => $filmData]);
                continue;
            }

            // Vérifier et formater les horaires
            $formattedShowtimes = [];
            if (isset($filmData['showtimes']) && is_array($filmData['showtimes'])) {
                foreach ($filmData['showtimes'] as $showtime) {
                    if (isset($showtime['date']) && isset($showtime['horaires']) && is_array($showtime['horaires'])) {
                        $formattedShowtimes[] = [
                            'date' => $showtime['date'],
                            'horaires' => $showtime['horaires']
                        ];
                    }
                }
            }

            $formattedFilm = [
                'film' => $filmData['film'],
                'director' => $filmData['director'] ?? 'Non spécifié',
                'duration' => $filmData['duration'] ?? 'Non spécifié',
                'genre' => $filmData['genre'] ?? 'Non spécifié',
                'poster_url' => $filmData['poster_url'] ?? null,
                'release_date' => $filmData['release_date'] ?? null,
                'showtimes' => $formattedShowtimes
            ];

            $formattedFilms[] = $formattedFilm;
        }

        return $formattedFilms;
    }
}
